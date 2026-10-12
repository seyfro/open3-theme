<?php
/**
 * Datenquelle des Projekt-Explorers (Sidebar). Rendering: template-parts/sidebar/explorer.php.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bildgrößen-Attribut des Explorers (Sidebar 340px ab 900px, sonst volle Breite).
 */
function open3_explorer_sizes(): string {
	return '(min-width: 900px) 340px, 100vw';
}

/**
 * Bilddaten eines Projekts für den Explorer oder null ohne Beitragsbild.
 *
 * @param int $post_id Projekt-ID.
 * @return array{src:string,srcset:string,sizes:string,alt:string,width:int,height:int}|null
 */
function open3_explorer_image( int $post_id ): ?array {
	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );
	if ( 0 === $thumbnail_id ) {
		return null;
	}
	$src = wp_get_attachment_image_src( $thumbnail_id, 'open3-teaser' );
	if ( ! is_array( $src ) || empty( $src[0] ) ) {
		return null;
	}
	$srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'open3-teaser' );
	$alt    = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

	return array(
		'src'    => (string) $src[0],
		'srcset' => is_string( $srcset ) ? $srcset : '',
		'sizes'  => open3_explorer_sizes(),
		'alt'    => is_string( $alt ) ? $alt : '',
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
	);
}

/**
 * Alle sichtbaren, veröffentlichten Projekte in zufälliger Reihenfolge.
 *
 * @return array<int,array{id:int,url:string,title:string,short:string,type:string,img:array|null}>
 */
function open3_explorer_items(): array {
	$posts = get_posts(
		array(
			'post_type'              => 'projekt',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_term_cache' => true,
			'ignore_sticky_posts'    => true,
			'meta_query'             => open3_projekt_visible_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);
	if ( ! is_array( $posts ) || array() === $posts ) {
		return array();
	}
	shuffle( $posts );

	$items = array();
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		// Klartext: the_title-Filter (wptexturize, convert_chars) hinterlassen Entities, die main.js per textContent wörtlich anzeigen würde.
		$title   = html_entity_decode( wp_strip_all_tags( (string) get_the_title( $post ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$kurz    = function_exists( 'open3_migration_projects' ) ? (string) ( open3_migration_projects()[ $post->post_name ][2] ?? '' ) : '';
		$items[] = array(
			'id'    => (int) $post->ID,
			'url'   => (string) get_permalink( $post ),
			'title' => $title,
			'short' => '' !== $kurz ? $kurz : $title,
			'type'  => open3_projekt_type_name( $post->ID ),
			'img'   => open3_explorer_image( $post->ID ),
		);
	}
	return $items;
}
