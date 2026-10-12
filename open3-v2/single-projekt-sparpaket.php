<?php
/**
 * Projekt "Sparpaket" (slug sparpaket): two JIT treemaps, full width, no sidebar.
 *
 * Block A = Gesamthaushalt 2011–2014 (suffix 2: #hoch2, #infovis2, #log2, init2()),
 * block B = Budgetkürzungen 2011–2014 (suffix '': #hoch, #infovis, #log, init()).
 * Markup contract and copy from the legacy template sparpaket.php; scripts via inc/treemap.php.
 *
 * @package open3
 */

get_header();

$open3_uploads   = 'https://www.open3.at/wp-content/uploads/js-treemap/';
$open3_moneypile = file_exists( OPEN3_DIR . '/assets/js/treemap/img/moneypile.png' )
	? OPEN3_URI . '/assets/js/treemap/img/moneypile.png'
	: $open3_uploads . 'moneypile/moneypile.png';

while ( have_posts() ) :
	the_post();
	$open3_post_id = (int) get_the_ID();
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-project o3-project--treemap' ); ?>>
		<?php get_template_part( 'template-parts/projekt/head', null, array( 'post_id' => $open3_post_id ) ); ?>

		<section class="o3-treemap-block" aria-labelledby="hoch2">
			<h2 id="hoch2" class="o3-treemap-block__title"><?php esc_html_e( 'Bundesfinanzrahmen 2011–2014 inkl. Konsolidierungsmaßnahmen als interaktive Grafik', 'open3' ); ?></h2>
			<p class="o3-treemap-block__hint"><?php esc_html_e( 'Um die jährlichen Budgets anzuzeigen, klicken Sie bitte mit der Maus auf den gewünschten Budgetposten. Ein Rechtsklick führt eine Ebene zurück.', 'open3' ); ?></p>
			<?php get_template_part( 'template-parts/projekt/treemap-controls', null, array( 'suffix' => '2' ) ); ?>
			<div class="o3-treemap">
				<div id="infovis2" class="o3-treemap__canvas"></div>
				<div id="log2" class="o3-treemap__log"></div>
			</div>
			<p class="o3-treemap__links">
				<a href="https://offener.datenkatalog.at/package/bundesbudget-2011-2014" target="_blank" rel="noopener"><?php esc_html_e( 'Rohdaten auf datenkatalog.at anzeigen', 'open3' ); ?></a>,
				<a href="<?php echo esc_url( $open3_uploads . 'gesamthaushalt-2011-2014-v1.1.png' ); ?>"><?php esc_html_e( 'Download als Grafik', 'open3' ); ?></a>
				<?php esc_html_e( 'oder', 'open3' ); ?>
				<a href="<?php echo esc_url( $open3_uploads . 'gesamthaushalt-2011-2014-v1.1.xls' ); ?>"><?php esc_html_e( 'Download als Excel-File', 'open3' ); ?></a>
			</p>
			<h3 id="pileofmoney" class="o3-treemap-block__subtitle"><?php esc_html_e( 'Alternative Darstellung', 'open3' ); ?></h3>
			<p class="o3-treemap-block__hint"><?php esc_html_e( 'Der Gesamthaushalt als Geldstapel im Größenvergleich. Die frühere interaktive Version dieser Grafik steht nicht mehr zur Verfügung.', 'open3' ); ?></p>
			<img class="o3-treemap__still" src="<?php echo esc_url( $open3_moneypile ); ?>" width="885" height="407" alt="<?php esc_attr_e( 'Der Bundeshaushalt 2011–2014 als gestapelte Geldscheine im Größenvergleich', 'open3' ); ?>" loading="lazy" decoding="async">
		</section>

		<section class="o3-treemap-block" aria-labelledby="hoch">
			<h2 id="hoch" class="o3-treemap-block__title"><?php esc_html_e( 'Budgetkürzungen 2011–2014 als interaktive Grafik', 'open3' ); ?></h2>
			<p class="o3-treemap-block__hint"><?php esc_html_e( 'Um die Detailsparmaßnahmen anzuzeigen, klicken Sie bitte mit der Maus auf den gewünschten Budgetposten. Ein Rechtsklick führt eine Ebene zurück.', 'open3' ); ?></p>
			<?php get_template_part( 'template-parts/projekt/treemap-controls', null, array( 'suffix' => '' ) ); ?>
			<div class="o3-treemap">
				<div id="infovis" class="o3-treemap__canvas"></div>
				<div id="log" class="o3-treemap__log"></div>
			</div>
			<p class="o3-treemap__links">
				<a href="https://offener.datenkatalog.at/package/bundesbudget-2011-2014_ausgabenseitige-kuerzungen" target="_blank" rel="noopener"><?php esc_html_e( 'Rohdaten auf datenkatalog.at anzeigen', 'open3' ); ?></a>
				<?php esc_html_e( 'oder', 'open3' ); ?>
				<a href="<?php echo esc_url( $open3_uploads . 'treemap-screenshot-v3.png' ); ?>"><?php esc_html_e( 'Download als Grafik', 'open3' ); ?></a>
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
