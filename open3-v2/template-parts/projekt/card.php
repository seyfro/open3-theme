<?php
/**
 * Projekt grid tile.
 *
 * @package open3
 *
 * @var array $args { @type int $post_id Post ID (default: current post). }
 */

$open3_post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
if ( $open3_post_id <= 0 ) {
	return;
}
$open3_type = open3_projekt_type_name( $open3_post_id );
?>
<a class="o3-pgrid__tile" href="<?php echo esc_url( (string) get_permalink( $open3_post_id ) ); ?>">
	<?php
	open3_media(
		$open3_post_id,
		'open3-teaser',
		array(
			'sizes' => '(min-width: 900px) 25vw, (min-width: 600px) 50vw, 100vw',
			'class' => 'o3-pgrid__media',
		)
	);
	?>
	<?php if ( '' !== $open3_type ) : ?>
		<span class="o3-micro o3-pgrid__type"><?php echo esc_html( $open3_type ); ?></span>
	<?php endif; ?>
	<span class="o3-pgrid__title"><?php echo esc_html( get_the_title( $open3_post_id ) ); ?></span>
</a>
