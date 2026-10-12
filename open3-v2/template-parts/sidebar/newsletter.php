<?php
/**
 * Sidebar: newsletter box (Newsletter plugin form or link fallback).
 *
 * @package open3
 */

$open3_nl_id = wp_unique_id( 'o3-nl-' );
?>
<?php if ( class_exists( 'Newsletter' ) ) : ?>
	<form class="o3-newsletter" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=tnp&na=s' ) ); ?>">
		<span class="o3-newsletter__title"><?php esc_html_e( 'Newsletter', 'open3' ); ?></span>
		<span class="o3-newsletter__text"><?php esc_html_e( 'Neue Beiträge, Termine und Projekte per E-Mail.', 'open3' ); ?></span>
		<input type="hidden" name="nlang" value="">
		<label class="screen-reader-text" for="<?php echo esc_attr( $open3_nl_id ); ?>"><?php esc_html_e( 'E-Mail-Adresse', 'open3' ); ?></label>
		<input id="<?php echo esc_attr( $open3_nl_id ); ?>" class="input" type="email" name="ne" required placeholder="<?php esc_attr_e( 'E-Mail-Adresse', 'open3' ); ?>" autocomplete="email">
		<button type="submit" class="btn btn-ghost o3-newsletter__submit"><?php esc_html_e( 'Abonnieren', 'open3' ); ?></button>
	</form>
<?php else : ?>
	<div class="o3-newsletter">
		<span class="o3-newsletter__title"><?php esc_html_e( 'Newsletter', 'open3' ); ?></span>
		<span class="o3-newsletter__text"><?php esc_html_e( 'Neue Beiträge, Termine und Projekte per E-Mail.', 'open3' ); ?></span>
		<a class="btn btn-ghost o3-newsletter__submit" href="<?php echo esc_url( open3_newsletter_url() ); ?>"><?php esc_html_e( 'Abonnieren', 'open3' ); ?></a>
	</div>
<?php endif; ?>
