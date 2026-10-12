<?php
/**
 * Customizer settings.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for the theme mods.
 *
 * @return array<string,string>
 */
function open3_customizer_defaults(): array {
	return array(
		'open3_sidebar_position'    => 'rechts',
		'open3_hero_text'           => __( 'Open3 ist ein offenes, gemeinnütziges Netzwerk. Seit 2010 setzen wir anwendungsorientierte Projekte um, sammeln Grundlagenwissen und vernetzen Politik, Verwaltung, Wirtschaft und Bevölkerung.', 'open3' ),
		'open3_projekte_intro_title' => __( 'Anwendungsorientierte Projekte seit 2010', 'open3' ),
		'open3_projekte_intro_text' => __( 'Visualisierungen öffentlicher Budgets, Apps auf Basis offener Verwaltungsdaten, Umfragen und Dokumentationen – umgesetzt von Mitgliedern des Netzwerks, ohne finanzielle Interessen.', 'open3' ),
		'open3_contact_email'       => 'office@open3.at',
		'open3_bluesky_url'         => 'https://bsky.app/profile/seyfro.at',
	);
}

/**
 * Read a theme mod with its default.
 *
 * @param string $key Setting key.
 * @return string
 */
function open3_mod( string $key ): string {
	$defaults = open3_customizer_defaults();
	$default  = $defaults[ $key ] ?? '';
	$value    = get_theme_mod( $key, $default );
	$value    = is_string( $value ) ? $value : $default;
	return '' !== trim( $value ) ? $value : $default;
}

/**
 * Sanitize the sidebar position.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function open3_sanitize_sidebar_position( $value ): string {
	return 'links' === $value ? 'links' : 'rechts';
}

/**
 * Sanitize multi-line plain text.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function open3_sanitize_textarea( $value ): string {
	return sanitize_textarea_field( (string) $value );
}

/**
 * Register sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function open3_customize_register( WP_Customize_Manager $wp_customize ): void {
	$defaults = open3_customizer_defaults();

	$wp_customize->add_section(
		'open3_options',
		array(
			'title'       => __( 'open3 Einstellungen', 'open3' ),
			'description' => __( 'Layout und Texte der Startseite, der Projektübersicht und des Footers.', 'open3' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_setting(
		'open3_sidebar_position',
		array(
			'default'           => $defaults['open3_sidebar_position'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'open3_sanitize_sidebar_position',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_sidebar_position',
		array(
			'section' => 'open3_options',
			'label'   => __( 'Position der Seitenleiste', 'open3' ),
			'type'    => 'radio',
			'choices' => array(
				'rechts' => __( 'Rechts', 'open3' ),
				'links'  => __( 'Links', 'open3' ),
			),
		)
	);

	$wp_customize->add_setting(
		'open3_hero_text',
		array(
			'default'           => $defaults['open3_hero_text'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'open3_sanitize_textarea',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_hero_text',
		array(
			'section'     => 'open3_options',
			'label'       => __( 'Einleitung auf der Startseite', 'open3' ),
			'description' => __( 'Der Text unter dem Untertitel der Website (Einstellungen → Allgemein).', 'open3' ),
			'type'        => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'open3_projekte_intro_title',
		array(
			'default'           => $defaults['open3_projekte_intro_title'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_projekte_intro_title',
		array(
			'section' => 'open3_options',
			'label'   => __( 'Projekte: Überschrift', 'open3' ),
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'open3_projekte_intro_text',
		array(
			'default'           => $defaults['open3_projekte_intro_text'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'open3_sanitize_textarea',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_projekte_intro_text',
		array(
			'section' => 'open3_options',
			'label'   => __( 'Projekte: Einleitung', 'open3' ),
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'open3_contact_email',
		array(
			'default'           => $defaults['open3_contact_email'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_contact_email',
		array(
			'section'     => 'open3_options',
			'label'       => __( 'Kontakt-E-Mail', 'open3' ),
			'description' => __( 'Wird im Footer und im Projekt-Steckbrief angezeigt.', 'open3' ),
			'type'        => 'email',
		)
	);

	$wp_customize->add_setting(
		'open3_bluesky_url',
		array(
			'default'           => $defaults['open3_bluesky_url'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_bluesky_url',
		array(
			'section'     => 'open3_options',
			'label'       => __( 'Bluesky-Profil (URL)', 'open3' ),
			'description' => __( 'Beispiel: https://bsky.app/profile/seyfro.at', 'open3' ),
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'open3_customize_register' );
