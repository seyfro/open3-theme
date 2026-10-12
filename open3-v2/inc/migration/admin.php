<?php
/**
 * Werkzeuge → „open3 Migration“: Migration ohne WP-CLI ausführen.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Untermenü registrieren.
 */
function open3_migration_admin_menu(): void {
	add_management_page(
		__( 'open3 Migration', 'open3' ),
		__( 'open3 Migration', 'open3' ),
		'manage_options',
		'open3-migration',
		'open3_migration_admin_page'
	);
}
add_action( 'admin_menu', 'open3_migration_admin_menu' );

/**
 * Formular verarbeiten und Seite rendern.
 */
function open3_migration_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'open3' ) );
	}

	$rows     = null;
	$log      = array();
	$errors   = false;
	$notice   = '';
	$defaults = array(
		'dry_run'         => true,
		'images'          => 'local',
		'menus'           => false,
		'reset_templates' => false,
		'rollback'        => false,
	);
	$options  = $defaults;

	if ( isset( $_POST['open3_migration_action'] ) ) {
		check_admin_referer( 'open3_migration', 'open3_migration_nonce' );
		$action = sanitize_key( wp_unslash( $_POST['open3_migration_action'] ) );

		if ( 'seed_terms' === $action ) {
			open3_seed_projekttyp();
			$notice = __( 'Projekttypen wurden angelegt bzw. geprüft.', 'open3' );
		} elseif ( 'migrate' === $action ) {
			$images  = isset( $_POST['open3_images'] ) ? sanitize_key( wp_unslash( $_POST['open3_images'] ) ) : 'skip';
			$options = array(
				'dry_run'         => isset( $_POST['open3_dry_run'] ),
				'images'          => in_array( $images, array( 'local', 'remote', 'skip' ), true ) ? $images : 'skip',
				'menus'           => isset( $_POST['open3_menus'] ),
				'reset_templates' => isset( $_POST['open3_reset_templates'] ),
				'rollback'        => isset( $_POST['open3_rollback'] ),
			);
			if ( function_exists( 'set_time_limit' ) ) {
				set_time_limit( 300 );
			}
			$migrator = new Open3_Project_Migrator( $options );
			$rows     = $migrator->run();
			$log      = $migrator->get_log();
			$errors   = $migrator->has_errors();
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'open3 Migration', 'open3' ); ?></h1>
		<p><?php esc_html_e( 'Konvertiert alle Kindseiten der Seite „projekte“ in place zum Inhaltstyp „Projekt“. IDs, Kommentare und Meta bleiben erhalten; die Elternseite wird zum Entwurf „projekte-legacy“. Vor dem Echtlauf: Backup anlegen und Testlauf prüfen.', 'open3' ); ?></p>

		<?php if ( '' !== $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'open3_migration', 'open3_migration_nonce' ); ?>
			<input type="hidden" name="open3_migration_action" value="migrate">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Testlauf', 'open3' ); ?></th>
						<td><label><input type="checkbox" name="open3_dry_run" value="1" <?php checked( $options['dry_run'] ); ?>> <?php esc_html_e( 'Nur berichten, nichts schreiben (dry-run)', 'open3' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="open3_images"><?php esc_html_e( 'Beitragsbilder', 'open3' ); ?></label></th>
						<td>
							<select id="open3_images" name="open3_images">
								<option value="local" <?php selected( 'local', $options['images'] ); ?>><?php esc_html_e( 'lokal aus /wp-content/uploads/projekt-explorer/', 'open3' ); ?></option>
								<option value="remote" <?php selected( 'remote', $options['images'] ); ?>><?php esc_html_e( 'remote von www.open3.at herunterladen', 'open3' ); ?></option>
								<option value="skip" <?php selected( 'skip', $options['images'] ); ?>><?php esc_html_e( 'überspringen', 'open3' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Menüs', 'open3' ); ?></th>
						<td><label><input type="checkbox" name="open3_menus" value="1" <?php checked( $options['menus'] ); ?>> <?php esc_html_e( '„Hauptnavigation“ und „Footer“ anlegen und zuweisen', 'open3' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Seitentemplates', 'open3' ); ?></th>
						<td><label><input type="checkbox" name="open3_reset_templates" value="1" <?php checked( $options['reset_templates'] ); ?>> <?php esc_html_e( 'Legacy-Templates (sparpaket.php, wienbudget2009.php, woergl-visualisierungen.php, poetry-slam.php, delicious.php, open3.php) von allen Seiten entfernen', 'open3' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Rollback', 'open3' ); ?></th>
						<td><label><input type="checkbox" name="open3_rollback" value="1" <?php checked( $options['rollback'] ); ?>> <?php esc_html_e( 'Migration rückgängig machen (Projekte wieder zu Seiten, Elternseite veröffentlichen)', 'open3' ); ?></label></td>
					</tr>
				</tbody>
			</table>
			<?php submit_button( __( 'Migration ausführen', 'open3' ) ); ?>
		</form>

		<form method="post" style="margin-top:1em">
			<?php wp_nonce_field( 'open3_migration', 'open3_migration_nonce' ); ?>
			<input type="hidden" name="open3_migration_action" value="seed_terms">
			<?php submit_button( __( 'Projekttypen anlegen', 'open3' ), 'secondary', 'submit', false ); ?>
			<p class="description"><?php esc_html_e( 'Legt die sechs Projekttypen (Visualisierung, App, Umfrage, Dokumentation, Veranstaltung, Code) an, falls sie fehlen.', 'open3' ); ?></p>
		</form>

		<?php if ( is_array( $rows ) ) : ?>
			<h2><?php echo $options['dry_run'] ? esc_html__( 'Bericht (Testlauf)', 'open3' ) : esc_html__( 'Bericht', 'open3' ); ?></h2>
			<?php if ( $errors ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Es sind Fehler aufgetreten – siehe Protokoll.', 'open3' ); ?></p></div>
			<?php endif; ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Slug', 'open3' ); ?></th>
						<th><?php esc_html_e( 'Aktion', 'open3' ); ?></th>
						<th><?php esc_html_e( 'Projekttyp', 'open3' ); ?></th>
						<th><?php esc_html_e( 'Bild', 'open3' ); ?></th>
						<th><?php esc_html_e( 'Warnungen', 'open3' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( array() === $rows ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Keine Zeilen.', 'open3' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( $row['slug'] ); ?></code></td>
						<td><?php echo esc_html( $row['action'] ); ?></td>
						<td><?php echo esc_html( $row['type'] ); ?></td>
						<td><?php echo esc_html( $row['image'] ); ?></td>
						<td><?php echo esc_html( $row['warnings'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( array() !== $log ) : ?>
				<h3><?php esc_html_e( 'Protokoll', 'open3' ); ?></h3>
				<ul>
					<?php foreach ( $log as $line ) : ?>
						<li><?php echo esc_html( $line ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
