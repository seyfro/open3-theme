<?php
/**
 * Projekt filter chips + grid (archive-projekt.php and taxonomy-projekttyp.php).
 *
 * @package open3
 */

$open3_terms = get_terms(
	array(
		'taxonomy'   => 'projekttyp',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);
$open3_terms = is_wp_error( $open3_terms ) ? array() : $open3_terms;

// hide_empty counts hidden (open3_versteckt) projects too: keep only chips with at least one visible project.
if ( array() !== $open3_terms ) {
	$open3_visible_ids = get_posts(
		array(
			'post_type'              => 'projekt',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'meta_query'             => open3_projekt_visible_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);
	$open3_visible_term_ids = array();
	if ( array() !== $open3_visible_ids ) {
		$open3_object_terms = wp_get_object_terms( $open3_visible_ids, 'projekttyp', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $open3_object_terms ) ) {
			$open3_visible_term_ids = array_map( 'intval', $open3_object_terms );
		}
	}
	$open3_terms = array_values(
		array_filter(
			$open3_terms,
			static function ( $open3_term ) use ( $open3_visible_term_ids ): bool {
				return $open3_term instanceof WP_Term && in_array( (int) $open3_term->term_id, $open3_visible_term_ids, true );
			}
		)
	);
}

$open3_active_slug = '';
if ( is_tax( 'projekttyp' ) ) {
	$open3_queried = get_queried_object();
	if ( $open3_queried instanceof WP_Term ) {
		$open3_active_slug = (string) $open3_queried->slug;
	}
}

$open3_archive_url = (string) get_post_type_archive_link( 'projekt' );
if ( '' === $open3_archive_url ) {
	$open3_archive_url = home_url( '/projekte/' );
}
?>
<div class="o3-filter">
	<a class="tag <?php echo '' === $open3_active_slug ? 'tag-accent' : 'tag-neutral'; ?>" href="<?php echo esc_url( $open3_archive_url ); ?>"<?php echo '' === $open3_active_slug ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Alle', 'open3' ); ?></a>
	<?php foreach ( $open3_terms as $open3_term ) : ?>
		<?php
		$open3_term_link = get_term_link( $open3_term );
		if ( is_wp_error( $open3_term_link ) ) {
			continue;
		}
		$open3_is_active = $open3_term->slug === $open3_active_slug;
		?>
		<a class="tag <?php echo $open3_is_active ? 'tag-accent' : 'tag-neutral'; ?>" href="<?php echo esc_url( $open3_term_link ); ?>"<?php echo $open3_is_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $open3_term->name ); ?></a>
	<?php endforeach; ?>
</div>
<?php if ( have_posts() ) : ?>
	<div class="o3-pgrid">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/projekt/card', null, array( 'post_id' => (int) get_the_ID() ) );
		}
		?>
	</div>
<?php else : ?>
	<p class="o3-pgrid__empty"><?php esc_html_e( 'Keine Projekte gefunden.', 'open3' ); ?></p>
<?php endif; ?>
