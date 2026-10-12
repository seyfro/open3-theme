<?php
/**
 * Meta-Felder („Steckbrief“) des Post Types „projekt“.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Textfelder des Steckbriefs (Meta-Key => Definition).
 *
 * @return array<string,array{label:string,type:string,sanitize:string,description:string}>
 */
function open3_projekt_meta_fields(): array {
	return array(
		'open3_jahr'             => array(
			'label'       => __( 'Jahr', 'open3' ),
			'type'        => 'text',
			'sanitize'    => 'open3_sanitize_meta_text',
			'description' => '',
		),
		'open3_datenquelle'      => array(
			'label'       => __( 'Datenquelle', 'open3' ),
			'type'        => 'text',
			'sanitize'    => 'open3_sanitize_meta_text',
			'description' => '',
		),
		'open3_technik'          => array(
			'label'       => __( 'Technik', 'open3' ),
			'type'        => 'text',
			'sanitize'    => 'open3_sanitize_meta_text',
			'description' => '',
		),
		'open3_lizenz'           => array(
			'label'       => __( 'Lizenz', 'open3' ),
			'type'        => 'text',
			'sanitize'    => 'open3_sanitize_meta_text',
			'description' => '',
		),
		'open3_app_url'          => array(
			'label'       => __( 'Link zur Anwendung', 'open3' ),
			'type'        => 'url',
			'sanitize'    => 'open3_sanitize_meta_url',
			'description' => __( 'Wird als Button „Anwendung öffnen“ angezeigt.', 'open3' ),
		),
		'open3_datensatz_url'    => array(
			'label'       => __( 'Link zum Datensatz', 'open3' ),
			'type'        => 'url',
			'sanitize'    => 'open3_sanitize_meta_url',
			'description' => __( 'Wird als Button „Datensatz“ angezeigt.', 'open3' ),
		),
		'open3_comments_heading' => array(
			'label'       => __( 'Überschrift der Kommentarliste', 'open3' ),
			'type'        => 'text',
			'sanitize'    => 'open3_sanitize_meta_text',
			'description' => __( 'Leer lassen für „Kommentare“. Beispiel: „Alle 23 Einreichungen“.', 'open3' ),
		),
	);
}

/**
 * Sanitize-Callback für Textfelder.
 *
 * @param mixed $value Rohwert.
 */
function open3_sanitize_meta_text( $value ): string {
	return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
}

/**
 * Sanitize-Callback für URL-Felder.
 *
 * @param mixed $value Rohwert.
 */
function open3_sanitize_meta_url( $value ): string {
	return is_scalar( $value ) ? esc_url_raw( trim( (string) $value ) ) : '';
}

/**
 * Berechtigung für Meta-Schreibzugriffe (REST): Bearbeitungsrecht am konkreten Projekt.
 *
 * @param bool   $allowed  Bisheriger Wert (ungenutzt).
 * @param string $meta_key Meta-Key (ungenutzt).
 * @param int    $post_id  Projekt-ID.
 */
function open3_projekt_meta_auth( $allowed, $meta_key, $post_id ): bool {
	return current_user_can( 'edit_post', (int) $post_id );
}

/**
 * Registriert alle Meta-Keys (show_in_rest).
 */
function open3_register_projekt_meta(): void {
	foreach ( open3_projekt_meta_fields() as $key => $field ) {
		register_post_meta(
			'projekt',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => $field['sanitize'],
				'auth_callback'     => 'open3_projekt_meta_auth',
			)
		);
	}
	register_post_meta(
		'projekt',
		'open3_versteckt',
		array(
			'type'              => 'boolean',
			'single'            => true,
			'default'           => false,
			'show_in_rest'      => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => 'open3_projekt_meta_auth',
		)
	);
}
add_action( 'init', 'open3_register_projekt_meta', 6 );

/**
 * Meta-Box „Steckbrief“ anmelden.
 */
function open3_add_projekt_meta_box(): void {
	add_meta_box(
		'open3-projekt-steckbrief',
		__( 'Steckbrief', 'open3' ),
		'open3_projekt_meta_box',
		'projekt',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_projekt', 'open3_add_projekt_meta_box' );

/**
 * Meta-Box rendern.
 *
 * @param WP_Post $post Aktuelles Projekt.
 */
function open3_projekt_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'open3_projekt_meta', 'open3_projekt_meta_nonce' );
	$hidden = open3_projekt_meta( $post->ID, 'open3_versteckt' );
	?>
	<table class="form-table" role="presentation">
		<tbody>
		<?php foreach ( open3_projekt_meta_fields() as $key => $field ) : ?>
			<?php $value = open3_projekt_meta( $post->ID, $key ); ?>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input type="<?php echo esc_attr( $field['type'] ); ?>" class="regular-text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo 'url' === $field['type'] ? ' placeholder="https://"' : ''; ?>>
					<?php if ( '' !== $field['description'] ) : ?>
						<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sichtbarkeit', 'open3' ); ?></th>
				<td>
					<label for="open3_versteckt">
						<input type="checkbox" id="open3_versteckt" name="open3_versteckt" value="1" <?php checked( '1', $hidden ); ?>>
						<?php esc_html_e( 'Nicht in Übersicht/Explorer listen', 'open3' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Das Projekt bleibt unter seiner Adresse erreichbar, erscheint aber nicht in der Projektübersicht und im Projekt-Explorer.', 'open3' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="description"><?php esc_html_e( 'Hinweis: Die Sondertemplates für Sparpaket und Wien Budget 2009 sind an den Permalink gebunden. Wird der Permalink geändert, greift das Standardtemplate.', 'open3' ); ?></p>
	<?php
}

/**
 * Meta-Box speichern.
 *
 * @param int     $post_id Projekt-ID.
 * @param WP_Post $post    Projekt.
 */
function open3_save_projekt_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['open3_projekt_meta_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['open3_projekt_meta_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'open3_projekt_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( 'projekt' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( open3_projekt_meta_fields() as $key => $field ) {
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		$value = call_user_func( $field['sanitize'], $raw );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	if ( isset( $_POST['open3_versteckt'] ) ) {
		update_post_meta( $post_id, 'open3_versteckt', true );
	} else {
		delete_post_meta( $post_id, 'open3_versteckt' );
	}
}
add_action( 'save_post_projekt', 'open3_save_projekt_meta', 10, 2 );

/**
 * Liest ein Steckbrief-Feld als String ('' wenn leer; '1' für gesetzte Booleans).
 *
 * @param int    $post_id Projekt-ID.
 * @param string $key     Meta-Key.
 */
function open3_projekt_meta( int $post_id, string $key ): string {
	$value = get_post_meta( $post_id, $key, true );
	if ( is_bool( $value ) ) {
		return $value ? '1' : '';
	}
	return is_scalar( $value ) ? (string) $value : '';
}
