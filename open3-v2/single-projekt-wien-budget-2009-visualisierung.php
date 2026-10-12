<?php
/**
 * Projekt "Wien Budget 2009" (slug wien-budget-2009-visualisierung): one JIT treemap,
 * full width, no sidebar. Markup contract and copy from legacy wienbudget2009.php
 * (#hoch, #infovis, #log, init()); scripts via inc/treemap.php.
 *
 * @package open3
 */

get_header();

while ( have_posts() ) :
	the_post();
	$open3_post_id = (int) get_the_ID();
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-project o3-project--treemap' ); ?>>
		<?php get_template_part( 'template-parts/projekt/head', null, array( 'post_id' => $open3_post_id ) ); ?>

		<section class="o3-treemap-block" aria-labelledby="hoch">
			<h2 id="hoch" class="o3-treemap-block__title"><?php esc_html_e( 'Wien Budget 2009 – Ausgaben als interaktive Grafik', 'open3' ); ?></h2>
			<p class="o3-treemap-block__hint">
				<?php esc_html_e( 'Für Details klicken Sie bitte mit der Maus auf den gewünschten Budgetposten; ein Rechtsklick führt eine Ebene zurück.', 'open3' ); ?>
				<?php
				printf(
					/* translators: 1: link to the Vienna open data catalogue, 2: link to the ogdwien-finanzdaten overview */
					esc_html__( 'Für weitere Visualisierungen basierend auf den im %1$s veröffentlichten Finanzdaten bitte %2$s klicken.', 'open3' ),
					'<a href="https://data.wien.gv.at" target="_blank" rel="noopener">' . esc_html__( 'Open Data Katalog der Stadt Wien', 'open3' ) . '</a>',
					'<a href="' . esc_url( home_url( '/ogdwien-finanzdaten' ) ) . '">' . esc_html__( 'hier', 'open3' ) . '</a>'
				);
				?>
			</p>
			<?php get_template_part( 'template-parts/projekt/treemap-controls', null, array( 'suffix' => '' ) ); ?>
			<div class="o3-treemap">
				<div id="infovis" class="o3-treemap__canvas"></div>
				<div id="log" class="o3-treemap__log"></div>
			</div>
			<p class="o3-treemap__links">
				<a href="https://offener.datenkatalog.at/package/wien-budget-2009-ausgaben" target="_blank" rel="noopener"><?php esc_html_e( 'Rohdaten auf datenkatalog.at anzeigen', 'open3' ); ?></a>,
				<a href="https://www.open3.at/wp-content/uploads/js-wienbudget/wien-budget2009-ausgaben.png"><?php esc_html_e( 'Download als Grafik', 'open3' ); ?></a>
			</p>
		</section>

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
get_footer();
