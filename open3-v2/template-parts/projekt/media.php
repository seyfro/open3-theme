<?php
/**
 * Projekt media wrapper (thin part around open3_media()).
 *
 * @package open3
 *
 * @var array $args {
 *     @type int    $post_id   Post ID (default: current post).
 *     @type string $size      Image size (default: open3-teaser).
 *     @type bool   $framed    2px frame.
 *     @type string $ratio     '16x9'.
 *     @type string $sizes     sizes attribute.
 *     @type string $caption   Placeholder caption fallback.
 *     @type string $class     Extra classes.
 *     @type string $loading   'lazy'|'eager'.
 *     @type bool   $grayscale Apply the grayscale class (default true).
 * }
 */

$args            = is_array( $args ?? null ) ? $args : array();
$open3_post_id   = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$open3_size      = isset( $args['size'] ) ? (string) $args['size'] : 'open3-teaser';
$open3_media_opt = array();

foreach ( array( 'framed', 'ratio', 'sizes', 'caption', 'class', 'loading', 'grayscale', 'tag' ) as $open3_key ) {
	if ( array_key_exists( $open3_key, $args ) ) {
		$open3_media_opt[ $open3_key ] = $args[ $open3_key ];
	}
}

if ( $open3_post_id > 0 ) {
	open3_media( $open3_post_id, $open3_size, $open3_media_opt );
}
