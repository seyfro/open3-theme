<?php
/**
 * Projekt detail (dc:235-276): breadcrumb, title, lede, actions, hero + caption,
 * two-column text/Steckbrief, related projects, comments (only when present/open).
 *
 * @package open3
 */

get_header();

while ( have_posts() ) :
	the_post();
	$open3_post_id = (int) get_the_ID();
	$open3_caption = has_post_thumbnail( $open3_post_id ) ? trim( wp_strip_all_tags( (string) get_the_post_thumbnail_caption( $open3_post_id ) ) ) : '';
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-project' ); ?>>
		<?php get_template_part( 'template-parts/projekt/head', null, array( 'post_id' => $open3_post_id ) ); ?>
		<?php
		get_template_part(
			'template-parts/projekt/media',
			null,
			array(
				'post_id' => $open3_post_id,
				'size'    => 'open3-wide',
				'framed'  => true,
				'sizes'   => '(min-width: 900px) 620px, 100vw',
				'loading' => 'eager',
				'class'   => 'o3-project__media',
			)
		);
		?>
		<?php if ( '' !== $open3_caption ) : ?>
			<p class="o3-project__caption"><?php echo esc_html( $open3_caption ); ?></p>
		<?php endif; ?>
		<div class="o3-project__body">
			<div class="o3-project__text">
				<?php get_template_part( 'template-parts/content/prose' ); ?>
			</div>
			<?php get_template_part( 'template-parts/projekt/steckbrief', null, array( 'post_id' => $open3_post_id ) ); ?>
		</div>
		<?php get_template_part( 'template-parts/projekt/related', null, array( 'post_id' => $open3_post_id ) ); ?>
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
