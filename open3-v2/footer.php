	</div><!-- .o3-layout -->
	<footer class="o3-footer">
		<div class="o3-footer__col">
			<?php get_template_part( 'template-parts/header/logo', null, array( 'class' => 'o3-footer__brand' ) ); ?>
			<span class="o3-footer__tagline"><?php echo esc_html( get_bloginfo( 'description', 'display' ) ); ?></span>
		</div>
		<div class="o3-footer__col">
			<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Sitemap', 'open3' ); ?></span>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'items_wrap'     => '<ul class="o3-footer__links">%3$s</ul>',
					'depth'          => 1,
					'fallback_cb'    => 'open3_footer_fallback',
				)
			);
			?>
		</div>
		<div class="o3-footer__col">
			<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Kontakt', 'open3' ); ?></span>
			<ul class="o3-footer__links">
				<?php $open3_email = open3_contact_email(); ?>
				<li><a href="<?php echo esc_url( 'mailto:' . $open3_email ); ?>"><?php echo esc_html( $open3_email ); ?></a></li>
				<li><a href="<?php echo esc_url( open3_bluesky_profile_url() ); ?>" rel="me noopener"><?php echo esc_html( open3_bluesky_label() ); ?></a></li>
				<li><a href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>"><?php esc_html_e( 'RSS-Feed', 'open3' ); ?></a></li>
			</ul>
		</div>
		<div class="o3-footer__col">
			<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Lizenz', 'open3' ); ?></span>
			<span class="o3-footer__license">
				<?php
				printf(
					/* translators: 1: license URL */
					wp_kses(
						__( 'open3.at-Inhalte stehen, wenn nicht anders angegeben, unter der <a href="%1$s" rel="license">Creative Commons Namensnennung 3.0 Österreich Lizenz</a>.', 'open3' ),
						array(
							'a' => array(
								'href' => array(),
								'rel'  => array(),
							),
						)
					),
					esc_url( 'https://creativecommons.org/licenses/by/3.0/at/deed.de' )
				);
				?>
			</span>
			<div class="o3-footer__legal">
				<?php
				$open3_impressum = get_page_by_path( 'impressum' );
				if ( $open3_impressum instanceof WP_Post && 'publish' === $open3_impressum->post_status ) :
					?>
					<a href="<?php echo esc_url( (string) get_permalink( $open3_impressum ) ); ?>"><?php esc_html_e( 'Impressum', 'open3' ); ?></a>
				<?php endif; ?>
				<?php
				$open3_privacy = get_privacy_policy_url();
				if ( '' === $open3_privacy ) {
					$open3_privacy = home_url( '/datenschutzerklaerung/' );
				}
				?>
				<a href="<?php echo esc_url( $open3_privacy ); ?>"><?php esc_html_e( 'Datenschutzerklärung', 'open3' ); ?></a>
			</div>
		</div>
	</footer>
</div><!-- .o3-page -->
<?php wp_footer(); ?>
</body>
</html>
