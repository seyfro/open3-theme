<?php
/**
 * Primary navigation (rendered once; CSS switches desktop row vs. mobile panel).
 *
 * @package open3
 */

?>
<nav id="skiplink-navigation" class="o3-nav" aria-label="<?php esc_attr_e( 'Hauptnavigation', 'open3' ); ?>" tabindex="-1">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '<ul class="o3-nav__list">%3$s</ul>',
			'depth'          => 1,
			'fallback_cb'    => 'open3_nav_fallback',
		)
	);
	?>
	<a class="btn btn-primary o3-nav__cta" href="<?php echo esc_url( open3_newsletter_url() ); ?>"><?php esc_html_e( 'Newsletter abonnieren', 'open3' ); ?></a>
</nav>
