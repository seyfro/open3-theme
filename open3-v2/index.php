<?php
/**
 * Fallback template: teaser list without hero.
 *
 * @package open3
 */

get_header();
?>
<div class="o3-list-head o3-list-head--top">
	<h1 class="screen-reader-text"><?php echo esc_html( is_paged() ? sprintf( /* translators: 1: list context, 2: page number */ __( '%1$s – Seite %2$d', 'open3' ), open3_kicker(), max( 1, (int) get_query_var( 'paged' ) ) ) : open3_kicker() ); ?></h1>
	<span class="o3-kicker"><?php echo esc_html( open3_kicker() ); ?></span>
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
