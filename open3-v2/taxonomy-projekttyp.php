<?php
/**
 * Projekttyp archive: same as the Projekte overview with the active chip.
 *
 * @package open3
 */

get_header();

$open3_term = get_queried_object();
$open3_desc = $open3_term instanceof WP_Term ? trim( (string) term_description( $open3_term ) ) : '';
?>
<div class="o3-archive">
	<span class="o3-kicker"><?php echo esc_html( open3_kicker() ); ?></span>
	<h1 class="o3-archive__title"><?php echo esc_html( open3_mod( 'open3_projekte_intro_title' ) ); ?></h1>
	<?php if ( '' !== $open3_desc ) : ?>
		<div class="o3-archive__lede"><?php echo wp_kses_post( $open3_desc ); ?></div>
	<?php else : ?>
		<p class="o3-archive__lede"><?php echo esc_html( open3_mod( 'open3_projekte_intro_text' ) ); ?></p>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/projekt/grid' ); ?>
</div>
		</main>
<?php
if ( open3_has_sidebar() ) {
	get_sidebar();
}
get_footer();
