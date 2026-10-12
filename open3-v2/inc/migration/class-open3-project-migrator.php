<?php
/**
 * In-place-Migration der Projekt-Kindseiten (page) zum Post Type „projekt“.
 *
 * Keine Ausgabe; liefert Report-Zeilen [slug, action, type, image, warnings].
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/data.php';

/**
 * Migrator-Service.
 */
final class Open3_Project_Migrator {

	public const LEGACY_PARENT_ID   = 17418;
	public const LEGACY_PARENT_SLUG = 'projekte-legacy';

	/**
	 * Optionen.
	 *
	 * @var array<string,mixed>
	 */
	private array $options;

	/**
	 * Report-Zeilen.
	 *
	 * @var array<int,array{slug:string,action:string,type:string,image:string,warnings:string}>
	 */
	private array $rows = array();

	/**
	 * Freitext-Protokoll (Menüs, Templates, Fehler).
	 *
	 * @var array<int,string>
	 */
	private array $log = array();

	/**
	 * Anzahl der Fehler.
	 */
	private int $errors = 0;

	/**
	 * @param array<string,mixed> $options dry_run, images (local|remote|skip), image_base, image_timeout, menus, reset_templates, rollback.
	 */
	public function __construct( array $options = array() ) {
		$this->options = array_merge(
			array(
				'dry_run'         => false,
				'images'          => 'skip',
				'image_base'      => open3_migration_image_base(),
				'image_timeout'   => 30,
				'menus'           => false,
				'reset_templates' => false,
				'rollback'        => false,
			),
			$options
		);
		if ( ! in_array( $this->options['images'], array( 'local', 'remote', 'skip' ), true ) ) {
			$this->options['images'] = 'skip';
		}
		$this->options['image_timeout'] = max( 1, (int) $this->options['image_timeout'] );
	}

	/**
	 * Aktive Optionen.
	 *
	 * @return array<string,mixed>
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * Protokollzeilen.
	 *
	 * @return array<int,string>
	 */
	public function get_log(): array {
		return $this->log;
	}

	/**
	 * Ob Fehler aufgetreten sind.
	 */
	public function has_errors(): bool {
		return $this->errors > 0;
	}

	/**
	 * Report-Zeilen des letzten Laufs.
	 *
	 * @return array<int,array{slug:string,action:string,type:string,image:string,warnings:string}>
	 */
	public function get_rows(): array {
		return $this->rows;
	}

	/**
	 * Führt Migration oder Rollback aus.
	 *
	 * @return array<int,array{slug:string,action:string,type:string,image:string,warnings:string}>
	 */
	public function run(): array {
		$this->rows   = array();
		$this->log    = array();
		$this->errors = 0;

		if ( $this->is_dry_run() ) {
			$this->log( __( 'Testlauf: es werden keine Änderungen gespeichert.', 'open3' ) );
		}

		if ( ! empty( $this->options['rollback'] ) ) {
			$this->rollback();
			$this->finish();
			return $this->rows;
		}

		if ( ! post_type_exists( 'projekt' ) && function_exists( 'open3_register_projekt' ) ) {
			open3_register_projekt();
		}
		if ( ! $this->is_dry_run() && function_exists( 'open3_seed_projekttyp' ) ) {
			open3_seed_projekttyp();
		}

		$parent   = $this->find_parent();
		$children = $parent instanceof WP_Post ? $this->children_of( $parent->ID ) : array();
		$migrated = $this->migrated_projects();

		if ( array() === $children && array() === $migrated ) {
			$this->error( __( 'Keine Kindseiten der Seite „projekte“ und keine bereits migrierten Projekte gefunden.', 'open3' ) );
			return $this->rows;
		}

		$this->log( sprintf( __( '%1$d Kindseiten zu konvertieren, %2$d Projekte bereits migriert.', 'open3' ), count( $children ), count( $migrated ) ) );

		foreach ( $children as $page ) {
			$this->convert( $page );
		}
		foreach ( $migrated as $projekt ) {
			$this->update( $projekt );
		}

		if ( $parent instanceof WP_Post && 'page' === $parent->post_type && self::LEGACY_PARENT_SLUG !== $parent->post_name ) {
			$this->retire_parent( $parent );
		}

		if ( ! empty( $this->options['menus'] ) ) {
			foreach ( self::setup_menus( $this->is_dry_run() ) as $line ) {
				$this->log( $line );
			}
		}
		if ( ! empty( $this->options['reset_templates'] ) ) {
			$this->reset_templates();
		}

		$this->finish();
		return $this->rows;
	}

	/**
	 * Ob Testlauf.
	 */
	public function is_dry_run(): bool {
		return ! empty( $this->options['dry_run'] );
	}

	/**
	 * Elternseite „projekte“ (oder Live-ID 17418).
	 */
	private function find_parent(): ?WP_Post {
		$parent = get_page_by_path( 'projekte', OBJECT, 'page' );
		if ( $parent instanceof WP_Post ) {
			return $parent;
		}
		$by_id = get_post( self::LEGACY_PARENT_ID );
		if ( $by_id instanceof WP_Post && 'page' === $by_id->post_type ) {
			return $by_id;
		}
		$legacy = get_page_by_path( self::LEGACY_PARENT_SLUG, OBJECT, 'page' );
		return $legacy instanceof WP_Post ? $legacy : null;
	}

	/**
	 * Alle Kindseiten (page) eines Elternteils.
	 *
	 * @param int $parent_id Eltern-ID.
	 * @return array<int,WP_Post>
	 */
	private function children_of( int $parent_id ): array {
		$posts = get_posts(
			array(
				'post_type'        => 'page',
				'post_parent'      => $parent_id,
				'post_status'      => array( 'publish', 'draft', 'private', 'pending', 'future' ),
				'numberposts'      => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => true,
			)
		);
		return array_values( array_filter( $posts, static fn( $p ) => $p instanceof WP_Post ) );
	}

	/**
	 * Bereits migrierte Projekte (Rollback-Meta vorhanden).
	 *
	 * @return array<int,WP_Post>
	 */
	private function migrated_projects(): array {
		if ( ! post_type_exists( 'projekt' ) ) {
			return array();
		}
		$posts = get_posts(
			array(
				'post_type'        => 'projekt',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'meta_key'         => '_open3_migrated_from_parent', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'suppress_filters' => true,
			)
		);
		return array_values( array_filter( $posts, static fn( $p ) => $p instanceof WP_Post ) );
	}

	/**
	 * Seite in place zu „projekt“ konvertieren.
	 *
	 * @param WP_Post $page Kindseite.
	 */
	private function convert( WP_Post $page ): void {
		$id       = (int) $page->ID;
		$slug     = (string) $page->post_name;
		$warnings = array();
		$order    = open3_migration_order_map()[ $slug ] ?? 99;
		if ( 99 === $order ) {
			$warnings[] = __( 'Slug nicht in der Prototyp-Reihenfolge (menu_order 99)', 'open3' );
		}

		if ( ! $this->is_dry_run() ) {
			$template = (string) get_post_meta( $id, '_wp_page_template', true );
			update_post_meta( $id, '_open3_migrated_from_parent', (int) $page->post_parent );
			update_post_meta( $id, '_open3_legacy_template', $template );
			update_post_meta( $id, '_open3_legacy_menu_order', (int) $page->menu_order );

			set_post_type( $id, 'projekt' );
			delete_post_meta( $id, '_wp_page_template' );

			$result = wp_update_post(
				array(
					'ID'          => $id,
					'post_parent' => 0,
					'menu_order'  => $order,
				),
				true
			);
			if ( is_wp_error( $result ) ) {
				$this->error( sprintf( __( '%1$s: Aktualisierung fehlgeschlagen (%2$s)', 'open3' ), $slug, $result->get_error_message() ) );
				$warnings[] = $result->get_error_message();
			}
			clean_post_cache( $id );
			$fresh = get_post( $id );
			if ( $fresh instanceof WP_Post && $fresh->post_name !== $slug ) {
				$msg = sprintf( __( 'Slug wurde zu „%s“ geändert (Konflikt mit Anhang?)', 'open3' ), $fresh->post_name );
				$warnings[] = $msg;
				$this->error( $slug . ': ' . $msg );
			}
		}

		$extras = $this->apply_extras( $id, $slug );
		$this->row( $slug, $this->is_dry_run() ? __( 'würde konvertieren', 'open3' ) : __( 'konvertiert', 'open3' ), $extras['type'], $extras['image'], array_merge( $warnings, $extras['warnings'] ) );
	}

	/**
	 * Bereits migriertes Projekt nachziehen (Terme, Bild, Sonderfälle).
	 *
	 * @param WP_Post $projekt Projekt.
	 */
	private function update( WP_Post $projekt ): void {
		$slug   = (string) $projekt->post_name;
		$extras = $this->apply_extras( (int) $projekt->ID, $slug );
		$this->row( $slug, $this->is_dry_run() ? __( 'würde aktualisieren', 'open3' ) : __( 'aktualisiert', 'open3' ), $extras['type'], $extras['image'], $extras['warnings'] );
	}

	/**
	 * Term, Beitragsbild und Sonderfälle anwenden.
	 *
	 * @param int    $id   Post-ID.
	 * @param string $slug Slug.
	 * @return array{type:string,image:string,warnings:array<int,string>}
	 */
	private function apply_extras( int $id, string $slug ): array {
		$warnings = array();

		$type_map  = open3_migration_type_map();
		$type_slug = $type_map[ $slug ] ?? '';
		if ( '' === $type_slug ) {
			$warnings[] = __( 'kein Projekttyp zugeordnet', 'open3' );
		} elseif ( ! $this->is_dry_run() ) {
			$existing = wp_get_object_terms( $id, 'projekttyp', array( 'fields' => 'slugs' ) );
			if ( ! is_array( $existing ) || ! in_array( $type_slug, $existing, true ) ) {
				$set = wp_set_object_terms( $id, $type_slug, 'projekttyp', false );
				if ( is_wp_error( $set ) ) {
					$warnings[] = $set->get_error_message();
				}
			}
		}

		$image_map = open3_migration_image_map();
		if ( isset( $image_map[ $slug ] ) ) {
			$image = $this->attach_image( $id, $image_map[ $slug ] );
			if ( '' !== $image['warning'] ) {
				$warnings[] = $image['warning'];
			}
			$image_status = $image['status'];
		} else {
			$image_status = __( 'kein Bild', 'open3' );
			if ( ! has_post_thumbnail( $id ) ) {
				$warnings[] = 'MISSING_IMAGE';
			}
		}

		$specials = open3_migration_specials();
		if ( isset( $specials[ $slug ] ) ) {
			$special = $specials[ $slug ];
			if ( $this->is_dry_run() ) {
				$keys = array_keys( $special['meta'] );
				if ( '' !== $special['comment_status'] ) {
					$keys[] = 'comment_status=' . $special['comment_status'];
				}
				$warnings[] = sprintf( __( 'Sonderfall: %s', 'open3' ), implode( ', ', $keys ) );
			} else {
				foreach ( $special['meta'] as $key => $value ) {
					if ( is_string( $value ) ) {
						$value = str_replace( '{home}', untrailingslashit( home_url() ), $value );
					}
					update_post_meta( $id, $key, $value );
				}
				if ( '' !== $special['comment_status'] ) {
					if ( '' === (string) get_post_meta( $id, '_open3_legacy_comment_status', true ) ) {
						update_post_meta( $id, '_open3_legacy_comment_status', (string) get_post_field( 'comment_status', $id ) );
					}
					wp_update_post(
						array(
							'ID'             => $id,
							'comment_status' => $special['comment_status'],
						)
					);
				}
			}
		}

		return array(
			'type'     => $type_slug,
			'image'    => $image_status,
			'warnings' => $warnings,
		);
	}

	/**
	 * Beitragsbild aus dem Legacy-Ordner setzen (idempotent über _open3_source_file).
	 *
	 * Öffentlich, damit dev/seed.php denselben Importpfad nutzt.
	 *
	 * @param int    $post_id Projekt-ID.
	 * @param string $file    Dateiname in projekt-explorer/.
	 * @return array{status:string,warning:string}
	 */
	public function attach_image( int $post_id, string $file ): array {
		if ( has_post_thumbnail( $post_id ) ) {
			return array(
				'status'  => __( 'vorhanden', 'open3' ),
				'warning' => '',
			);
		}

		$existing = $this->find_attachment( $file );
		if ( $existing > 0 ) {
			if ( ! $this->is_dry_run() ) {
				set_post_thumbnail( $post_id, $existing );
			}
			return array(
				'status'  => sprintf( __( 'wiederverwendet (#%d)', 'open3' ), $existing ),
				'warning' => '',
			);
		}

		$mode = (string) $this->options['images'];
		if ( 'skip' === $mode ) {
			return array(
				'status'  => __( 'übersprungen', 'open3' ),
				'warning' => '',
			);
		}
		if ( $this->is_dry_run() ) {
			return array(
				'status'  => sprintf( __( 'würde importieren: %s', 'open3' ), $file ),
				'warning' => '',
			);
		}

		$tmp = $this->fetch_to_temp( $file, $mode );
		if ( is_wp_error( $tmp ) ) {
			return array(
				'status'  => __( 'fehlt', 'open3' ),
				'warning' => $tmp->get_error_message(),
			);
		}

		$this->load_media_functions();
		$attachment_id = media_handle_sideload(
			array(
				'name'     => $file,
				'tmp_name' => $tmp,
			),
			$post_id
		);
		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return array(
				'status'  => __( 'Fehler', 'open3' ),
				'warning' => $attachment_id->get_error_message(),
			);
		}
		update_post_meta( (int) $attachment_id, '_open3_source_file', $file );
		set_post_thumbnail( $post_id, (int) $attachment_id );

		return array(
			'status'  => sprintf( __( 'importiert (#%d)', 'open3' ), (int) $attachment_id ),
			'warning' => '',
		);
	}

	/**
	 * Vorhandenen Anhang zur Quelldatei finden.
	 *
	 * @param string $file Dateiname.
	 */
	private function find_attachment( string $file ): int {
		$ids = get_posts(
			array(
				'post_type'        => 'attachment',
				'post_status'      => 'inherit',
				'numberposts'      => 1,
				'fields'           => 'ids',
				'meta_key'         => '_open3_source_file', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'suppress_filters' => true,
			)
		);
		return is_array( $ids ) && array() !== $ids ? (int) reset( $ids ) : 0;
	}

	/**
	 * Quelldatei in eine temporäre Kopie holen (lokal oder remote).
	 *
	 * @param string $file Dateiname.
	 * @param string $mode local|remote.
	 * @return string|WP_Error Temp-Pfad.
	 */
	private function fetch_to_temp( string $file, string $mode ) {
		$this->load_media_functions();
		if ( 'local' === $mode ) {
			$path = WP_CONTENT_DIR . '/uploads/projekt-explorer/' . $file;
			if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
				return new WP_Error( 'open3_missing_file', sprintf( __( 'Datei nicht gefunden: %s', 'open3' ), $path ) );
			}
			$tmp = wp_tempnam( $file );
			if ( '' === $tmp || ! copy( $path, $tmp ) ) {
				return new WP_Error( 'open3_copy_failed', sprintf( __( 'Kopie nach temp fehlgeschlagen: %s', 'open3' ), $file ) );
			}
			return $tmp;
		}

		$url = trailingslashit( (string) $this->options['image_base'] ) . rawurlencode( $file );
		$tmp = download_url( $url, (int) $this->options['image_timeout'] );
		if ( is_wp_error( $tmp ) ) {
			return new WP_Error( 'open3_download_failed', sprintf( __( 'Download fehlgeschlagen (%1$s): %2$s', 'open3' ), $url, $tmp->get_error_message() ) );
		}
		return $tmp;
	}

	/**
	 * Admin-Includes für Sideload laden.
	 */
	private function load_media_functions(): void {
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
	}

	/**
	 * Elternseite auf Entwurf setzen, Slug freigeben.
	 *
	 * @param WP_Post $parent Elternseite.
	 */
	private function retire_parent( WP_Post $parent ): void {
		if ( $this->is_dry_run() ) {
			$this->row( $parent->post_name, __( 'würde Elternseite auf Entwurf setzen (projekte-legacy)', 'open3' ), '', '', array() );
			return;
		}
		update_post_meta( $parent->ID, '_open3_legacy_slug', $parent->post_name );
		update_post_meta( $parent->ID, '_open3_legacy_status', $parent->post_status );
		// wp_update_post() reicht page_template erneut ein; ein dem aktiven Theme unbekanntes Legacy-Template ergäbe invalid_page_template.
		$template = (string) get_post_meta( $parent->ID, '_wp_page_template', true );
		update_post_meta( $parent->ID, '_open3_legacy_template', $template );
		delete_post_meta( $parent->ID, '_wp_page_template' );
		$result = wp_update_post(
			array(
				'ID'          => $parent->ID,
				'post_status' => 'draft',
				'post_name'   => self::LEGACY_PARENT_SLUG,
			),
			true
		);
		$warnings = array();
		if ( is_wp_error( $result ) ) {
			$warnings[] = $result->get_error_message();
			$this->error( $result->get_error_message() );
		}
		$this->row( $parent->post_name, __( 'Elternseite → Entwurf (projekte-legacy)', 'open3' ), '', '', $warnings );
	}

	/**
	 * Legacy-Seitentemplates von allen Seiten entfernen.
	 */
	private function reset_templates(): void {
		$ids = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'fields'           => 'ids',
				'suppress_filters' => true,
				'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_wp_page_template',
						'value'   => open3_migration_legacy_templates(),
						'compare' => 'IN',
					),
				),
			)
		);
		if ( ! is_array( $ids ) || array() === $ids ) {
			$this->log( __( 'Templates: keine Seite mit Legacy-Template gefunden.', 'open3' ) );
			return;
		}
		foreach ( $ids as $id ) {
			$template = (string) get_post_meta( (int) $id, '_wp_page_template', true );
			$post     = get_post( (int) $id );
			$slug     = $post instanceof WP_Post ? $post->post_name : (string) $id;
			if ( ! $this->is_dry_run() ) {
				delete_post_meta( (int) $id, '_wp_page_template' );
			}
			$this->log( sprintf( $this->is_dry_run() ? __( 'Template %1$s würde von „%2$s“ entfernt.', 'open3' ) : __( 'Template %1$s von „%2$s“ entfernt.', 'open3' ), $template, $slug ) );
		}
	}

	/**
	 * Rollback: Projekte zurück zu Seiten, Elternseite wiederherstellen.
	 */
	private function rollback(): void {
		$projects = $this->migrated_projects();
		if ( array() === $projects ) {
			$this->log( __( 'Rollback: keine migrierten Projekte gefunden.', 'open3' ) );
		}

		$legacy_parent = get_page_by_path( self::LEGACY_PARENT_SLUG, OBJECT, 'page' );
		$parent_id     = $legacy_parent instanceof WP_Post ? (int) $legacy_parent->ID : 0;

		foreach ( $projects as $projekt ) {
			$id       = (int) $projekt->ID;
			$slug     = (string) $projekt->post_name;
			$warnings = array();
			$target   = (int) get_post_meta( $id, '_open3_migrated_from_parent', true );
			if ( 0 === $target ) {
				$target = $parent_id;
			}
			$template = (string) get_post_meta( $id, '_open3_legacy_template', true );
			$order    = (int) get_post_meta( $id, '_open3_legacy_menu_order', true );

			if ( ! $this->is_dry_run() ) {
				set_post_type( $id, 'page' );
				$restore = array(
					'ID'          => $id,
					'post_parent' => $target,
					'menu_order'  => $order,
				);
				$comment_status = (string) get_post_meta( $id, '_open3_legacy_comment_status', true );
				if ( '' !== $comment_status ) {
					$restore['comment_status'] = $comment_status;
				}
				$result = wp_update_post( $restore, true );
				if ( is_wp_error( $result ) ) {
					$warnings[] = $result->get_error_message();
					$this->error( $slug . ': ' . $result->get_error_message() );
				}
				if ( '' !== $template ) {
					update_post_meta( $id, '_wp_page_template', $template );
				}
				wp_set_object_terms( $id, array(), 'projekttyp', false );
				$special = open3_migration_specials()[ $slug ] ?? null;
				if ( is_array( $special ) ) {
					foreach ( array_keys( $special['meta'] ) as $key ) {
						delete_post_meta( $id, $key );
					}
				}
				delete_post_meta( $id, '_open3_legacy_comment_status' );
				delete_post_meta( $id, '_open3_migrated_from_parent' );
				delete_post_meta( $id, '_open3_legacy_template' );
				delete_post_meta( $id, '_open3_legacy_menu_order' );
				clean_post_cache( $id );
			}
			$this->row( $slug, $this->is_dry_run() ? __( 'würde zurücksetzen (page)', 'open3' ) : __( 'zurückgesetzt (page)', 'open3' ), '', '' !== $template ? $template : '', $warnings );
		}

		if ( $legacy_parent instanceof WP_Post ) {
			$slug   = (string) get_post_meta( $legacy_parent->ID, '_open3_legacy_slug', true );
			$status = (string) get_post_meta( $legacy_parent->ID, '_open3_legacy_status', true );
			$slug   = '' !== $slug ? $slug : 'projekte';
			$status = '' !== $status ? $status : 'publish';
			if ( ! $this->is_dry_run() ) {
				wp_update_post(
					array(
						'ID'          => $legacy_parent->ID,
						'post_status' => $status,
						'post_name'   => $slug,
					)
				);
				$parent_template = (string) get_post_meta( $legacy_parent->ID, '_open3_legacy_template', true );
				if ( '' !== $parent_template ) {
					update_post_meta( $legacy_parent->ID, '_wp_page_template', $parent_template );
				}
				delete_post_meta( $legacy_parent->ID, '_open3_legacy_template' );
				delete_post_meta( $legacy_parent->ID, '_open3_legacy_slug' );
				delete_post_meta( $legacy_parent->ID, '_open3_legacy_status' );
			}
			$this->row( self::LEGACY_PARENT_SLUG, $this->is_dry_run() ? sprintf( __( 'würde Elternseite wiederherstellen (%s)', 'open3' ), $slug ) : sprintf( __( 'Elternseite wiederhergestellt (%s)', 'open3' ), $slug ), '', '', array() );
		}
	}

	/**
	 * Menüs „Hauptnavigation“ und „Footer“ anlegen und Locations zuweisen.
	 *
	 * @param bool $dry_run Nur protokollieren.
	 * @return array<int,string> Protokoll.
	 */
	public static function setup_menus( bool $dry_run = false ): array {
		$log       = array();
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations = is_array( $locations ) ? $locations : array();
		$changed   = false;

		foreach ( open3_migration_menu_spec() as $location => $menu ) {
			$existing = wp_get_nav_menu_object( $menu['name'] );
			if ( $existing instanceof WP_Term ) {
				$menu_id = (int) $existing->term_id;
				$log[]   = sprintf( __( 'Menü „%1$s“ vorhanden (ID %2$d).', 'open3' ), $menu['name'], $menu_id );
			} elseif ( $dry_run ) {
				$log[] = sprintf( __( 'Menü „%s“ würde angelegt.', 'open3' ), $menu['name'] );
				continue;
			} else {
				$created = wp_create_nav_menu( $menu['name'] );
				if ( is_wp_error( $created ) ) {
					$log[] = sprintf( __( 'Menü „%1$s“ konnte nicht angelegt werden: %2$s', 'open3' ), $menu['name'], $created->get_error_message() );
					continue;
				}
				$menu_id = (int) $created;
				$log[]   = sprintf( __( 'Menü „%1$s“ angelegt (ID %2$d).', 'open3' ), $menu['name'], $menu_id );
			}

			$items = wp_get_nav_menu_items( $menu_id );
			if ( ( ! is_array( $items ) || array() === $items ) && ! $dry_run ) {
				$position = 0;
				foreach ( $menu['items'] as $item ) {
					++$position;
					$result = self::add_menu_item( $menu_id, $item, $position );
					if ( is_wp_error( $result ) ) {
						$log[] = sprintf( __( 'Eintrag „%1$s“ übersprungen: %2$s', 'open3' ), $item['title'], $result->get_error_message() );
					}
				}
				$log[] = sprintf( __( '%1$d Einträge in „%2$s“ angelegt.', 'open3' ), $position, $menu['name'] );
			}

			if ( ! isset( $locations[ $location ] ) || (int) $locations[ $location ] !== $menu_id ) {
				$locations[ $location ] = $menu_id;
				$changed                = true;
				$log[]                  = sprintf( __( 'Location „%1$s“ → Menü „%2$s“.', 'open3' ), $location, $menu['name'] );
			}
		}

		if ( $changed && ! $dry_run ) {
			set_theme_mod( 'nav_menu_locations', $locations );
		}
		return $log;
	}

	/**
	 * Einzelnen Menüeintrag anlegen.
	 *
	 * @param int                  $menu_id  Menü-ID.
	 * @param array<string,string> $item     Spezifikation.
	 * @param int                  $position Position.
	 * @return int|WP_Error
	 */
	private static function add_menu_item( int $menu_id, array $item, int $position ) {
		$data = array(
			'menu-item-title'    => $item['title'],
			'menu-item-status'   => 'publish',
			'menu-item-position' => $position,
		);
		switch ( $item['type'] ) {
			case 'home':
				$data['menu-item-type'] = 'custom';
				$data['menu-item-url']  = home_url( '/' );
				break;
			case 'post_type_archive':
				$data['menu-item-type']   = 'post_type_archive';
				$data['menu-item-object'] = $item['object'];
				break;
			case 'page':
				$page = get_page_by_path( $item['slug'], OBJECT, 'page' );
				if ( ! $page instanceof WP_Post ) {
					return new WP_Error( 'open3_menu_page_missing', sprintf( __( 'Seite „%s“ nicht gefunden', 'open3' ), $item['slug'] ) );
				}
				$data['menu-item-type']      = 'post_type';
				$data['menu-item-object']    = 'page';
				$data['menu-item-object-id'] = (int) $page->ID;
				break;
			default:
				return new WP_Error( 'open3_menu_type', __( 'Unbekannter Eintragstyp', 'open3' ) );
		}
		return wp_update_nav_menu_item( $menu_id, 0, $data );
	}

	/**
	 * Abschluss: Rewrite-Regeln erneuern.
	 */
	private function finish(): void {
		if ( $this->is_dry_run() ) {
			return;
		}
		if ( function_exists( 'open3_register_projekt' ) && ! post_type_exists( 'projekt' ) ) {
			open3_register_projekt();
		}
		flush_rewrite_rules();
		$this->log( __( 'Rewrite-Regeln erneuert.', 'open3' ) );
	}

	/**
	 * Report-Zeile hinzufügen.
	 *
	 * @param string             $slug     Slug.
	 * @param string             $action   Aktion.
	 * @param string             $type     Projekttyp.
	 * @param string             $image    Bildstatus.
	 * @param array<int,string>  $warnings Warnungen.
	 */
	private function row( string $slug, string $action, string $type, string $image, array $warnings ): void {
		$this->rows[] = array(
			'slug'     => $slug,
			'action'   => $action,
			'type'     => $type,
			'image'    => $image,
			'warnings' => implode( '; ', array_filter( $warnings ) ),
		);
	}

	/**
	 * Protokollzeile.
	 *
	 * @param string $message Text.
	 */
	private function log( string $message ): void {
		$this->log[] = $message;
	}

	/**
	 * Fehler protokollieren und zählen.
	 *
	 * @param string $message Text.
	 */
	private function error( string $message ): void {
		++$this->errors;
		$this->log[] = 'FEHLER: ' . $message;
	}
}
