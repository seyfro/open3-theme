<?php
/**
 * WP-CLI: wp open3 migrate-projekte
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Konvertiert die Kindseiten von „projekte“ zum Post Type „projekt“.
 *
 * ## OPTIONS
 *
 * [--dry-run]
 * : Nur berichten, nichts schreiben.
 *
 * [--images=<modus>]
 * : Beitragsbilder importieren.
 * ---
 * default: skip
 * options:
 *   - local
 *   - remote
 *   - skip
 * ---
 *
 * [--menus]
 * : Menüs „Hauptnavigation“ und „Footer“ anlegen und zuweisen.
 *
 * [--reset-templates]
 * : Legacy-Seitentemplates von allen Seiten entfernen.
 *
 * [--rollback]
 * : Migration rückgängig machen (Projekte wieder zu Seiten).
 *
 * ## EXAMPLES
 *
 *     wp open3 migrate-projekte --dry-run
 *     wp open3 migrate-projekte --images=local --menus --reset-templates
 *     wp open3 migrate-projekte --rollback
 *
 * @param array<int,string>    $args       Positionsargumente (ungenutzt).
 * @param array<string,string> $assoc_args Flags.
 */
function open3_cli_migrate_projekte( array $args, array $assoc_args ): void {
	$options = array(
		'dry_run'         => (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false ),
		'images'          => (string) \WP_CLI\Utils\get_flag_value( $assoc_args, 'images', 'skip' ),
		'menus'           => (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'menus', false ),
		'reset_templates' => (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'reset-templates', false ),
		'rollback'        => (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'rollback', false ),
	);

	$migrator = new Open3_Project_Migrator( $options );
	$rows     = $migrator->run();

	if ( array() !== $rows ) {
		\WP_CLI\Utils\format_items( 'table', $rows, array( 'slug', 'action', 'type', 'image', 'warnings' ) );
	}
	foreach ( $migrator->get_log() as $line ) {
		WP_CLI::log( $line );
	}

	if ( $migrator->has_errors() ) {
		/* translators: %d: number of report rows */
		WP_CLI::error( sprintf( __( '%d Zeilen, Fehler aufgetreten.', 'open3' ), count( $rows ) ) );
	}
	/* translators: 1: number of report rows, 2: " (Testlauf)" or empty */
	WP_CLI::success( sprintf( __( '%1$d Zeilen verarbeitet%2$s.', 'open3' ), count( $rows ), $options['dry_run'] ? __( ' (Testlauf)', 'open3' ) : '' ) );
}

WP_CLI::add_command(
	'open3 migrate-projekte',
	'open3_cli_migrate_projekte',
	array(
		'shortdesc' => __( 'Migriert die Projekt-Kindseiten zum Post Type „projekt“.', 'open3' ),
		'synopsis'  => array(
			array(
				'type'     => 'flag',
				'name'     => 'dry-run',
				'optional' => true,
			),
			array(
				'type'     => 'assoc',
				'name'     => 'images',
				'optional' => true,
				'options'  => array( 'local', 'remote', 'skip' ),
				'default'  => 'skip',
			),
			array(
				'type'     => 'flag',
				'name'     => 'menus',
				'optional' => true,
			),
			array(
				'type'     => 'flag',
				'name'     => 'reset-templates',
				'optional' => true,
			),
			array(
				'type'     => 'flag',
				'name'     => 'rollback',
				'optional' => true,
			),
		),
	)
);
