<?php
/**
 * Weiterleitungen für Legacy-Pfade – greifen ausschließlich bei 404.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pfad => Zielpfad (relativ zu home_url()).
 *
 * @return array<string,string>
 */
function open3_redirect_map(): array {
	return array(
		'/sparpaket'               => '/projekte/sparpaket',
		'/projekte/linz-open-data' => '/projekte/linz-open-data-apps-und-visualisierungen',
	);
}

/**
 * 301 auf das neue Ziel, wenn WordPress selbst nichts gefunden hat.
 */
function open3_legacy_redirects(): void {
	if ( ! is_404() ) {
		return;
	}
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( '' === $request_uri ) {
		return;
	}
	$path = wp_parse_url( $request_uri, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		return;
	}
	$path = rawurldecode( $path );

	$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home_path = is_string( $home_path ) ? rtrim( $home_path, '/' ) : '';
	if ( '' !== $home_path && str_starts_with( $path, $home_path ) ) {
		$path = substr( $path, strlen( $home_path ) );
	}
	$path = '/' . trim( $path, '/' );

	$map = open3_redirect_map();
	if ( ! isset( $map[ $path ] ) ) {
		return;
	}
	wp_safe_redirect( home_url( $map[ $path ] ), 301, 'open3' );
	exit;
}
add_action( 'template_redirect', 'open3_legacy_redirects', 1 );
