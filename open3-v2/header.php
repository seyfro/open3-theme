<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<ul class="o3-skiplinks">
	<li><a class="o3-skip" href="#skiplink-navigation"><?php esc_html_e( 'Zur Navigation springen', 'open3' ); ?></a></li>
	<li><a class="o3-skip" href="#skiplink-inhalt"><?php esc_html_e( 'Zum Inhalt springen', 'open3' ); ?></a></li>
	<?php if ( is_singular() && get_comments_number() > 0 ) : ?>
		<li><a class="o3-skip" href="#skiplink-kommentare"><?php esc_html_e( 'Zu den Kommentaren springen', 'open3' ); ?></a></li>
	<?php endif; ?>
	<?php if ( is_singular() && comments_open() ) : ?>
		<li><a class="o3-skip" href="#skiplink-kommentarformular"><?php esc_html_e( 'Zum Kommentarformular springen', 'open3' ); ?></a></li>
	<?php endif; ?>
</ul>
<div class="o3-page">
	<header class="o3-header">
		<?php get_template_part( 'template-parts/header/logo', null, array( 'class' => 'o3-brand' ) ); ?>
		<?php get_template_part( 'template-parts/header/nav' ); ?>
		<a class="btn btn-primary o3-header__cta" href="<?php echo esc_url( open3_newsletter_url() ); ?>"><?php esc_html_e( 'Newsletter', 'open3' ); ?></a>
		<button type="button" class="btn btn-secondary o3-menu-toggle" aria-expanded="false" aria-controls="skiplink-navigation" data-label-open="<?php esc_attr_e( 'Menü', 'open3' ); ?>" data-label-close="<?php esc_attr_e( 'Schließen', 'open3' ); ?>"><?php esc_html_e( 'Menü', 'open3' ); ?></button>
	</header>
	<div class="o3-layout">
		<main id="skiplink-inhalt" class="o3-main" tabindex="-1">
