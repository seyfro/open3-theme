<?php
/**
 * Empty state for lists and search.
 *
 * @package open3
 */

?>
<div class="o3-none">
	<?php if ( is_search() ) : ?>
		<p class="o3-none__text"><?php esc_html_e( 'Keine Treffer. Bitte versuchen Sie es mit anderen Suchbegriffen.', 'open3' ); ?></p>
	<?php else : ?>
		<p class="o3-none__text"><?php esc_html_e( 'Hier gibt es noch keine Beiträge.', 'open3' ); ?></p>
	<?php endif; ?>
	<?php get_search_form(); ?>
</div>
