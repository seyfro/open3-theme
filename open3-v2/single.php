<?php
/**
 * Single post (Beitrag).
 *
 * @package open3
 */

get_header();

while ( have_posts() ) :
	the_post();
	$open3_post_id  = (int) get_the_ID();
	$open3_category = open3_first_category( $open3_post_id );
	$open3_tags     = get_the_tags( $open3_post_id );
	$open3_prev     = get_previous_post();
	$open3_next     = get_next_post();
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-post' ); ?>>
		<div class="o3-post__crumbs o3-tnum">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Blog', 'open3' ); ?></a>
			<span aria-hidden="true">/</span>
			<?php if ( $open3_category ) : ?>
				<a class="tag tag-accent" href="<?php echo esc_url( (string) get_category_link( $open3_category ) ); ?>"><?php echo esc_html( $open3_category->name ); ?></a>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( (string) get_the_date( 'c' ) ); ?>"><?php echo esc_html( (string) get_the_date( 'j. F Y' ) ); ?></time>
		</div>
		<h1 class="o3-post__title"><?php the_title(); ?></h1>
		<div class="o3-post__byline">
			<span>
				<?php
				printf(
					/* translators: %s: author link */
					wp_kses_post( __( 'Autor: %s', 'open3' ) ),
					'<a href="' . esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ) . '" rel="author">' . esc_html( get_the_author() ) . '</a>'
				);
				?>
			</span>
			<span aria-hidden="true">·</span>
			<span><?php echo esc_html( open3_reading_time( $open3_post_id ) ); ?></span>
		</div>
		<?php if ( has_post_thumbnail( $open3_post_id ) ) : ?>
			<figure class="o3-post__media">
				<?php
				open3_media(
					$open3_post_id,
					'open3-wide',
					array(
						'sizes'   => '(min-width: 900px) 620px, 100vw',
						'loading' => 'eager',
					)
				);
				$open3_caption = (string) get_the_post_thumbnail_caption( $open3_post_id );
				if ( '' !== $open3_caption ) :
					?>
					<figcaption><?php echo esc_html( $open3_caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/content/prose' ); ?>
		<div class="o3-post__terms">
			<?php if ( $open3_category ) : ?>
				<span class="o3-post__terms-label"><?php esc_html_e( 'Kategorie', 'open3' ); ?></span>
				<?php
				foreach ( get_the_category( $open3_post_id ) as $open3_cat ) :
					?>
					<a class="tag tag-accent" href="<?php echo esc_url( (string) get_category_link( $open3_cat ) ); ?>"><?php echo esc_html( $open3_cat->name ); ?></a>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ( is_array( $open3_tags ) && ! empty( $open3_tags ) ) : ?>
				<span class="o3-post__terms-label o3-post__terms-label--themes"><?php esc_html_e( 'Themen', 'open3' ); ?></span>
				<?php foreach ( $open3_tags as $open3_tag ) : ?>
					<a class="tag tag-neutral" href="<?php echo esc_url( (string) get_tag_link( $open3_tag ) ); ?>"><?php echo esc_html( $open3_tag->name ); ?></a>
				<?php endforeach; ?>
			<?php endif; ?>
			<span class="o3-post__terms-spacer"></span>
			<a class="btn btn-ghost" href="<?php echo esc_url( open3_bluesky_share_url( $open3_post_id ) ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Teilen auf Bluesky', 'open3' ); ?></a>
		</div>
		<nav class="o3-postnav" aria-label="<?php esc_attr_e( 'Beitragsnavigation', 'open3' ); ?>">
			<?php if ( $open3_prev instanceof WP_Post ) : ?>
				<a class="o3-postnav__prev" href="<?php echo esc_url( (string) get_permalink( $open3_prev ) ); ?>" rel="prev">
					<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( '← Vorheriger Beitrag', 'open3' ); ?></span>
					<span class="o3-postnav__title"><?php echo esc_html( get_the_title( $open3_prev ) ); ?></span>
				</a>
			<?php else : ?>
				<div class="o3-postnav__prev">
					<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( '← Vorheriger Beitrag', 'open3' ); ?></span>
					<span class="o3-postnav__title o3-postnav__empty"><?php esc_html_e( 'Kein älterer Beitrag', 'open3' ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $open3_next instanceof WP_Post ) : ?>
				<a class="o3-postnav__next" href="<?php echo esc_url( (string) get_permalink( $open3_next ) ); ?>" rel="next">
					<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Nächster Beitrag →', 'open3' ); ?></span>
					<span class="o3-postnav__title"><?php echo esc_html( get_the_title( $open3_next ) ); ?></span>
				</a>
			<?php else : ?>
				<div class="o3-postnav__next">
					<span class="o3-kicker o3-kicker--sm"><?php esc_html_e( 'Nächster Beitrag →', 'open3' ); ?></span>
					<span class="o3-postnav__title o3-postnav__empty"><?php esc_html_e( 'Noch kein neuerer Beitrag', 'open3' ); ?></span>
				</div>
			<?php endif; ?>
		</nav>
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
