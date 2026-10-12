<?php
/**
 * Archives (category, tag, author, date): Start A without hero.
 *
 * @package open3
 */

get_header();

$open3_feed = '';
if ( is_category() || is_tag() || is_tax() ) {
	$open3_term = get_queried_object();
	if ( $open3_term instanceof WP_Term ) {
		$open3_feed = (string) get_term_feed_link( $open3_term->term_id, $open3_term->taxonomy );
	}
} elseif ( is_author() ) {
	$open3_feed = (string) get_author_feed_link( (int) get_queried_object_id() );
}
?>
<div class="o3-list-head o3-list-head--top">
	<span class="o3-kicker"><?php echo esc_html( open3_kicker() ); ?></span>
	<?php if ( '' !== $open3_feed ) : ?>
		<a class="o3-list-head__link" href="<?php echo esc_url( $open3_feed ); ?>"><?php esc_html_e( 'RSS-Feed', 'open3' ); ?></a>
	<?php endif; ?>
</div>
<header class="o3-archive-head">
	<h1 class="o3-archive-head__title"><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
	<?php
	$open3_description = get_the_archive_description();
	if ( '' !== trim( (string) $open3_description ) ) :
		?>
		<div class="o3-archive-head__desc"><?php echo wp_kses_post( $open3_description ); ?></div>
	<?php endif; ?>
</header>
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
