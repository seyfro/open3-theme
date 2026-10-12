<?php
/**
 * Projekt detail head: breadcrumb (Projekte / Typ), H1, lede, action buttons.
 *
 * Shared by single-projekt.php and the treemap templates.
 *
 * @package open3
 *
 * @var array $args { @type int $post_id Post ID (default: current post). }
 */

$open3_post_id  = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$open3_type     = open3_projekt_type_name( $open3_post_id );
$open3_archive  = (string) get_post_type_archive_link( 'projekt' );
$open3_app_url  = open3_get_projekt_meta( $open3_post_id, 'open3_app_url' );
$open3_data_url = open3_get_projekt_meta( $open3_post_id, 'open3_datensatz_url' );
?>
<div class="o3-project__crumbs">
	<a href="<?php echo esc_url( '' !== $open3_archive ? $open3_archive : home_url( '/projekte/' ) ); ?>"><?php esc_html_e( 'Projekte', 'open3' ); ?></a>
	<?php if ( '' !== $open3_type ) : ?>
		<span aria-hidden="true">/</span>
		<span class="o3-micro o3-project__type"><?php echo esc_html( $open3_type ); ?></span>
	<?php endif; ?>
</div>
<h1 class="o3-project__title"><?php echo esc_html( get_the_title( $open3_post_id ) ); ?></h1>
<?php if ( has_excerpt( $open3_post_id ) ) : ?>
	<p class="o3-project__lede"><?php echo esc_html( wp_strip_all_tags( (string) get_the_excerpt( $open3_post_id ) ) ); ?></p>
<?php endif; ?>
<?php if ( '' !== $open3_app_url || '' !== $open3_data_url ) : ?>
	<div class="o3-project__actions">
		<?php if ( '' !== $open3_app_url ) : ?>
			<a class="btn btn-primary" href="<?php echo esc_url( $open3_app_url ); ?>"><?php esc_html_e( 'Anwendung öffnen', 'open3' ); ?></a>
		<?php endif; ?>
		<?php if ( '' !== $open3_data_url ) : ?>
			<a class="btn btn-secondary" href="<?php echo esc_url( $open3_data_url ); ?>"><?php esc_html_e( 'Datensatz', 'open3' ); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>
