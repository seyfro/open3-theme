<?php
/**
 * Grundlagen: numbered index of the child pages (prototype dc:279-293).
 *
 * Kicker "Grundlagen · n Beiträge", H1 = title, lede = excerpt or first paragraph,
 * ol.o3-basics with %02d numbers, then the remaining page content.
 *
 * @package open3
 */

get_header();

while ( have_posts() ) :
	the_post();
	$open3_post_id  = (int) get_the_ID();
	$open3_children = get_pages(
		array(
			'child_of'    => $open3_post_id,
			'parent'      => $open3_post_id,
			'sort_column' => 'menu_order, post_title',
			'sort_order'  => 'ASC',
			'post_status' => 'publish',
		)
	);
	$open3_children = is_array( $open3_children ) ? $open3_children : array();
	$open3_count    = count( $open3_children );

	$open3_body = (string) apply_filters( 'the_content', get_the_content() );
	$open3_body = str_replace( ']]>', ']]&gt;', $open3_body );
	$open3_lede = '';
	if ( has_excerpt( $open3_post_id ) ) {
		$open3_lede = trim( wp_strip_all_tags( (string) get_the_excerpt( $open3_post_id ) ) );
	} elseif ( preg_match( '#<p\b[^>]*>(.*?)</p>#is', $open3_body, $open3_match ) ) {
		$open3_lede = trim( wp_strip_all_tags( $open3_match[1] ) );
		$open3_body = (string) preg_replace( '#<p\b[^>]*>.*?</p>#is', '', $open3_body, 1 );
	}
	$open3_body = trim( $open3_body );
	?>
	<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-archive o3-archive--basics' ); ?>>
		<span class="o3-kicker">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of child pages */
					_n( 'Grundlagen · %d Beitrag', 'Grundlagen · %d Beiträge', $open3_count, 'open3' ),
					$open3_count
				)
			);
			?>
		</span>
		<h1 class="o3-archive__title"><?php the_title(); ?></h1>
		<?php if ( '' !== $open3_lede ) : ?>
			<p class="o3-archive__lede"><?php echo esc_html( $open3_lede ); ?></p>
		<?php endif; ?>
		<?php if ( $open3_count > 0 ) : ?>
			<ol class="o3-basics o3-tnum" aria-label="<?php esc_attr_e( 'Verzeichnis der Grundlagen', 'open3' ); ?>">
				<?php foreach ( $open3_children as $open3_index => $open3_child ) : ?>
					<li>
						<a href="<?php echo esc_url( (string) get_permalink( $open3_child ) ); ?>">
							<span class="o3-basics__num"><?php echo esc_html( sprintf( '%02d', $open3_index + 1 ) ); ?></span>
							<span class="o3-basics__title"><?php echo esc_html( get_the_title( $open3_child ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<p class="o3-basics__empty"><?php esc_html_e( 'Noch keine Beiträge in den Grundlagen.', 'open3' ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $open3_body ) : ?>
			<div class="entry-content">
				<?php echo $open3_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content filters applied above. ?>
			</div>
		<?php endif; ?>
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
