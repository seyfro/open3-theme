<?php
/**
 * Template tags shared by templates and other modules.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Context kicker for archives, search and 404.
 *
 * @return string
 */
function open3_kicker(): string {
	if ( is_404() ) {
		return __( 'Fehler 404', 'open3' );
	}
	if ( is_search() ) {
		return __( 'Suche', 'open3' );
	}
	if ( is_home() ) {
		return __( 'Blog · Neueste Beiträge', 'open3' );
	}
	if ( is_post_type_archive( 'projekt' ) || is_tax( 'projekttyp' ) ) {
		return sprintf(
			/* translators: %d: number of projects */
			__( 'Projekte · %d', 'open3' ),
			(int) $GLOBALS['wp_query']->found_posts
		);
	}
	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return sprintf(
				/* translators: 1: category name, 2: post count */
				__( 'Kategorie · %1$s · %2$d', 'open3' ),
				$term->name,
				(int) $term->count
			);
		}
		return __( 'Kategorie', 'open3' );
	}
	if ( is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			/* translators: %s: tag name */
			return sprintf( __( 'Thema · %s', 'open3' ), $term->name );
		}
		return __( 'Thema', 'open3' );
	}
	if ( is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$tax = get_taxonomy( $term->taxonomy );
			$lab = $tax ? $tax->labels->singular_name : $term->taxonomy;
			return $lab . ' · ' . $term->name;
		}
	}
	if ( is_author() ) {
		return __( 'Autor', 'open3' );
	}
	if ( is_date() ) {
		return __( 'Archiv', 'open3' );
	}
	return __( 'Archiv', 'open3' );
}

/**
 * Reading time label, e.g. "4 Min. Lesezeit".
 *
 * @param int $post_id Post ID (0 = current).
 * @return string
 */
function open3_reading_time( int $post_id = 0 ): string {
	$post_id = $post_id > 0 ? $post_id : (int) get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );
	$text    = wp_strip_all_tags( strip_shortcodes( $content ) );
	$words   = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	$count   = is_array( $words ) ? count( $words ) : 0;
	$minutes = max( 1, (int) ceil( $count / 200 ) );

	/* translators: %d: minutes */
	return sprintf( _n( '%d Min. Lesezeit', '%d Min. Lesezeit', $minutes, 'open3' ), $minutes );
}

/**
 * Print the pagination nav (kicker + paginate_links).
 *
 * @return void
 */
function open3_pagination(): void {
	$links = paginate_links(
		array(
			'type'               => 'array',
			'mid_size'           => 2,
			'end_size'           => 1,
			'prev_text'          => __( '← Neuere', 'open3' ),
			'next_text'          => __( 'Ältere →', 'open3' ),
			'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Seite ', 'open3' ) . '</span>',
		)
	);

	if ( empty( $links ) || ! is_array( $links ) ) {
		return;
	}

	echo '<nav class="o3-pagination o3-tnum" aria-label="' . esc_attr__( 'Seitennavigation', 'open3' ) . '">';
	echo '<span class="o3-kicker o3-kicker--muted o3-pagination__label">' . esc_html__( 'Seite', 'open3' ) . '</span>';
	echo wp_kses_post( implode( "\n", $links ) );
	echo '</nav>' . "\n";
}

/**
 * Placeholder caption for a post without a thumbnail.
 *
 * @param int    $post_id  Post ID.
 * @param string $fallback Explicit caption.
 * @return string
 */
function open3_media_caption( int $post_id, string $fallback = '' ): string {
	if ( '' !== $fallback ) {
		return $fallback;
	}
	$title = (string) get_the_title( $post_id );
	return '' !== $title ? $title : __( 'Kein Bild', 'open3' );
}

/**
 * Build the .o3-media markup (image or placeholder).
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @param array  $opts    Options: ratio ('16x9'), framed (bool), sizes (string), caption (string),
 *                        class (string), tag (string), grayscale (bool), loading (string).
 * @return string
 */
function open3_get_media( int $post_id, string $size = 'open3-teaser', array $opts = array() ): string {
	$ratio     = isset( $opts['ratio'] ) ? (string) $opts['ratio'] : '16x9';
	$framed    = ! empty( $opts['framed'] );
	$grayscale = array_key_exists( 'grayscale', $opts ) ? (bool) $opts['grayscale'] : true;
	$tag       = isset( $opts['tag'] ) && in_array( $opts['tag'], array( 'div', 'span', 'figure' ), true ) ? $opts['tag'] : 'div';
	$sizes     = isset( $opts['sizes'] ) ? (string) $opts['sizes'] : '';
	$loading   = isset( $opts['loading'] ) ? (string) $opts['loading'] : 'lazy';

	$classes = array( 'o3-media', 'o3-media--' . sanitize_html_class( $ratio ) );
	if ( $framed ) {
		$classes[] = 'o3-media--framed';
	}
	if ( $grayscale ) {
		$classes[] = 'grayscale';
	}
	if ( ! empty( $opts['class'] ) ) {
		foreach ( preg_split( '/\s+/', (string) $opts['class'] ) as $extra ) {
			if ( '' !== $extra ) {
				$classes[] = sanitize_html_class( $extra );
			}
		}
	}

	$inner        = '';
	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id > 0 ) {
		$attrs = array( 'decoding' => 'async' );
		if ( '' !== $sizes ) {
			$attrs['sizes'] = $sizes;
		}
		if ( '' !== $loading ) {
			$attrs['loading'] = $loading;
		}
		$inner = wp_get_attachment_image( $thumbnail_id, $size, false, $attrs );
	}

	if ( '' === $inner ) {
		$caption = open3_media_caption( $post_id, isset( $opts['caption'] ) ? (string) $opts['caption'] : '' );
		$inner   = '<span class="o3-placeholder"><span class="o3-placeholder__caption">' . esc_html( $caption ) . '</span></span>';
	}

	return sprintf( '<%1$s class="%2$s">%3$s</%1$s>', $tag, esc_attr( implode( ' ', $classes ) ), $inner );
}

/**
 * Print the .o3-media wrapper.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @param array  $opts    See open3_get_media().
 * @return void
 */
function open3_media( int $post_id, string $size = 'open3-teaser', array $opts = array() ): void {
	echo open3_get_media( $post_id, $size, $opts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in builder.
}

/**
 * URL of the newsletter page.
 *
 * @return string
 */
function open3_newsletter_url(): string {
	$page = get_page_by_path( 'newsletter' );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return (string) get_permalink( $page );
	}
	return home_url( '/newsletter/' );
}

/**
 * Bluesky compose intent for a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function open3_bluesky_share_url( int $post_id ): string {
	$text = html_entity_decode( wp_strip_all_tags( (string) get_the_title( $post_id ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . ' ' . (string) get_permalink( $post_id );
	return 'https://bsky.app/intent/compose?text=' . rawurlencode( $text );
}

/**
 * Sidebar position from the Customizer: 'rechts' or 'links'.
 *
 * @return string
 */
function open3_sidebar_position(): string {
	$value = (string) get_theme_mod( 'open3_sidebar_position', 'rechts' );
	return 'links' === $value ? 'links' : 'rechts';
}

/**
 * Should the current view render the sidebar?
 *
 * @return bool
 */
function open3_has_sidebar(): bool {
	$has = ! ( function_exists( 'open3_is_treemap_template' ) && open3_is_treemap_template() );
	return (bool) apply_filters( 'open3_has_sidebar', $has );
}

/**
 * Contact e-mail from the Customizer.
 *
 * @return string
 */
function open3_contact_email(): string {
	$email = sanitize_email( (string) get_theme_mod( 'open3_contact_email', 'office@open3.at' ) );
	return '' !== $email ? $email : 'office@open3.at';
}

/**
 * Bluesky profile URL from the Customizer.
 *
 * @return string
 */
function open3_bluesky_profile_url(): string {
	$url = esc_url_raw( (string) get_theme_mod( 'open3_bluesky_url', 'https://bsky.app/profile/seyfro.at' ) );
	return '' !== $url ? $url : 'https://bsky.app/profile/seyfro.at';
}

/**
 * Label for the Bluesky footer link, e.g. "Bluesky @seyfro.at".
 *
 * @return string
 */
function open3_bluesky_label(): string {
	$path   = (string) wp_parse_url( open3_bluesky_profile_url(), PHP_URL_PATH );
	$handle = '';
	if ( preg_match( '#/profile/([^/]+)#', $path, $m ) ) {
		$handle = '@' . $m[1];
	}
	return '' !== $handle
		/* translators: %s: Bluesky handle */
		? sprintf( __( 'Bluesky %s', 'open3' ), $handle )
		: __( 'Bluesky', 'open3' );
}

/**
 * First category of a post (or null).
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function open3_first_category( int $post_id ): ?WP_Term {
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) ) {
		return null;
	}
	$first = reset( $cats );
	return $first instanceof WP_Term ? $first : null;
}

/**
 * Print the tag chip for a teaser/post: first category (posts) or type/label (others).
 *
 * @param int $post_id Post ID.
 * @return void
 */
function open3_the_context_tag( int $post_id ): void {
	$type = get_post_type( $post_id );

	if ( 'post' === $type ) {
		$cat = open3_first_category( $post_id );
		if ( $cat ) {
			printf(
				'<a class="tag tag-accent" href="%1$s">%2$s</a>',
				esc_url( (string) get_category_link( $cat ) ),
				esc_html( $cat->name )
			);
		}
		return;
	}

	if ( 'projekt' === $type ) {
		$label = open3_projekt_type_name( $post_id );
		if ( '' === $label ) {
			$label = __( 'Projekt', 'open3' );
		}
		echo '<span class="tag tag-neutral">' . esc_html( $label ) . '</span>';
		return;
	}

	$object = get_post_type_object( (string) $type );
	if ( $object ) {
		echo '<span class="tag tag-neutral">' . esc_html( $object->labels->singular_name ) . '</span>';
	}
}

/**
 * Projekt meta as string (wrapper around Agent C's open3_projekt_meta()).
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return string
 */
function open3_get_projekt_meta( int $post_id, string $key ): string {
	return trim( (string) open3_projekt_meta( $post_id, $key ) );
}

/**
 * Inline logo SVG from assets/img/logo-inline.svg with class and a unique title id.
 *
 * @return string Empty string when the file is missing.
 */
function open3_inline_logo_svg(): string {
	static $source = null;

	if ( null === $source ) {
		$file   = OPEN3_DIR . '/assets/img/logo-inline.svg';
		$source = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$source = (string) preg_replace( '/^\s*<\?xml[^>]*\?>\s*/i', '', $source );
		if ( '' !== $source && ! str_contains( $source, '<svg' ) ) {
			$source = '';
		}
	}

	if ( '' === $source ) {
		return '';
	}

	$svg = str_replace( 'o3-logo-title', wp_unique_id( 'o3-logo-title-' ), $source );
	return (string) preg_replace( '/<svg\b/', '<svg class="o3-logo"', $svg, 1 );
}
