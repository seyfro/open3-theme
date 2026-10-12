<?php
/**
 * Termin-Meta auf Beiträgen (openEvents): Datum, Ort, Anmeldelink.
 *
 * Meta-Keys: open3_termin_datum (Y-m-d\TH:i, lokale Zeit), open3_termin_ort, open3_termin_link.
 * Dazu Meta-Box „Termin“, Customizer-Setting für den Kalender-Embed und Abfrage-Helfer
 * für page-openevents.php.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Felder der Meta-Box (Meta-Key => Definition).
 *
 * @return array<string,array{label:string,type:string,sanitize:string,description:string}>
 */
function open3_termin_meta_fields(): array {
	return array(
		'open3_termin_datum' => array(
			'label'       => __( 'Datum und Uhrzeit', 'open3' ),
			'type'        => 'datetime-local',
			'sanitize'    => 'open3_sanitize_termin_datum',
			'description' => __( 'Beiträge der Kategorie „Termine“ mit einem Datum in der Zukunft erscheinen auf der openEvents-Seite unter „Nächste Veranstaltungen“.', 'open3' ),
		),
		'open3_termin_ort'   => array(
			'label'       => __( 'Ort', 'open3' ),
			'type'        => 'text',
			'sanitize'    => 'open3_sanitize_termin_text',
			'description' => __( 'Beispiel: VHS Wiener Urania, Uraniastraße 1, 1010 Wien', 'open3' ),
		),
		'open3_termin_link'  => array(
			'label'       => __( 'Link zur Anmeldung', 'open3' ),
			'type'        => 'url',
			'sanitize'    => 'open3_sanitize_termin_url',
			'description' => __( 'Wird als „Zur Anmeldung“ verlinkt.', 'open3' ),
		),
	);
}

/**
 * Sanitize: Datum im Format Y-m-d\TH:i (auch „Y-m-d H:i“ und mit Sekunden akzeptiert).
 *
 * @param mixed $value Rohwert.
 */
function open3_sanitize_termin_datum( $value ): string {
	if ( ! is_scalar( $value ) ) {
		return '';
	}
	$raw = trim( (string) $value );
	if ( '' === $raw ) {
		return '';
	}
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::\d{2})?$/', $raw, $m ) ) {
		return '';
	}
	if ( ! checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) || (int) $m[4] > 23 || (int) $m[5] > 59 ) {
		return '';
	}
	return sprintf( '%s-%s-%sT%s:%s', $m[1], $m[2], $m[3], $m[4], $m[5] );
}

/**
 * Sanitize: einzeiliger Text.
 *
 * @param mixed $value Rohwert.
 */
function open3_sanitize_termin_text( $value ): string {
	return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
}

/**
 * Sanitize: URL.
 *
 * @param mixed $value Rohwert.
 */
function open3_sanitize_termin_url( $value ): string {
	return is_scalar( $value ) ? esc_url_raw( trim( (string) $value ) ) : '';
}

/**
 * Schreibrecht (REST): Bearbeitungsrecht am Beitrag.
 *
 * @param bool   $allowed  Bisheriger Wert (ungenutzt).
 * @param string $meta_key Meta-Key (ungenutzt).
 * @param int    $post_id  Beitrags-ID.
 */
function open3_termin_meta_auth( $allowed, $meta_key, $post_id ): bool {
	return current_user_can( 'edit_post', (int) $post_id );
}

/**
 * Meta-Keys registrieren (show_in_rest).
 */
function open3_register_termin_meta(): void {
	foreach ( open3_termin_meta_fields() as $key => $field ) {
		register_post_meta(
			'post',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => $field['sanitize'],
				'auth_callback'     => 'open3_termin_meta_auth',
			)
		);
	}
}
add_action( 'init', 'open3_register_termin_meta', 6 );

/**
 * Meta-Box „Termin“ anmelden.
 */
function open3_add_termin_meta_box(): void {
	add_meta_box(
		'open3-termin',
		__( 'Termin', 'open3' ),
		'open3_termin_meta_box',
		'post',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_post', 'open3_add_termin_meta_box' );

/**
 * Meta-Box rendern.
 *
 * @param WP_Post $post Aktueller Beitrag.
 */
function open3_termin_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'open3_termin_meta', 'open3_termin_meta_nonce' );
	?>
	<p class="description"><?php esc_html_e( 'Nur für Veranstaltungen (Kategorie „Termine“). Leer lassen bei normalen Beiträgen.', 'open3' ); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
		<?php foreach ( open3_termin_meta_fields() as $key => $field ) : ?>
			<?php $value = open3_termin_meta( $post->ID, $key ); ?>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input type="<?php echo esc_attr( $field['type'] ); ?>" class="regular-text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php echo 'url' === $field['type'] ? ' placeholder="https://"' : ''; ?><?php echo 'datetime-local' === $field['type'] ? ' step="60"' : ''; ?>>
					<?php if ( '' !== $field['description'] ) : ?>
						<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Meta-Box speichern.
 *
 * @param int     $post_id Beitrags-ID.
 * @param WP_Post $post    Beitrag.
 */
function open3_save_termin_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['open3_termin_meta_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['open3_termin_meta_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'open3_termin_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( 'post' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( open3_termin_meta_fields() as $key => $field ) {
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		$value = call_user_func( $field['sanitize'], $raw );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_post', 'open3_save_termin_meta', 10, 2 );

/**
 * Liest ein Termin-Feld als String ('' wenn leer).
 *
 * @param int    $post_id Beitrags-ID.
 * @param string $key     Meta-Key.
 */
function open3_termin_meta( int $post_id, string $key ): string {
	$value = get_post_meta( $post_id, $key, true );
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Termin-Zeitpunkt als DateTimeImmutable in der Site-Zeitzone (null wenn kein gültiges Datum).
 *
 * @param int $post_id Beitrags-ID.
 */
function open3_termin_datetime( int $post_id ): ?DateTimeImmutable {
	$value = open3_sanitize_termin_datum( open3_termin_meta( $post_id, 'open3_termin_datum' ) );
	if ( '' === $value ) {
		return null;
	}
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i', $value, wp_timezone() );
	return $date instanceof DateTimeImmutable ? $date : null;
}

/**
 * Aktueller Zeitpunkt im Meta-Format (lokale Zeit) für String-Vergleiche in meta_query.
 */
function open3_termin_now(): string {
	return (string) current_time( 'Y-m-d\TH:i' );
}

/**
 * Kategorie „Termine“ (null wenn nicht vorhanden).
 */
function open3_termin_category(): ?WP_Term {
	$term = get_category_by_slug( 'termine' );
	return $term instanceof WP_Term ? $term : null;
}

/**
 * Kommende Termine: Kategorie termine, Datum ≥ jetzt, aufsteigend.
 *
 * @param int $limit Maximale Anzahl.
 * @return WP_Post[]
 */
function open3_termin_upcoming( int $limit = 10 ): array {
	$posts = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'category_name'          => 'termine',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_key'               => 'open3_termin_datum', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => open3_termin_now(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_compare'           => '>=',
			'orderby'                => 'meta_value',
			'order'                  => 'ASC',
		)
	);
	return array_values( array_filter( $posts, static fn( $p ) => $p instanceof WP_Post ) );
}

/**
 * Vergangene Termine: die neuesten Beiträge der Kategorie termine ohne Termin in der Zukunft.
 *
 * @param int $limit Maximale Anzahl.
 * @return WP_Post[]
 */
function open3_termin_past( int $limit = 10 ): array {
	$posts = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'category_name'          => 'termine',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => 'open3_termin_datum',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'open3_termin_datum',
					'value'   => open3_termin_now(),
					'compare' => '<',
				),
			),
		)
	);
	return array_values( array_filter( $posts, static fn( $p ) => $p instanceof WP_Post ) );
}

/**
 * Anzeigedatum eines Termins: Termin-Meta, sonst Veröffentlichungsdatum.
 *
 * @param int    $post_id Beitrags-ID.
 * @param string $format  PHP-Datumsformat.
 */
function open3_termin_display_date( int $post_id, string $format = 'j. F Y' ): string {
	$date = open3_termin_datetime( $post_id );
	if ( $date instanceof DateTimeImmutable ) {
		return (string) wp_date( $format, $date->getTimestamp() );
	}
	return (string) get_the_date( $format, $post_id );
}

/**
 * Customizer: URL des eingebetteten Kalenders (Sektion open3_options aus inc/customizer.php).
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function open3_termin_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_setting(
		'open3_events_calendar_url',
		array(
			'default'           => '',
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'open3_events_calendar_url',
		array(
			'section'     => 'open3_options',
			'label'       => __( 'openEvents: Kalender-Embed (URL)', 'open3' ),
			'description' => __( 'Embed-Adresse des Google-Kalenders (https://calendar.google.com/calendar/embed?src=…). Wird auf der openEvents-Seite erst nach Klick geladen. Ein <iframe> im Seiteninhalt hat Vorrang.', 'open3' ),
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'open3_termin_customize_register', 20 );

/**
 * Regex für Google-Kalender-Embeds im Seiteninhalt.
 */
function open3_termin_calendar_pattern(): string {
	return '#<iframe\b[^>]*\bsrc=["\']([^"\']*(?:calendar\.google\.com|google\.com/calendar)[^"\']*)["\'][^>]*>.*?</iframe>#is';
}

/**
 * Embed-URL des Kalenders: aus dem Seiteninhalt (erstes Google-Kalender-iframe), sonst Customizer.
 *
 * @param int $page_id Seiten-ID.
 */
function open3_termin_calendar_url( int $page_id ): string {
	$content = (string) get_post_field( 'post_content', $page_id );
	if ( '' !== $content && preg_match( open3_termin_calendar_pattern(), $content, $m ) ) {
		$url = esc_url_raw( html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( '' !== $url ) {
			return $url;
		}
	}
	$mod = get_theme_mod( 'open3_events_calendar_url', '' );
	return is_string( $mod ) ? esc_url_raw( $mod ) : '';
}

/**
 * Entfernt Google-Kalender-iframes aus dem Inhalt (die Consent-Box übernimmt das Laden).
 *
 * @param string|null $content Inhalt (Plugins können null durchreichen).
 */
function open3_termin_strip_calendar_iframe( $content ): string {
	$content  = (string) $content;
	$stripped = preg_replace( open3_termin_calendar_pattern(), '', $content );
	return is_string( $stripped ) ? $stripped : $content;
}

/**
 * Inline-Script: Consent-Button durch das Kalender-iframe ersetzen (hängt an open3-main).
 */
function open3_termin_consent_script(): void {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$js = <<<'JS'
(function(){
	var buttons = document.querySelectorAll('.o3-consent__load[data-src]');
	Array.prototype.forEach.call(buttons, function(btn){
		btn.addEventListener('click', function(){
			var src = btn.getAttribute('data-src');
			if (!src || !/^https:\/\//i.test(src)) { return; }
			var frame = document.createElement('iframe');
			frame.src = src;
			frame.title = btn.getAttribute('data-title') || 'Kalender';
			frame.loading = 'lazy';
			frame.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
			var wrap = document.createElement('div');
			wrap.className = 'o3-consent__frame';
			wrap.appendChild(frame);
			var note = btn.parentNode.querySelector('.o3-consent__text');
			if (note) { note.hidden = true; }
			btn.parentNode.replaceChild(wrap, btn);
		});
	});
})();
JS;

	wp_add_inline_script( 'open3-main', $js, 'after' );
}
