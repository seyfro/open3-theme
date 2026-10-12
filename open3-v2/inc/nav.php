<?php
/**
 * Navigation: menu filters (no walker) and fallback renderers.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the current request a "Blog" context (home, posts, post archives)?
 *
 * @return bool
 */
function open3_is_blog_context(): bool {
	return is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_date() || is_author();
}

/**
 * Is the current request a "Projekte" context (archive, single, type)?
 *
 * @return bool
 */
function open3_is_projekt_context(): bool {
	return is_post_type_archive( 'projekt' ) || is_singular( 'projekt' ) || is_tax( 'projekttyp' );
}

/**
 * Normalise a URL for comparison (scheme-less, no trailing slash, lower-case host).
 *
 * @param string $url URL.
 * @return string
 */
function open3_nav_normalize_url( string $url ): string {
	$url = untrailingslashit( trim( $url ) );
	$url = preg_replace( '#^https?://#i', '', $url );
	return strtolower( (string) $url );
}

/**
 * Decide whether a menu item is active for the current request.
 *
 * @param WP_Post|object $item Menu item.
 * @return bool
 */
function open3_nav_item_is_active( $item ): bool {
	if ( ! is_object( $item ) ) {
		return false;
	}
	$type   = isset( $item->type ) ? (string) $item->type : '';
	$object = isset( $item->object ) ? (string) $item->object : '';
	$url    = isset( $item->url ) ? (string) $item->url : '';

	if ( open3_is_blog_context() ) {
		if ( '' !== $url && open3_nav_normalize_url( $url ) === open3_nav_normalize_url( home_url( '/' ) ) ) {
			return true;
		}
		if ( 'post_type' === $type && 'page' === $object && (int) get_option( 'page_for_posts' ) > 0
			&& (int) ( $item->object_id ?? 0 ) === (int) get_option( 'page_for_posts' ) ) {
			return true;
		}
	}

	if ( open3_is_projekt_context() ) {
		if ( 'post_type_archive' === $type && 'projekt' === $object ) {
			return true;
		}
		$archive = get_post_type_archive_link( 'projekt' );
		if ( $archive && '' !== $url && open3_nav_normalize_url( $url ) === open3_nav_normalize_url( $archive ) ) {
			return true;
		}
	}

	if ( 'post_type' === $type && 'page' === $object && is_page() ) {
		$page_id = (int) ( $item->object_id ?? 0 );
		$current = get_queried_object_id();
		if ( $page_id > 0 && ( $page_id === $current || in_array( $page_id, get_post_ancestors( $current ), true ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Add BEM classes to primary menu items and mark context-active items.
 *
 * @param string[] $classes Item classes.
 * @param object   $item    Menu item.
 * @param object   $args    wp_nav_menu() args.
 * @param int      $depth   Depth.
 * @return string[]
 */
function open3_nav_menu_css_class( $classes, $item, $args = array(), $depth = 0 ): array {
	$classes  = is_array( $classes ) ? $classes : array();
	$args     = (object) $args;
	$location = isset( $args->theme_location ) ? (string) $args->theme_location : '';

	if ( 'primary' === $location ) {
		$classes[] = 'o3-nav__item';
	}

	if ( open3_nav_item_is_active( $item ) ) {
		$classes[] = 'current-menu-item';
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'nav_menu_css_class', 'open3_nav_menu_css_class', 10, 4 );

/**
 * Add the link class and aria-current to active items.
 *
 * @param array<string,string> $atts  Link attributes.
 * @param object               $item  Menu item.
 * @param object               $args  wp_nav_menu() args.
 * @param int                  $depth Depth.
 * @return array<string,string>
 */
function open3_nav_menu_link_attributes( $atts, $item, $args = array(), $depth = 0 ): array {
	$atts     = is_array( $atts ) ? $atts : array();
	$args     = (object) $args;
	$location = isset( $args->theme_location ) ? (string) $args->theme_location : '';

	if ( 'primary' === $location ) {
		$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' o3-nav__link' );
	}

	$item_classes = is_object( $item ) && isset( $item->classes ) ? (array) $item->classes : array();
	$is_current   = ( is_object( $item ) && ! empty( $item->current ) ) || in_array( 'current-menu-item', $item_classes, true ) || open3_nav_item_is_active( $item );

	if ( $is_current ) {
		$atts['aria-current'] = 'page';
	} elseif ( isset( $atts['aria-current'] ) && '' === $atts['aria-current'] ) {
		unset( $atts['aria-current'] );
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'open3_nav_menu_link_attributes', 10, 4 );

/**
 * The seven design items, resolved by slug. Missing pages are skipped.
 *
 * @return array<int,array{label:string,url:string,active:bool}>
 */
function open3_nav_fallback_items(): array {
	$items = array();

	$items[] = array(
		'label'  => __( 'Blog', 'open3' ),
		'url'    => home_url( '/' ),
		'active' => open3_is_blog_context(),
	);

	$archive = post_type_exists( 'projekt' ) ? get_post_type_archive_link( 'projekt' ) : false;
	if ( $archive ) {
		$items[] = array(
			'label'  => __( 'Projekte', 'open3' ),
			'url'    => $archive,
			'active' => open3_is_projekt_context(),
		);
	}

	$pages = array(
		'grundlagen'   => __( 'Grundlagen', 'open3' ),
		'openevents'   => __( 'openEvents', 'open3' ),
		'opennetwork'  => __( 'openNetwork', 'open3' ),
		'medienspiegel' => __( 'Medienspiegel', 'open3' ),
		'uber-open3'   => __( 'Über open3', 'open3' ),
	);

	$current_id = is_page() ? (int) get_queried_object_id() : 0;
	$ancestors  = $current_id ? get_post_ancestors( $current_id ) : array();

	foreach ( $pages as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
			continue;
		}
		$items[] = array(
			'label'  => $label,
			'url'    => (string) get_permalink( $page ),
			'active' => $current_id > 0 && ( $current_id === (int) $page->ID || in_array( (int) $page->ID, $ancestors, true ) ),
		);
	}

	return $items;
}

/**
 * Render a list of fallback items as <li><a> markup.
 *
 * @param array<int,array{label:string,url:string,active:bool}> $items   Items.
 * @param bool                                                  $primary Add o3-nav__* classes.
 * @return string
 */
function open3_nav_render_items( array $items, bool $primary ): string {
	$html = '';
	foreach ( $items as $item ) {
		$li_classes = array();
		$a_classes  = array();
		if ( $primary ) {
			$li_classes[] = 'o3-nav__item';
			$a_classes[]  = 'o3-nav__link';
		}
		$li_classes[] = 'menu-item';
		if ( ! empty( $item['active'] ) ) {
			$li_classes[] = 'current-menu-item';
		}

		$html .= sprintf(
			'<li class="%1$s"><a%2$s href="%3$s"%4$s>%5$s</a></li>' . "\n",
			esc_attr( implode( ' ', $li_classes ) ),
			$a_classes ? ' class="' . esc_attr( implode( ' ', $a_classes ) ) . '"' : '',
			esc_url( $item['url'] ),
			! empty( $item['active'] ) ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	return $html;
}

/**
 * Wrap rendered items in the items_wrap of the given wp_nav_menu() args.
 *
 * @param string       $items_html Rendered <li> items.
 * @param array|object $args       wp_nav_menu() args.
 * @param string       $default_wrap Fallback wrap.
 * @return string
 */
function open3_nav_wrap_items( string $items_html, $args, string $default_wrap ): string {
	$args = (array) $args;
	$wrap = ! empty( $args['items_wrap'] ) ? (string) $args['items_wrap'] : $default_wrap;
	$id   = ! empty( $args['menu_id'] ) ? (string) $args['menu_id'] : '';
	$cls  = ! empty( $args['menu_class'] ) ? (string) $args['menu_class'] : '';
	return sprintf( $wrap, esc_attr( $id ), esc_attr( $cls ), $items_html );
}

/**
 * Fallback for the primary location: the seven design items.
 *
 * @param array $args wp_nav_menu() args.
 * @return string|void
 */
function open3_nav_fallback( array $args ) {
	$html = open3_nav_wrap_items(
		open3_nav_render_items( open3_nav_fallback_items(), true ),
		$args,
		'<ul class="o3-nav__list">%3$s</ul>'
	);

	if ( ! empty( $args['echo'] ) ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
		return;
	}
	return $html;
}

/**
 * Fallback for the footer location: primary menu (or its fallback) + Newsletter.
 *
 * @param array $args wp_nav_menu() args.
 * @return string|void
 */
function open3_footer_fallback( array $args ) {
	$items_html = '';
	$locations  = get_nav_menu_locations();

	if ( ! empty( $locations['primary'] ) ) {
		$menu = wp_get_nav_menu_object( $locations['primary'] );
		if ( $menu ) {
			$items_html = (string) wp_nav_menu(
				array(
					'menu'           => $menu,
					'theme_location' => 'footer',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'echo'           => false,
					'fallback_cb'    => false,
				)
			);
		}
	}

	if ( '' === trim( $items_html ) ) {
		$items_html = open3_nav_render_items( open3_nav_fallback_items(), false );
	}

	$items_html .= sprintf(
		'<li class="menu-item"><a href="%1$s">%2$s</a></li>' . "\n",
		esc_url( open3_newsletter_url() ),
		esc_html__( 'Newsletter', 'open3' )
	);

	$html = open3_nav_wrap_items( $items_html, $args, '<ul class="o3-footer__links">%3$s</ul>' );

	if ( ! empty( $args['echo'] ) ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
		return;
	}
	return $html;
}
