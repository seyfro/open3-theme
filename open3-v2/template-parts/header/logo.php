<?php
/**
 * Brand link: custom logo or the inline SVG.
 *
 * @package open3
 *
 * @var array $args { @type string $class Anchor class ('o3-brand' or 'o3-footer__brand'). }
 */

$open3_link_class = isset( $args['class'] ) && '' !== $args['class'] ? (string) $args['class'] : 'o3-brand';
$open3_logo_html  = '';

if ( has_custom_logo() ) {
	$open3_logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $open3_logo_id > 0 ) {
		$open3_logo_html = wp_get_attachment_image(
			$open3_logo_id,
			'full',
			false,
			array(
				'class'    => 'o3-logo o3-logo--custom',
				'alt'      => get_bloginfo( 'name', 'display' ),
				'loading'  => 'eager',
				'decoding' => 'async',
			)
		);
	}
}

if ( '' === $open3_logo_html ) {
	$open3_logo_html = open3_inline_logo_svg();
}

if ( '' === $open3_logo_html ) {
	$open3_logo_html = '<span class="o3-logo o3-logo--text">' . esc_html( get_bloginfo( 'name', 'display' ) ) . '</span>';
}
?>
<a class="<?php echo esc_attr( $open3_link_class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'open3 – zur Startseite', 'open3' ); ?>">
	<?php echo $open3_logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-shipped SVG or wp_get_attachment_image() output. ?>
</a>
