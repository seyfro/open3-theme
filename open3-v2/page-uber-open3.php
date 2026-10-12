<?php
/**
 * Über open3: 3-cell grid, prose, services, "Auf dem Laufenden bleiben" (prototype dc:296-319).
 *
 * @package open3
 */

get_header();

// Static services list (prototype dc:483-487). Pages are resolved by slug; missing pages are skipped.
$open3_services_source = array(
	array(
		'name' => 'Grundlagen',
		'desc' => __( 'Grundlageninformationen zu openSociety, openGovernment und openData', 'open3' ),
		'path' => 'grundlagen',
	),
	array(
		'name' => 'openNetwork',
		'desc' => __( 'Netzwerktätigkeit auf der Plattform', 'open3' ),
		'path' => 'opennetwork',
	),
	array(
		'name' => 'openNews',
		'desc' => __( 'Informationstransfer über die Plattform', 'open3' ),
		'path' => '',
	),
	array(
		'name' => 'openLinks',
		'desc' => __( 'Linksammlung', 'open3' ),
		'path' => 'grundlagen/openlinks',
	),
	array(
		'name' => 'openEvents',
		'desc' => __( 'Veranstaltungsverzeichnis', 'open3' ),
		'path' => 'openevents',
	),
);

$open3_services = array();
foreach ( $open3_services_source as $open3_service ) {
	if ( '' === $open3_service['path'] ) {
		$open3_service['url'] = home_url( '/' );
	} else {
		$open3_page = get_page_by_path( $open3_service['path'], OBJECT, 'page' );
		if ( ! $open3_page instanceof WP_Post || 'publish' !== $open3_page->post_status ) {
			continue;
		}
		$open3_service['url'] = (string) get_permalink( $open3_page );
	}
	$open3_services[] = $open3_service;
}

$open3_three = array(
	array( '1', __( 'Open Society', 'open3' ), __( 'Transparenz', 'open3' ) ),
	array( '2', __( 'Open Government', 'open3' ), __( 'Partizipation und Partizipationsfähigkeit', 'open3' ) ),
	array( '3', __( 'Open Data', 'open3' ), __( 'Kollaboration zwischen Stakeholdern', 'open3' ) ),
);

while ( have_posts() ) :
	the_post();
	$open3_post_id = (int) get_the_ID();
	$open3_email   = open3_contact_email();
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-archive o3-about' ); ?>>
		<span class="o3-kicker"><?php esc_html_e( 'Über open3', 'open3' ); ?></span>
		<h1 class="o3-archive__title o3-archive__title--tight"><?php the_title(); ?></h1>
		<div class="o3-three">
			<?php foreach ( $open3_three as $open3_cell ) : ?>
				<div class="o3-three__cell">
					<span class="o3-three__num" aria-hidden="true"><?php echo esc_html( $open3_cell[0] ); ?></span>
					<strong class="o3-three__label"><?php echo esc_html( $open3_cell[1] ); ?></strong>
					<span class="o3-three__desc"><?php echo esc_html( $open3_cell[2] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php get_template_part( 'template-parts/content/prose' ); ?>
		<div class="entry-content o3-about__more">
			<?php if ( ! empty( $open3_services ) ) : ?>
				<h2 id="services"><?php esc_html_e( 'Services', 'open3' ); ?></h2>
				<ul class="o3-services">
					<?php foreach ( $open3_services as $open3_service ) : ?>
						<li>
							<a href="<?php echo esc_url( $open3_service['url'] ); ?>">
								<strong class="o3-services__name"><?php echo esc_html( $open3_service['name'] ); ?></strong>
								<span class="o3-services__desc"><?php echo esc_html( $open3_service['desc'] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<h2 id="auf-dem-laufenden"><?php esc_html_e( 'Auf dem Laufenden bleiben', 'open3' ); ?></h2>
			<p class="o3-about__stay">
				<?php
				printf(
					/* translators: 1: feed link, 2: newsletter link, 3: Bluesky link, 4: mailto link */
					esc_html__( 'Abonnieren Sie den %1$s, den %2$s oder folgen Sie uns auf %3$s. Bei Fragen oder Feedback: %4$s', 'open3' ),
					'<a href="' . esc_url( (string) get_feed_link() ) . '">' . esc_html__( 'RSS-Feed des Blogs', 'open3' ) . '</a>',
					'<a href="' . esc_url( open3_newsletter_url() ) . '">' . esc_html__( 'Newsletter', 'open3' ) . '</a>',
					'<a href="' . esc_url( open3_bluesky_profile_url() ) . '" rel="me">' . esc_html__( 'Bluesky', 'open3' ) . '</a>',
					'<a href="' . esc_url( 'mailto:' . $open3_email ) . '">' . esc_html( $open3_email ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
		if ( comments_open() || get_comments_number() > 0 ) {
			comments_template();
		}
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
