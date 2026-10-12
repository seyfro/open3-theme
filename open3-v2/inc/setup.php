<?php
/**
 * Theme setup: supports, image sizes, menus, text domain.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and settings.
 *
 * @return void
 */
function open3_setup(): void {
	load_theme_textdomain( 'open3', OPEN3_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'post-thumbnails', array( 'post', 'projekt' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'               => 32,
			'width'                => 212,
			'flex-width'           => true,
			'flex-height'          => true,
			'unlink-homepage-logo' => false,
		)
	);

	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/design-system.css', 'assets/css/editor.css' ) );
	remove_theme_support( 'core-block-patterns' );

	add_image_size( 'open3-teaser', 640, 360, true );
	add_image_size( 'open3-wide', 1280, 720, true );

	register_nav_menus(
		array(
			'primary' => __( 'Hauptnavigation', 'open3' ),
			'footer'  => __( 'Footer-Sitemap', 'open3' ),
		)
	);

	$GLOBALS['content_width'] = 800;
}
add_action( 'after_setup_theme', 'open3_setup' );

/**
 * Offer the custom image sizes in the media dialog.
 *
 * @param array<string,string> $sizes Registered size labels.
 * @return array<string,string>
 */
function open3_image_size_names( array $sizes ): array {
	$sizes['open3-teaser'] = __( 'Teaser 16:9 (640 px)', 'open3' );
	$sizes['open3-wide']   = __( 'Breit 16:9 (1280 px)', 'open3' );
	return $sizes;
}
add_filter( 'image_size_names_choose', 'open3_image_size_names' );

/**
 * Register the "Graustufen" block style for core images.
 *
 * @return void
 */
function open3_register_block_styles(): void {
	if ( function_exists( 'register_block_style' ) ) {
		register_block_style(
			'core/image',
			array(
				'name'  => 'grayscale',
				'label' => __( 'Graustufen', 'open3' ),
			)
		);
	}
}
add_action( 'init', 'open3_register_block_styles' );
