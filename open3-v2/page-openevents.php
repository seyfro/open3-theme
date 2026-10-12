<?php
/**
 * openEvents: upcoming Termine, consent-gated calendar embed, past-events table (prototype dc:322-350).
 *
 * Data: posts in category "termine" with the Termin meta from inc/meta-termin.php.
 *
 * @package open3
 */

get_header();

while ( have_posts() ) :
	the_post();
	$open3_post_id  = (int) get_the_ID();
	$open3_email    = open3_contact_email();
	$open3_category = open3_termin_category();
	$open3_cat_link = $open3_category instanceof WP_Term ? (string) get_category_link( $open3_category ) : '';
	$open3_total    = $open3_category instanceof WP_Term ? (int) $open3_category->count : 0;
	$open3_upcoming = open3_termin_upcoming( 10 );
	$open3_past     = open3_termin_past( 10 );
	$open3_calendar = open3_termin_calendar_url( $open3_post_id );
	$open3_cal_name = __( 'ePSIplatform events', 'open3' );

	$open3_mailto = '<a href="' . esc_url( 'mailto:' . $open3_email ) . '">' . esc_html( $open3_email ) . '</a>';
	$open3_catlnk = '' !== $open3_cat_link
		? '<a href="' . esc_url( $open3_cat_link ) . '">' . esc_html__( 'Termine', 'open3' ) . '</a>'
		: esc_html__( 'Termine', 'open3' );

	if ( '' !== $open3_calendar ) {
		open3_termin_consent_script();
	}
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-archive o3-events' ); ?>>
		<span class="o3-kicker"><?php esc_html_e( 'openEvents', 'open3' ); ?></span>
		<h1 class="o3-archive__title o3-archive__title--tight"><?php the_title(); ?></h1>

		<h2 class="o3-events__heading" id="oesterreich"><?php esc_html_e( 'Nächste Veranstaltungen in Österreich', 'open3' ); ?></h2>
		<p class="o3-events__lede">
			<?php
			printf(
				/* translators: 1: link to the Termine category, 2: mailto link */
				esc_html__( 'Neue Veranstaltungen in Österreich werden im open3-Blog in der Kategorie %1$s angekündigt – Ergänzungen für diese Liste bitte an %2$s senden.', 'open3' ),
				$open3_catlnk, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				$open3_mailto // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			);
			?>
		</p>
		<?php if ( ! empty( $open3_upcoming ) ) : ?>
			<?php
			foreach ( $open3_upcoming as $open3_event ) :
				$open3_event_id = (int) $open3_event->ID;
				$open3_when     = open3_termin_datetime( $open3_event_id );
				$open3_ort      = open3_termin_meta( $open3_event_id, 'open3_termin_ort' );
				$open3_link     = open3_termin_meta( $open3_event_id, 'open3_termin_link' );
				if ( ! $open3_when instanceof DateTimeImmutable ) {
					continue;
				}
				$open3_ts = $open3_when->getTimestamp();
				?>
				<div class="o3-event">
					<span class="o3-event__date">
						<time datetime="<?php echo esc_attr( $open3_when->format( 'Y-m-d\TH:i' ) ); ?>">
							<?php echo esc_html( (string) wp_date( 'd.m.', $open3_ts ) ); ?>
							<span class="o3-event__sub"><?php echo esc_html( (string) wp_date( 'Y', $open3_ts ) . ' · ' . (string) wp_date( 'H:i', $open3_ts ) ); ?></span>
						</time>
					</span>
					<div>
						<strong class="o3-event__title"><a href="<?php echo esc_url( (string) get_permalink( $open3_event ) ); ?>"><?php echo esc_html( get_the_title( $open3_event ) ); ?></a></strong>
						<?php if ( '' !== $open3_ort || '' !== $open3_link ) : ?>
							<span class="o3-event__meta">
								<?php if ( '' !== $open3_ort ) : ?>
									<?php echo esc_html( $open3_ort ); ?>
								<?php endif; ?>
								<?php if ( '' !== $open3_ort && '' !== $open3_link ) : ?>
									<span aria-hidden="true">·</span>
								<?php endif; ?>
								<?php if ( '' !== $open3_link ) : ?>
									<a href="<?php echo esc_url( $open3_link ); ?>"><?php esc_html_e( 'Zur Anmeldung', 'open3' ); ?></a>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="o3-events__empty"><?php esc_html_e( 'Derzeit sind keine kommenden Veranstaltungen eingetragen.', 'open3' ); ?></p>
		<?php endif; ?>

		<h2 class="o3-events__heading" id="europa"><?php esc_html_e( 'Nächste Veranstaltungen in Europa', 'open3' ); ?></h2>
		<p class="o3-events__lede"><?php esc_html_e( 'Der nachfolgende Kalender wird von Mitarbeitern der europäischen Kommission im Rahmen der ePSIplus-Plattform gepflegt und zeigt Veranstaltungen rund um OpenData in ganz Europa.', 'open3' ); ?></p>
		<?php if ( '' !== $open3_calendar ) : ?>
			<div class="o3-consent">
				<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Eingebetteter Kalender', 'open3' ); ?></span>
				<span class="o3-consent__text">
					<?php
					printf(
						/* translators: %s: calendar name */
						esc_html__( 'Google-Kalender „%s“ – wird erst nach Klick geladen. Dabei werden Daten an Google übertragen.', 'open3' ),
						esc_html( $open3_cal_name )
					);
					?>
				</span>
				<button type="button" class="btn btn-secondary o3-consent__load" data-src="<?php echo esc_url( $open3_calendar ); ?>" data-title="<?php echo esc_attr( $open3_cal_name ); ?>"><?php esc_html_e( 'Kalender laden', 'open3' ); ?></button>
				<noscript><a class="o3-consent__fallback" href="<?php echo esc_url( $open3_calendar ); ?>" rel="noopener"><?php esc_html_e( 'Kalender in neuem Fenster öffnen', 'open3' ); ?></a></noscript>
			</div>
		<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
			<div class="o3-consent">
				<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Eingebetteter Kalender', 'open3' ); ?></span>
				<span class="o3-consent__text"><?php esc_html_e( 'Hinweis für Redaktion: Keine Kalender-URL hinterlegt. Bitte im Customizer unter „open3 Einstellungen“ eintragen oder ein Google-Kalender-iframe in den Seiteninhalt einfügen.', 'open3' ); ?></span>
			</div>
		<?php endif; ?>

		<h2 class="o3-events__heading o3-events__heading--table" id="vergangen"><?php esc_html_e( 'Bereits stattgefundene Veranstaltungen in Österreich', 'open3' ); ?></h2>
		<?php if ( ! empty( $open3_past ) ) : ?>
			<div class="o3-events__table">
				<table class="table">
					<thead>
						<tr>
							<th scope="col" class="o3-col-date"><?php esc_html_e( 'Datum', 'open3' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Veranstaltung', 'open3' ); ?></th>
							<th scope="col" class="o3-col-place"><?php esc_html_e( 'Ort', 'open3' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $open3_past as $open3_event ) : ?>
							<?php $open3_event_id = (int) $open3_event->ID; ?>
							<tr>
								<td><?php echo esc_html( open3_termin_display_date( $open3_event_id, 'j. F Y' ) ); ?></td>
								<td><a href="<?php echo esc_url( (string) get_permalink( $open3_event ) ); ?>"><?php echo esc_html( get_the_title( $open3_event ) ); ?></a></td>
								<td><?php echo esc_html( open3_termin_meta( $open3_event_id, 'open3_termin_ort' ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<p class="o3-events__empty"><?php esc_html_e( 'Noch keine Einträge.', 'open3' ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $open3_cat_link && $open3_total > 0 ) : ?>
			<div class="o3-events__more">
				<a class="btn btn-ghost" href="<?php echo esc_url( $open3_cat_link ); ?>">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of posts in the Termine category */
							_n( 'Alle %d Einträge anzeigen', 'Alle %d Einträge anzeigen', $open3_total, 'open3' ),
							$open3_total
						)
					);
					?>
				</a>
			</div>
		<?php endif; ?>

		<?php
		add_filter( 'the_content', 'open3_termin_strip_calendar_iframe', 5 );
		if ( '' !== trim( (string) get_post_field( 'post_content', $open3_post_id ) ) ) {
			get_template_part( 'template-parts/content/prose' );
		}
		remove_filter( 'the_content', 'open3_termin_strip_calendar_iframe', 5 );
		?>
	</article>
	<?php
endwhile;
?>
		</main>
<?php
if ( open3_has_sidebar() ) {
	get_sidebar();
}
get_footer();
