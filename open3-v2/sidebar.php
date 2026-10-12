<?php
/**
 * Sidebar: fixed, query-driven blocks (no widget area).
 *
 * @package open3
 */

?>
<aside class="o3-aside" aria-label="<?php esc_attr_e( 'Seitenleiste', 'open3' ); ?>">
	<?php
	get_template_part( 'template-parts/sidebar/search' );
	get_template_part( 'template-parts/sidebar/explorer' );
	get_template_part( 'template-parts/sidebar/recent' );
	get_template_part( 'template-parts/sidebar/categories' );
	get_template_part( 'template-parts/sidebar/newsletter' );
	?>
</aside>
