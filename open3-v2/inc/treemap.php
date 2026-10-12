<?php
/**
 * Treemap projects (Stage 2): JIT enqueue, inline boot, treemap.css, full-width body class.
 *
 * Only the two slugs in open3_treemap_slugs() get the assets; each has its own
 * single-projekt-{slug}.php template (WP ≥ 4.4 hierarchy). Renaming a slug in the editor
 * silently drops both the template and the scripts.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slug → list of data-script handles (in load order; the last one carries the boot script).
 *
 * @return array<string,string[]>
 */
function open3_treemap_slugs(): array {
	return array(
		'sparpaket'                       => array( 'open3-treemap-gesamthaushalt', 'open3-treemap-kuerzungen' ),
		'wien-budget-2009-visualisierung' => array( 'open3-treemap-wien2009' ),
	);
}

/**
 * Data-script handle → [ file relative to assets/js/treemap/, init function names ].
 *
 * @return array<string,array{file:string,init:string[]}>
 */
function open3_treemap_scripts(): array {
	return array(
		'open3-treemap-gesamthaushalt' => array(
			'file' => 'gesamthaushalt-2011-2014-v1.0.js',
			'init' => array( 'init2' ),
		),
		'open3-treemap-kuerzungen'     => array(
			'file' => 'budgetkuerzungen-2011-2014-v3.1.js',
			'init' => array( 'init' ),
		),
		'open3-treemap-wien2009'       => array(
			'file' => 'wien-budget2009-ausgaben.js',
			'init' => array( 'init' ),
		),
	);
}

/**
 * Slug of the queried treemap project, or '' when the current request is not one.
 *
 * @return string
 */
function open3_treemap_current_slug(): string {
	if ( ! is_singular( 'projekt' ) ) {
		return '';
	}
	$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	if ( ! array_key_exists( $slug, open3_treemap_slugs() ) ) {
		return '';
	}
	// Guard: without the slug template the page falls back to single-projekt.php and must not load the scripts.
	return '' !== locate_template( 'single-projekt-' . $slug . '.php' ) ? $slug : '';
}

/**
 * Is the current request one of the treemap project pages?
 *
 * @return bool
 */
function open3_is_treemap_template(): bool {
	return '' !== open3_treemap_current_slug();
}

/**
 * Treemap pages are full width.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function open3_treemap_body_class( array $classes ): array {
	if ( open3_is_treemap_template() ) {
		$classes[] = 'no-sidebar';
		$classes[] = 'o3-treemap-page';
	}
	return $classes;
}
add_filter( 'body_class', 'open3_treemap_body_class' );

/**
 * Enqueue JIT, the data file(s), the treemap stylesheet and the inline boot script.
 *
 * Scripts go to the footer without a loading strategy: the boot script is an "after"
 * inline script, which would make WP drop `defer` anyway, and JIT reads the container
 * size (offsetWidth/offsetHeight) at init time, so the DOM must exist.
 *
 * @return void
 */
function open3_treemap_enqueue(): void {
	$slug = open3_treemap_current_slug();
	if ( '' === $slug ) {
		return;
	}

	$handles = open3_treemap_slugs()[ $slug ];
	$scripts = open3_treemap_scripts();
	$base    = 'assets/js/treemap/';

	wp_enqueue_style(
		'open3-treemap',
		OPEN3_URI . '/assets/css/treemap.css',
		array( 'open3-theme' ),
		open3_asset_version( 'assets/css/treemap.css' )
	);

	wp_register_script(
		'open3-jit',
		OPEN3_URI . '/' . $base . 'jit-treemap-c.js',
		array(),
		open3_asset_version( $base . 'jit-treemap-c.js' ),
		true
	);
	wp_add_inline_script(
		'open3-jit',
		'window.o3TreemapBase = ' . wp_json_encode( OPEN3_URI . '/' . $base . 'img/' ) . ';',
		'before'
	);
	wp_enqueue_script( 'open3-jit' );

	// Data files are chained (each depends on the previous one) so the boot script,
	// attached to the last handle, is guaranteed to print after all of them.
	$inits = array();
	$last  = '';
	$prev  = 'open3-jit';
	foreach ( $handles as $handle ) {
		if ( ! isset( $scripts[ $handle ] ) ) {
			continue;
		}
		wp_enqueue_script(
			$handle,
			OPEN3_URI . '/' . $base . $scripts[ $handle ]['file'],
			array( $prev ),
			open3_asset_version( $base . $scripts[ $handle ]['file'] ),
			true
		);
		$inits = array_merge( $inits, $scripts[ $handle ]['init'] );
		$last  = $handle;
		$prev  = $handle;
	}

	if ( '' === $last || array() === $inits ) {
		return;
	}

	$calls = '';
	foreach ( array_unique( $inits ) as $fn ) {
		$fn     = preg_replace( '/[^a-z0-9_]/i', '', $fn );
		$calls .= 'if(typeof ' . $fn . '==="function"){' . $fn . '();}';
	}
	$boot = '(function(){function o3TreemapBoot(){' . $calls . '}'
		. 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",o3TreemapBoot);}else{o3TreemapBoot();}})();';

	wp_add_inline_script( $last, $boot, 'after' );
}
add_action( 'wp_enqueue_scripts', 'open3_treemap_enqueue', 30 );
