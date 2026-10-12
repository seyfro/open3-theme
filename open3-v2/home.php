<?php
/**
 * Blog index (Start A): hero on page 1, teaser list, pagination.
 *
 * @package open3
 */

get_header();

if ( ! is_paged() ) {
	get_template_part( 'template-parts/content/hero' );
}
?>
<div class="o3-list-head<?php echo is_paged() ? ' o3-list-head--top' : ''; ?>">
	<?php if ( is_paged() ) : // page 1 has the hero H1 ?>
		<h1 class="screen-reader-text"><?php /* translators: %d: page number */ printf( esc_html__( 'Blog – Seite %d', 'open3' ), max( 1, (int) get_query_var( 'paged' ) ) ); ?></h1>
	<?php endif; ?>
	<span class="o3-kicker"><?php esc_html_e( 'Blog · Neueste Beiträge', 'open3' ); ?></span>
	<a class="o3-list-head__link" href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>"><?php esc_html_e( 'RSS-Feed', 'open3' ); ?></a>
</div>
<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/content/teaser' );
	}
	get_template_part( 'template-parts/content/pagination' );
} else {
	get_template_part( 'template-parts/content/none' );
}
?>
		</main>
<?php
if ( open3_has_sidebar() ) {
	get_sidebar();
}
get_footer();
