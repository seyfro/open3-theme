<?php
/**
 * Post/page body: the_content + paginated pages.
 *
 * @package open3
 */

?>
<div class="entry-content">
	<?php
	the_content();
	wp_link_pages(
		array(
			'before'      => '<nav class="o3-pagelinks o3-tnum" aria-label="' . esc_attr__( 'Seiten', 'open3' ) . '"><span class="o3-kicker o3-kicker--muted">' . esc_html__( 'Seiten', 'open3' ) . '</span>',
			'after'       => '</nav>',
			'link_before' => '<span class="page-numbers">',
			'link_after'  => '</span>',
		)
	);
	?>
</div>
