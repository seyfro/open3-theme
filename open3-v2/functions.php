<?php
/**
 * open3 theme bootstrap.
 *
 * Defines the theme constants and loads the modules from inc/ in a fixed order.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'OPEN3_VERSION' ) ) {
	$open3_theme = wp_get_theme( get_template() ); // Verzeichnisname kann „open3-v2“ sein
	define( 'OPEN3_VERSION', $open3_theme->exists() ? (string) $open3_theme->get( 'Version' ) : '2.0.0' );
	unset( $open3_theme );
}

if ( ! defined( 'OPEN3_DIR' ) ) {
	define( 'OPEN3_DIR', untrailingslashit( get_template_directory() ) );
}

if ( ! defined( 'OPEN3_URI' ) ) {
	define( 'OPEN3_URI', untrailingslashit( get_template_directory_uri() ) );
}

/**
 * Module list (order is binding, see build contract).
 */
$open3_modules = array(
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/cleanup.php',
	'inc/nav.php',
	'inc/template-tags.php',
	'inc/customizer.php',
	'inc/cpt-projekt.php',
	'inc/meta-projekt.php',
	'inc/meta-termin.php',
	'inc/explorer.php',
	'inc/comments.php',
	'inc/redirects.php',
	'inc/treemap.php',
	'inc/migration/class-open3-project-migrator.php',
	'inc/migration/admin.php',
	'inc/migration/cli.php',
);

foreach ( $open3_modules as $open3_module ) {
	require OPEN3_DIR . '/' . $open3_module;
}

unset( $open3_modules, $open3_module );
