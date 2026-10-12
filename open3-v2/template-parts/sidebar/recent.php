<?php
/**
 * Sidebar: eight newest posts.
 *
 * @package open3
 */

$open3_recent = get_posts(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 8,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( empty( $open3_recent ) ) {
	return;
}
?>
<section class="o3-widget o3-widget--recent">
	<span class="o3-kicker"><?php esc_html_e( 'Neu im Blog', 'open3' ); ?></span>
	<div class="o3-recent">
		<?php foreach ( $open3_recent as $open3_item ) : ?>
			<a class="o3-recent__item" href="<?php echo esc_url( (string) get_permalink( $open3_item ) ); ?>">
				<span class="o3-recent__date o3-tnum"><?php echo esc_html( (string) get_the_date( 'm/y', $open3_item ) ); ?></span>
				<span><?php echo esc_html( get_the_title( $open3_item ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
