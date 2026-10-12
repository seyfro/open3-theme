<?php
/**
 * Head cleanup, body classes, excerpt and query tweaks.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove emoji scripts, RSD, shortlink and generator output.
 *
 * @return void
 */
function open3_cleanup_head(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );

	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'open3_cleanup_head' );

/**
 * Excerpt length in words.
 *
 * @return int
 */
function open3_excerpt_length(): int {
	return 40;
}
add_filter( 'excerpt_length', 'open3_excerpt_length', 999 );

/**
 * No "[…]" after the excerpt; the teaser has its own "Weiterlesen" button.
 *
 * @return string
 */
function open3_excerpt_more(): string {
	return '';
}
add_filter( 'excerpt_more', 'open3_excerpt_more', 999 );

/**
 * Body classes: sidebar position and hero state.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function open3_body_class( array $classes ): array {
	$classes[] = 'links' === open3_sidebar_position() ? 'o3-sidebar-left' : 'o3-sidebar-right';

	if ( is_home() && ! is_paged() ) {
		$classes[] = 'has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'open3_body_class' );

/**
 * The kicker carries the context; drop "Kategorie:" etc. from the archive title.
 *
 * @return string
 */
function open3_archive_title_prefix(): string {
	return '';
}
add_filter( 'get_the_archive_title_prefix', 'open3_archive_title_prefix' );

/**
 * Search covers posts, pages and projects (placeholder promises "Beiträge, Projekte, Grundlagen").
 *
 * @param WP_Query $query The query.
 * @return void
 */
function open3_search_post_types( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	if ( ! empty( $query->get( 'post_type' ) ) ) {
		return;
	}
	$types = array( 'post', 'page' );
	if ( post_type_exists( 'projekt' ) ) {
		$types[] = 'projekt';
	}
	$query->set( 'post_type', $types );
}
add_action( 'pre_get_posts', 'open3_search_post_types' );
