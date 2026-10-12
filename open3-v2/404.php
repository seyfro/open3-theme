<?php
/**
 * 404: Start A shell without hero.
 *
 * @package open3
 */

get_header();
?>
<div class="o3-list-head o3-list-head--top">
	<span class="o3-kicker"><?php echo esc_html( open3_kicker() ); ?></span>
</div>
<header class="o3-archive-head">
	<h1 class="o3-archive-head__title"><?php esc_html_e( 'Seite nicht gefunden', 'open3' ); ?></h1>
	<p class="o3-archive-head__desc"><?php esc_html_e( 'Die angeforderte Adresse existiert nicht oder wurde verschoben. Vielleicht hilft die Suche weiter.', 'open3' ); ?></p>
</header>
<div class="o3-none">
	<?php get_search_form(); ?>
	<p class="o3-none__text"><a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Zur Startseite →', 'open3' ); ?></a></p>
</div>
		</main>
<?php
if ( open3_has_sidebar() ) {
	get_sidebar();
}
get_footer();
