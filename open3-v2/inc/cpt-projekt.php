<?php
/**
 * Custom Post Type „projekt“ und Taxonomie „projekttyp“.
 *
 * Reihenfolge: Taxonomie zuerst, dann der Post Type (init, Priorität 5).
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registriert Taxonomie und Post Type.
 */
function open3_register_projekt(): void {
	register_taxonomy(
		'projekttyp',
		array( 'projekt' ),
		array(
			'labels'            => array(
				'name'          => __( 'Projekttypen', 'open3' ),
				'singular_name' => __( 'Projekttyp', 'open3' ),
				'menu_name'     => __( 'Projekttypen', 'open3' ),
				'all_items'     => __( 'Alle Projekttypen', 'open3' ),
				'edit_item'     => __( 'Projekttyp bearbeiten', 'open3' ),
				'add_new_item'  => __( 'Neuen Projekttyp anlegen', 'open3' ),
				'search_items'  => __( 'Projekttypen suchen', 'open3' ),
				'not_found'     => __( 'Keine Projekttypen gefunden', 'open3' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'         => 'projekttyp',
				'with_front'   => false,
				'hierarchical' => false,
			),
		)
	);

	register_post_type(
		'projekt',
		array(
			'labels'        => array(
				'name'                  => __( 'Projekte', 'open3' ),
				'singular_name'         => __( 'Projekt', 'open3' ),
				'menu_name'             => __( 'Projekte', 'open3' ),
				'add_new'               => __( 'Neues Projekt', 'open3' ),
				'add_new_item'          => __( 'Neues Projekt anlegen', 'open3' ),
				'edit_item'             => __( 'Projekt bearbeiten', 'open3' ),
				'new_item'              => __( 'Neues Projekt', 'open3' ),
				'view_item'             => __( 'Projekt ansehen', 'open3' ),
				'view_items'            => __( 'Projekte ansehen', 'open3' ),
				'search_items'          => __( 'Projekte suchen', 'open3' ),
				'not_found'             => __( 'Keine Projekte gefunden', 'open3' ),
				'not_found_in_trash'    => __( 'Keine Projekte im Papierkorb', 'open3' ),
				'all_items'             => __( 'Alle Projekte', 'open3' ),
				'archives'              => __( 'Projekt-Archiv', 'open3' ),
				'featured_image'        => __( 'Projektbild (16:9)', 'open3' ),
				'set_featured_image'    => __( 'Projektbild festlegen', 'open3' ),
				'remove_featured_image' => __( 'Projektbild entfernen', 'open3' ),
				'use_featured_image'    => __( 'Als Projektbild verwenden', 'open3' ),
				'item_published'        => __( 'Projekt veröffentlicht.', 'open3' ),
				'item_updated'          => __( 'Projekt aktualisiert.', 'open3' ),
			),
			'description'   => __( 'Anwendungsorientierte Projekte von open3', 'open3' ),
			'public'        => true,
			'show_in_rest'  => true,
			'rest_base'     => 'projekt',
			'has_archive'   => 'projekte',
			'rewrite'       => array(
				'slug'       => 'projekte',
				'with_front' => false,
				'feeds'      => false,
			),
			'menu_position' => 20,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'comments', 'revisions', 'author' ),
			'hierarchical'  => false,
			'taxonomies'    => array( 'projekttyp' ),
			'template'      => array(
				array( 'core/paragraph', array( 'placeholder' => __( 'Worum es geht …', 'open3' ) ) ),
			),
		)
	);
}
add_action( 'init', 'open3_register_projekt', 5 );

/**
 * Geschlossene Term-Menge der Taxonomie „projekttyp“ (Slug => Name).
 *
 * @return array<string,string>
 */
function open3_projekttyp_terms(): array {
	return array(
		'visualisierung' => __( 'Visualisierung', 'open3' ),
		'app'            => __( 'App', 'open3' ),
		'umfrage'        => __( 'Umfrage', 'open3' ),
		'dokumentation'  => __( 'Dokumentation', 'open3' ),
		'veranstaltung'  => __( 'Veranstaltung', 'open3' ),
		'code'           => __( 'Code', 'open3' ),
	);
}

/**
 * Legt die sechs Projekttypen an (idempotent).
 */
function open3_seed_projekttyp(): void {
	if ( ! taxonomy_exists( 'projekttyp' ) ) {
		open3_register_projekt();
	}
	foreach ( open3_projekttyp_terms() as $slug => $name ) {
		if ( term_exists( $slug, 'projekttyp' ) ) {
			continue;
		}
		wp_insert_term( $name, 'projekttyp', array( 'slug' => $slug ) );
	}
	update_option( 'open3_terms_seeded', '1' );
}

/**
 * Einmaliges Seeding im Admin, falls das Theme ohne after_switch_theme aktiviert wurde.
 */
function open3_maybe_seed_projekttyp(): void {
	if ( '1' === get_option( 'open3_terms_seeded' ) ) {
		return;
	}
	open3_seed_projekttyp();
}
add_action( 'admin_init', 'open3_maybe_seed_projekttyp' );

/**
 * Nach Theme-Aktivierung: registrieren, Terme anlegen, Rewrite-Regeln erneuern.
 */
function open3_after_switch_theme(): void {
	open3_register_projekt();
	open3_seed_projekttyp();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'open3_after_switch_theme' );
add_action( 'switch_theme', 'flush_rewrite_rules' );

/**
 * Meta-Query, die als „versteckt“ markierte Projekte ausschließt.
 *
 * @return array<int|string,mixed>
 */
function open3_projekt_visible_meta_query(): array {
	return array(
		'relation' => 'OR',
		array(
			'key'     => 'open3_versteckt',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => 'open3_versteckt',
			'value'   => '1',
			'compare' => '!=',
		),
	);
}

/**
 * Hauptabfrage für Projekt-Archiv und Projekttyp-Archiv.
 *
 * @param WP_Query $query Die Abfrage.
 */
function open3_projekt_archive_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_post_type_archive( 'projekt' ) && ! $query->is_tax( 'projekttyp' ) ) {
		return;
	}
	$query->set( 'posts_per_page', -1 );
	$query->set(
		'orderby',
		array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		)
	);
	$query->set( 'meta_query', open3_projekt_visible_meta_query() );
}
add_action( 'pre_get_posts', 'open3_projekt_archive_query' );

/**
 * Name des ersten Projekttyps eines Projekts.
 *
 * @param int $post_id Projekt-ID.
 */
function open3_projekt_type_name( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'projekttyp' );
	if ( ! is_array( $terms ) || array() === $terms ) {
		return '';
	}
	$first = reset( $terms );
	return $first instanceof WP_Term ? (string) $first->name : '';
}
