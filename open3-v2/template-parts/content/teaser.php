<?php
/**
 * Teaser row (home, archives, search).
 *
 * @package open3
 */

$open3_post_id = (int) get_the_ID();
?>
<article id="post-<?php echo esc_attr( (string) $open3_post_id ); ?>" <?php post_class( 'o3-teaser' ); ?>>
	<div class="o3-teaser__meta o3-tnum">
		<time datetime="<?php echo esc_attr( (string) get_the_date( 'c' ) ); ?>"><?php echo esc_html( (string) get_the_date( 'j. F Y' ) ); ?></time>
		<?php open3_the_context_tag( $open3_post_id ); ?>
	</div>
	<h2 class="o3-teaser__title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	<div class="o3-teaser__excerpt"><?php the_excerpt(); ?></div>
	<div class="o3-teaser__actions">
		<a class="btn btn-ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Weiterlesen →', 'open3' ); ?><span class="screen-reader-text"><?php echo esc_html( ': ' . get_the_title() ); ?></span></a>
		<?php if ( 'post' === get_post_type( $open3_post_id ) ) : ?>
			<span class="o3-teaser__author"><?php echo esc_html( get_the_author() ); ?></span>
		<?php endif; ?>
	</div>
</article>
