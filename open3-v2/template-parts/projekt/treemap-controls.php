<?php
/**
 * Treemap layout controls (radios + "one level up" button) for the JIT data scripts.
 *
 * IDs follow the legacy contract the data files bind to: r-sq/r-st/r-sd + back (suffix ''),
 * r-sq2/r-st2/r-sd2 + back2 (suffix '2', patched gesamthaushalt file).
 *
 * @package open3
 *
 * @var array $args { @type string $suffix '' or '2'. }
 */

$open3_suffix = isset( $args['suffix'] ) ? preg_replace( '/[^a-z0-9-]/i', '', (string) $args['suffix'] ) : '';

$open3_layouts = array(
	'r-sq' => array( 'left', __( 'Quadrate', 'open3' ) ),
	'r-st' => array( 'top', __( 'Streifen', 'open3' ) ),
	'r-sd' => array( 'bottom', __( 'Slice-and-Dice', 'open3' ) ),
);
?>
<div id="<?php echo esc_attr( 'id-list' . $open3_suffix ); ?>" class="o3-treemap__controls">
	<fieldset class="o3-treemap__layouts">
		<legend class="o3-kicker o3-kicker--muted"><?php esc_html_e( 'Darstellung', 'open3' ); ?></legend>
		<?php foreach ( $open3_layouts as $open3_id => $open3_layout ) : ?>
			<label class="o3-treemap__radio" for="<?php echo esc_attr( $open3_id . $open3_suffix ); ?>">
				<input type="radio" id="<?php echo esc_attr( $open3_id . $open3_suffix ); ?>" name="<?php echo esc_attr( 'layout' . $open3_suffix ); ?>" value="<?php echo esc_attr( $open3_layout[0] ); ?>"<?php checked( 'left', $open3_layout[0] ); ?>>
				<span><?php echo esc_html( $open3_layout[1] ); ?></span>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<a id="<?php echo esc_attr( 'back' . $open3_suffix ); ?>" class="btn btn-secondary o3-treemap__back" href="<?php echo esc_attr( '#hoch' . $open3_suffix ); ?>"><?php esc_html_e( 'Eine Ebene höher anzeigen', 'open3' ); ?></a>
</div>
