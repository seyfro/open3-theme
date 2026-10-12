<?php
/**
 * Generic page.
 *
 * @package open3
 */

get_header();

while ( have_posts() ) :
	the_post();
	$open3_post_id = (int) get_the_ID();
	$open3_parent  = wp_get_post_parent_id( $open3_post_id );
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-post o3-page-content' ); ?>>
		<?php if ( $open3_parent > 0 ) : ?>
			<div class="o3-post__crumbs">
				<a href="<?php echo esc_url( (string) get_permalink( $open3_parent ) ); ?>"><?php echo esc_html( get_the_title( $open3_parent ) ); ?></a>
			</div>
		<?php endif; ?>
		<h1 class="o3-post__title"><?php the_title(); ?></h1>
		<?php get_template_part( 'template-parts/content/prose' ); ?>
		<?php
		if ( comments_open() || get_comments_number() > 0 ) {
			comments_template();
		}
		?>
	</article>
	<?php
endwhile;
?>
		</main>
<?php
if ( open3_has_sidebar() ) {
	get_sidebar();
}
get_footer();
