<?php
/**
 * Start A hero: tagline as H1, Customizer lede.
 *
 * @package open3
 */

$open3_tagline = (string) get_bloginfo( 'description', 'display' );
if ( '' === trim( $open3_tagline ) ) {
	$open3_tagline = __( 'Netzwerk zur Förderung von OpenSociety, OpenGov und OpenData in Österreich', 'open3' );
}
?>
<section class="o3-hero">
	<h1 class="o3-hero__title"><?php echo esc_html( $open3_tagline ); ?></h1>
	<p class="o3-hero__lede"><?php echo esc_html( open3_mod( 'open3_hero_text' ) ); ?></p>
</section>
