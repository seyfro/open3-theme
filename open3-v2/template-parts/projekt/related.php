<?php
/**
 * "Verwandte Projekte": up to three visible projects, same projekttyp first, random order.
 *
 * Omits the whole section when nothing is found.
 *
 * @package open3
 *
 * @var array $args { @type int $post_id Post ID (default: current post). @type int $count Max cards (default 3). }
 */

$open3_post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$open3_count   = isset( $args['count'] ) ? max( 1, (int) $args['count'] ) : 3;
if ( $open3_post_id <= 0 ) {
	return;
}

$open3_base = array(
	'post_type'              => 'projekt',
	'post_status'            => 'publish',
	'posts_per_page'         => $open3_count,
	'post__not_in'           => array( $open3_post_id ),
	'orderby'                => 'rand',
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'meta_query'             => open3_projekt_visible_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
);

$open3_related = array();
$open3_terms   = wp_get_post_terms( $open3_post_id, 'projekttyp', array( 'fields' => 'ids' ) );
if ( ! is_wp_error( $open3_terms ) && array() !== $open3_terms ) {
	$open3_related = get_posts(
		array_merge(
			$open3_base,
			array(
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'projekttyp',
						'field'    => 'term_id',
						'terms'    => array_map( 'intval', $open3_terms ),
					),
				),
			)
		)
	);
}

if ( count( $open3_related ) < $open3_count ) {
	$open3_exclude = array( $open3_post_id );
	foreach ( $open3_related as $open3_found ) {
		$open3_exclude[] = (int) $open3_found->ID;
	}
	$open3_fill = get_posts(
		array_merge(
			$open3_base,
			array(
				'posts_per_page' => $open3_count - count( $open3_related ),
				'post__not_in'   => $open3_exclude,
			)
		)
	);
	$open3_related = array_merge( $open3_related, $open3_fill );
}

if ( array() === $open3_related ) {
	return;
}
?>
<section class="o3-related" aria-labelledby="o3-related-heading">
	<h2 id="o3-related-heading" class="o3-kicker"><?php esc_html_e( 'Verwandte Projekte', 'open3' ); ?></h2>
	<div class="o3-related__grid">
		<?php foreach ( $open3_related as $open3_item ) : ?>
			<?php
			$open3_item_id   = (int) $open3_item->ID;
			$open3_item_type = open3_projekt_type_name( $open3_item_id );
			?>
			<a class="card o3-related__card" href="<?php echo esc_url( (string) get_permalink( $open3_item_id ) ); ?>">
				<?php if ( '' !== $open3_item_type ) : ?>
					<span class="card-kicker"><?php echo esc_html( $open3_item_type ); ?></span>
				<?php endif; ?>
				<span class="card-title"><?php echo esc_html( get_the_title( $open3_item_id ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
