<?php
/**
 * Sidebar: categories with counts.
 *
 * @package open3
 */

$open3_categories = get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'hide_empty' => true,
	)
);

if ( empty( $open3_categories ) ) {
	return;
}
?>
<section class="o3-widget o3-widget--categories">
	<span class="o3-kicker"><?php esc_html_e( 'Kategorien', 'open3' ); ?></span>
	<div class="o3-cats">
		<?php foreach ( $open3_categories as $open3_category ) : ?>
			<a class="o3-cats__item" href="<?php echo esc_url( (string) get_category_link( $open3_category ) ); ?>">
				<span><?php echo esc_html( $open3_category->name ); ?></span>
				<span class="o3-cats__count o3-tnum"><?php echo esc_html( (string) (int) $open3_category->count ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
