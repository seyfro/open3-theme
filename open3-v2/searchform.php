<?php
/**
 * Search form (also used by get_search_form()).
 *
 * @package open3
 */

$open3_search_id = wp_unique_id( 'o3-search-' );
?>
<form role="search" method="get" class="o3-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="o3-kicker o3-kicker--muted" for="<?php echo esc_attr( $open3_search_id ); ?>"><?php esc_html_e( 'Suche', 'open3' ); ?></label>
	<div class="o3-search__row">
		<input id="<?php echo esc_attr( $open3_search_id ); ?>" class="input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Beiträge, Projekte, Grundlagen …', 'open3' ); ?>">
		<button type="submit" class="btn btn-secondary"><?php esc_html_e( 'Suchen', 'open3' ); ?></button>
	</div>
</form>
