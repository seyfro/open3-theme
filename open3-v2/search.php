<?php
/**
 * Search results.
 *
 * @package open3
 */

get_header();

$open3_found = (int) $GLOBALS['wp_query']->found_posts;
?>
<div class="o3-list-head o3-list-head--top">
	<span class="o3-kicker"><?php echo esc_html( open3_kicker() ); ?></span>
</div>
<header class="o3-archive-head">
	<h1 class="o3-archive-head__title">
		<?php
		printf(
			/* translators: 1: search query, 2: number of results */
			esc_html( _n( 'Suchergebnisse für „%1$s“ (%2$d Treffer)', 'Suchergebnisse für „%1$s“ (%2$d Treffer)', $open3_found, 'open3' ) ),
			esc_html( get_search_query( false ) ),
			(int) $open3_found
		);
		?>
	</h1>
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
