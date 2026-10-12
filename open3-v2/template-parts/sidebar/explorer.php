<?php
/**
 * Sidebar: Projekt-Explorer (server-rendered item 0 + JSON island).
 *
 * @package open3
 */

$open3_items = function_exists( 'open3_explorer_items' ) ? open3_explorer_items() : array();
$open3_items = is_array( $open3_items ) ? array_values( $open3_items ) : array();

$open3_archive_url = post_type_exists( 'projekt' ) ? (string) get_post_type_archive_link( 'projekt' ) : home_url( '/projekte/' );
if ( '' === $open3_archive_url ) {
	$open3_archive_url = home_url( '/projekte/' );
}

/**
 * Normalise one raw item to the contract shape.
 *
 * @param array $raw Raw item.
 * @return array{id:int,url:string,title:string,short:string,type:string,img:?array}
 */
$open3_normalize = static function ( array $raw ): array {
	$img = $raw['img'] ?? $raw['image'] ?? null;
	if ( is_array( $img ) && ! empty( $img['src'] ) ) {
		$img = array(
			'src'    => (string) $img['src'],
			'srcset' => (string) ( $img['srcset'] ?? '' ),
			'sizes'  => (string) ( $img['sizes'] ?? '(min-width: 900px) 340px, 100vw' ),
			'alt'    => (string) ( $img['alt'] ?? '' ),
			'width'  => (int) ( $img['width'] ?? 0 ),
			'height' => (int) ( $img['height'] ?? 0 ),
		);
	} else {
		$img = null;
	}
	$title = (string) ( $raw['title'] ?? '' );
	return array(
		'id'    => (int) ( $raw['id'] ?? 0 ),
		'url'   => (string) ( $raw['url'] ?? '' ),
		'title' => $title,
		'short' => (string) ( $raw['short'] ?? $title ),
		'type'  => (string) ( $raw['type'] ?? '' ),
		'img'   => $img,
	);
};

$open3_items = array_map( $open3_normalize, array_filter( $open3_items, 'is_array' ) );
$open3_first = $open3_items[0] ?? null;
?>
<section class="o3-widget o3-explorer" data-index="0">
	<div class="o3-explorer__head">
		<span class="o3-kicker"><?php esc_html_e( 'Projekt-Explorer', 'open3' ); ?></span>
		<a class="o3-explorer__all" href="<?php echo esc_url( $open3_archive_url ); ?>"><?php esc_html_e( 'alle anzeigen', 'open3' ); ?></a>
	</div>
	<?php if ( $open3_first ) : ?>
		<a class="o3-explorer__media o3-media o3-media--16x9 o3-media--framed grayscale" href="<?php echo esc_url( $open3_first['url'] ); ?>">
			<?php if ( $open3_first['img'] ) : ?>
				<img src="<?php echo esc_url( $open3_first['img']['src'] ); ?>"
					<?php if ( '' !== $open3_first['img']['srcset'] ) : ?>
						srcset="<?php echo esc_attr( $open3_first['img']['srcset'] ); ?>"
					<?php endif; ?>
					sizes="<?php echo esc_attr( $open3_first['img']['sizes'] ); ?>"
					alt="<?php echo esc_attr( $open3_first['img']['alt'] ); ?>"
					<?php if ( $open3_first['img']['width'] > 0 && $open3_first['img']['height'] > 0 ) : ?>
						width="<?php echo esc_attr( (string) $open3_first['img']['width'] ); ?>" height="<?php echo esc_attr( (string) $open3_first['img']['height'] ); ?>"
					<?php endif; ?>
					loading="lazy" decoding="async">
			<?php else : ?>
				<span class="o3-placeholder"><span class="o3-placeholder__caption"><?php echo esc_html( $open3_first['short'] ); ?></span></span>
			<?php endif; ?>
		</a>
		<span class="o3-micro o3-explorer__type"><?php echo esc_html( $open3_first['type'] ); ?></span>
		<a class="o3-explorer__title" href="<?php echo esc_url( $open3_first['url'] ); ?>" aria-live="polite"><?php echo esc_html( $open3_first['title'] ); ?></a>
		<button type="button" class="btn btn-ghost o3-explorer__next"<?php echo count( $open3_items ) < 2 ? ' disabled' : ''; ?>><?php esc_html_e( 'Nächstes Projekt →', 'open3' ); ?></button>
	<?php else : ?>
		<a class="o3-explorer__media o3-media o3-media--16x9 o3-media--framed grayscale" href="<?php echo esc_url( $open3_archive_url ); ?>">
			<span class="o3-placeholder"><span class="o3-placeholder__caption"><?php esc_html_e( 'Projekte', 'open3' ); ?></span></span>
		</a>
		<span class="o3-micro o3-explorer__type"></span>
		<a class="o3-explorer__title" href="<?php echo esc_url( $open3_archive_url ); ?>" aria-live="polite"><?php esc_html_e( 'Alle Projekte ansehen', 'open3' ); ?></a>
		<button type="button" class="btn btn-ghost o3-explorer__next" disabled><?php esc_html_e( 'Nächstes Projekt →', 'open3' ); ?></button>
	<?php endif; ?>
</section>
<?php if ( ! empty( $open3_items ) ) : ?>
	<script type="application/json" id="o3-explorer-data"><?php echo wp_json_encode( $open3_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
<?php endif; ?>
