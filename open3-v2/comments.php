<?php
/**
 * Comments: list first, form last.
 *
 * @package open3
 */

if ( post_password_required() ) {
	return;
}

$open3_ping_count = (int) get_comments(
	array(
		'post_id' => get_the_ID(),
		'type'    => 'pings',
		'status'  => 'approve',
		'count'   => true,
	)
);
?>
<section id="skiplink-kommentare" class="o3-comments comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="o3-comments__title comments-title"><?php echo esc_html( open3_comments_heading( (int) get_the_ID() ) ); ?></h2>
		<ol class="commentlist comment-list">
			<?php
			wp_list_comments(
				array(
					'type'        => 'comment',
					'callback'    => 'open3_comment',
					'avatar_size' => 0,
					'style'       => 'ol',
				)
			);
			?>
		</ol>
		<?php
		the_comments_navigation(
			array(
				'prev_text' => __( '← Ältere Kommentare', 'open3' ),
				'next_text' => __( 'Neuere Kommentare →', 'open3' ),
			)
		);
		?>
		<?php if ( $open3_ping_count > 0 ) : ?>
			<h3 class="o3-comments__pings-title"><?php esc_html_e( 'Links zum Artikel', 'open3' ); ?></h3>
			<ol class="ping-list">
				<?php
				wp_list_comments(
					array(
						'type'       => 'pings',
						'short_ping' => true,
					)
				);
				?>
			</ol>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<div id="skiplink-kommentarformular" class="o3-comments__form">
			<?php comment_form( open3_comment_form_args() ); ?>
		</div>
	<?php elseif ( have_comments() ) : ?>
		<p class="o3-comments__closed no-comments"><?php esc_html_e( 'Die Kommentarfunktion ist für diesen Beitrag geschlossen.', 'open3' ); ?></p>
	<?php endif; ?>
</section>
