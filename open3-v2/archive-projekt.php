<?php
/**
 * Projekte overview: intro, filter chips, grid.
 *
 * @package open3
 */

get_header();
?>
<div class="o3-archive">
	<span class="o3-kicker"><?php echo esc_html( open3_kicker() ); ?></span>
	<h1 class="o3-archive__title"><?php echo esc_html( open3_mod( 'open3_projekte_intro_title' ) ); ?></h1>
	<p class="o3-archive__lede"><?php echo esc_html( open3_mod( 'open3_projekte_intro_text' ) ); ?></p>
	<?php get_template_part( 'template-parts/projekt/grid' ); ?>
</div>
		</main>
<?php
if ( open3_has_sidebar() ) {
	get_sidebar();
}
get_footer();
