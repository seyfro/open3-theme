<?php
/**
 * Projekt Steckbrief: Jahr / Datenquelle / Technik / Lizenz / Kontakt.
 *
 * @package open3
 *
 * @var array $args { @type int $post_id Post ID. }
 */

$open3_post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();

$open3_rows = array();
foreach ( array(
	'open3_jahr'        => __( 'Jahr', 'open3' ),
	'open3_datenquelle' => __( 'Datenquelle', 'open3' ),
	'open3_technik'     => __( 'Technik', 'open3' ),
	'open3_lizenz'      => __( 'Lizenz', 'open3' ),
) as $open3_key => $open3_label ) {
	$open3_value = trim( open3_get_projekt_meta( $open3_post_id, $open3_key ) );
	if ( '' !== $open3_value ) {
		$open3_rows[] = array(
			'label' => $open3_label,
			'html'  => esc_html( $open3_value ),
		);
	}
}

$open3_email  = open3_contact_email();
$open3_rows[] = array(
	'label' => __( 'Kontakt', 'open3' ),
	'html'  => '<a href="' . esc_url( 'mailto:' . $open3_email ) . '">' . esc_html( $open3_email ) . '</a>',
);
?>
<div class="o3-project__facts">
	<h2 class="o3-kicker"><?php esc_html_e( 'Steckbrief', 'open3' ); ?></h2>
	<table class="table o3-project__table">
		<tbody>
			<?php foreach ( $open3_rows as $open3_row ) : ?>
				<tr>
					<td><?php echo esc_html( $open3_row['label'] ); ?></td>
					<td><?php echo wp_kses_post( $open3_row['html'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
