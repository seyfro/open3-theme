<?php
/**
 * Front-end assets.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asset version: filemtime while debugging, theme version otherwise.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function open3_asset_version( string $relative ): string {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$file = OPEN3_DIR . '/' . ltrim( $relative, '/' );
		if ( is_readable( $file ) ) {
			$time = filemtime( $file );
			if ( false !== $time ) {
				return OPEN3_VERSION . '.' . $time;
			}
		}
	}
	return OPEN3_VERSION;
}

/**
 * Enqueue styles and scripts.
 *
 * @return void
 */
function open3_enqueue(): void {
	wp_enqueue_style(
		'open3-design-system',
		OPEN3_URI . '/assets/css/design-system.css',
		array(),
		open3_asset_version( 'assets/css/design-system.css' )
	);

	$theme_deps = array( 'open3-design-system', 'wp-block-library' );
	if ( wp_style_is( 'global-styles', 'enqueued' ) ) {
		$theme_deps[] = 'global-styles';
	}

	wp_enqueue_style(
		'open3-theme',
		OPEN3_URI . '/assets/css/theme.css',
		$theme_deps,
		open3_asset_version( 'assets/css/theme.css' )
	);

	wp_enqueue_script(
		'open3-main',
		OPEN3_URI . '/assets/js/main.js',
		array(),
		open3_asset_version( 'assets/js/main.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'open3_enqueue', 20 );

/**
 * Remove the no-js class as early as possible (before CSS renders the mobile nav).
 *
 * @return void
 */
function open3_no_js_script(): void {
	echo "<script>document.documentElement.classList.remove('no-js');</script>\n";
}
add_action( 'wp_head', 'open3_no_js_script', 1 );

/**
 * Preload the two self-hosted Archivo font files.
 *
 * @return void
 */
function open3_preload_fonts(): void {
	$fonts = array(
		'assets/fonts/Archivo-latin.woff2',
		'assets/fonts/Archivo-latin-ext.woff2',
	);
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( OPEN3_URI . '/' . $font )
		);
	}
}
add_action( 'wp_head', 'open3_preload_fonts', 2 );
