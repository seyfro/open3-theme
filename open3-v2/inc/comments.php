<?php
/**
 * Comments: callback, form arguments, spam protection, heading.
 *
 * @package open3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Heading above the comment list.
 *
 * Uses the post meta `open3_comments_heading` when set (with an optional %d
 * placeholder), otherwise "Einreichungen (%d)" for the poetry contest and
 * "Kommentare (%d)" everywhere else.
 *
 * @param int $post_id Post ID (0 = current).
 * @return string
 */
function open3_comments_heading( int $post_id = 0 ): string {
	$post_id = $post_id > 0 ? $post_id : (int) get_the_ID();
	$count   = (int) get_comments_number( $post_id );
	$custom  = trim( (string) get_post_meta( $post_id, 'open3_comments_heading', true ) );

	if ( '' !== $custom ) {
		return str_replace( '%d', (string) $count, $custom ); // kein Format-Parsing von Redaktionstext
	}

	if ( 'projekt' === get_post_type( $post_id ) && 'poetry-contest' === get_post_field( 'post_name', $post_id ) ) {
		/* translators: %d: number of submissions */
		return sprintf( _n( 'Einreichung (%d)', 'Einreichungen (%d)', $count, 'open3' ), $count );
	}

	/* translators: %d: number of comments */
	return sprintf( _n( 'Kommentar (%d)', 'Kommentare (%d)', $count, 'open3' ), $count );
}

/**
 * Arguments for comment_form().
 *
 * @return array<string,mixed>
 */
function open3_comment_form_args(): array {
	$commenter = wp_get_current_commenter();
	$req       = (bool) get_option( 'require_name_email' );
	$required  = $req ? ' required' : '';

	$fields = array(
		'author' => sprintf(
			'<div class="field comment-form-author"><label for="author">%1$s</label><input id="author" class="input" name="author" type="text" value="%2$s" placeholder="%3$s" autocomplete="name"%4$s></div>',
			esc_html__( 'Name', 'open3' ),
			esc_attr( $commenter['comment_author'] ),
			esc_attr__( 'Ihr Name', 'open3' ),
			$required
		),
		'email'  => sprintf(
			'<div class="field comment-form-email"><label for="email">%1$s</label><input id="email" class="input" name="email" type="email" value="%2$s" placeholder="%3$s" autocomplete="email"%4$s></div>',
			esc_html__( 'E-Mail', 'open3' ),
			esc_attr( $commenter['comment_author_email'] ),
			esc_attr__( 'name@beispiel.at', 'open3' ),
			$required
		),
	);

	if ( get_option( 'show_comments_cookies_opt_in' ) ) {
		$consent           = empty( $commenter['comment_author_email'] ) ? '' : ' checked';
		$fields['cookies'] = sprintf(
			'<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%1$s><label for="wp-comment-cookies-consent">%2$s</label></p>',
			$consent,
			esc_html__( 'Meinen Namen und meine E-Mail-Adresse in diesem Browser für spätere Kommentare speichern.', 'open3' )
		);
	}

	return array(
		'title_reply'          => __( 'Kommentar hinzufügen', 'open3' ),
		/* translators: %s: author of the comment being replied to */
		'title_reply_to'       => __( 'Antwort auf %s', 'open3' ),
		'title_reply_before'   => '<h2 id="reply-title" class="comment-form-title">',
		'title_reply_after'    => '</h2>',
		'cancel_reply_before'  => ' <small>',
		'cancel_reply_after'   => '</small>',
		'cancel_reply_link'    => __( 'Abbrechen', 'open3' ),
		'comment_notes_before' => '<p class="comment-notes">' . ( get_comments_number() ? '' : esc_html__( 'Noch keine Kommentare. ', 'open3' ) ) . esc_html__( 'E-Mail-Adresse wird nicht veröffentlicht.', 'open3' ) . '</p>',
		'comment_notes_after'  => '',
		'fields'               => $fields,
		'comment_field'        => sprintf(
			'<div class="field field-comment comment-form-comment"><label for="comment">%1$s</label><textarea id="comment" class="input" name="comment" rows="5" placeholder="%2$s" required></textarea></div>',
			esc_html__( 'Kommentar', 'open3' ),
			esc_attr__( 'Ihr Kommentar', 'open3' )
		),
		'must_log_in'          => '<p class="must-log-in">' . sprintf(
			/* translators: %s: login URL */
			wp_kses_post( __( 'Sie müssen <a href="%s">angemeldet</a> sein, um einen Kommentar abzugeben.', 'open3' ) ),
			esc_url( wp_login_url( (string) get_permalink() ) )
		) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf(
			/* translators: 1: profile URL, 2: display name, 3: logout URL */
			wp_kses_post( __( 'Angemeldet als <a href="%1$s">%2$s</a>. <a href="%3$s">Abmelden?</a>', 'open3' ) ),
			esc_url( get_edit_user_link() ),
			esc_html( wp_get_current_user()->display_name ),
			esc_url( wp_logout_url( (string) get_permalink() ) )
		) . '</p>',
		'label_submit'         => __( 'Kommentar absenden', 'open3' ),
		'class_form'           => 'comment-form o3-comment-form',
		'class_submit'         => 'btn btn-primary',
		'submit_button'        => '<button type="submit" name="%1$s" id="%2$s" class="%3$s">%4$s</button>',
		'submit_field'         => '<div class="form-submit">%1$s %2$s</div>',
		'format'               => 'html5',
	);
}

/**
 * Order: name, e-mail, (cookies), comment.
 *
 * @param array<string,string> $fields Fields.
 * @return array<string,string>
 */
function open3_comment_form_fields_order( array $fields ): array {
	if ( ! isset( $fields['comment'] ) ) {
		return $fields;
	}
	$comment = $fields['comment'];
	unset( $fields['comment'], $fields['url'] );
	$fields['comment'] = $comment;
	return $fields;
}
add_filter( 'comment_form_fields', 'open3_comment_form_fields_order' );

/**
 * Open the name/e-mail grid.
 *
 * @return void
 */
function open3_comment_form_before_fields(): void {
	echo '<div class="o3-comments__grid">';
}
add_action( 'comment_form_before_fields', 'open3_comment_form_before_fields' );

/**
 * Close the name/e-mail grid.
 *
 * @return void
 */
function open3_comment_form_after_fields(): void {
	echo '</div>';
}
add_action( 'comment_form_after_fields', 'open3_comment_form_after_fields' );

/**
 * Honeypot + timestamp fields (printed inside the form for everyone).
 *
 * @return void
 */
function open3_comment_form_spam_fields(): void {
	printf(
		'<div class="o3-hp" aria-hidden="true"><label for="o3_website">%1$s</label><input id="o3_website" name="o3_website" type="text" value="" tabindex="-1" autocomplete="off"></div>' .
		'<input type="hidden" name="o3_ts" value="%2$d">',
		esc_html__( 'Website', 'open3' ),
		(int) time()
	);
}
add_action( 'comment_form', 'open3_comment_form_spam_fields' );

/**
 * Reject comments that filled the honeypot or were sent within 4 seconds.
 *
 * @param array<string,mixed> $commentdata Comment data.
 * @return array<string,mixed>
 */
function open3_preprocess_comment( array $commentdata ): array {
	$type = isset( $commentdata['comment_type'] ) ? (string) $commentdata['comment_type'] : 'comment';
	if ( '' !== $type && 'comment' !== $type ) {
		return $commentdata;
	}
	if ( is_user_logged_in() ) {
		return $commentdata;
	}
	// Only the native wp-comments-post.php flow carries our fields.
	if ( ! isset( $_POST['comment_post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $commentdata;
	}

	$honeypot = isset( $_POST['o3_website'] ) ? trim( (string) wp_unslash( $_POST['o3_website'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$ts       = isset( $_POST['o3_ts'] ) ? (int) $_POST['o3_ts'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( '' !== $honeypot || $ts <= 0 || ( time() - $ts ) < 4 ) {
		wp_die(
			esc_html__( 'Kommentar abgelehnt.', 'open3' ),
			esc_html__( 'Kommentar abgelehnt', 'open3' ),
			array( 'response' => 403 )
		);
	}

	return $commentdata;
}
add_filter( 'preprocess_comment', 'open3_preprocess_comment' );

/**
 * Callback for wp_list_comments(): one comment (core closes the <li>).
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    List args.
 * @param int        $depth   Depth.
 * @return void
 */
function open3_comment( WP_Comment $comment, array $args, int $depth ): void {
	$id = (int) $comment->comment_ID;
	?>
	<li id="comment-<?php echo esc_attr( (string) $id ); ?>" <?php comment_class( 'comment', $comment ); ?>>
		<article id="div-comment-<?php echo esc_attr( (string) $id ); ?>" class="comment-body">
			<header class="comment-meta o3-tnum">
				<b class="comment-author"><?php comment_author_link( $comment ); ?></b>
				<time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>">
					<?php
					printf(
						/* translators: 1: date, 2: time */
						esc_html__( '%1$s · %2$s Uhr', 'open3' ),
						esc_html( get_comment_date( 'j. F Y', $comment ) ),
						esc_html( get_comment_time( 'H:i', false, true, $comment ) )
					);
					?>
				</time>
				<?php edit_comment_link( __( 'Bearbeiten', 'open3' ), '<span class="comment-edit"> ', '</span>', $comment ); ?>
			</header>
			<?php if ( '0' === (string) $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php esc_html_e( 'Ihr Kommentar wartet auf Freischaltung.', 'open3' ); ?></p>
			<?php endif; ?>
			<div class="comment-content"><?php comment_text( $comment ); ?></div>
			<?php
			comment_reply_link(
				array_merge(
					$args,
					array(
						'depth'      => $depth,
						'max_depth'  => $args['max_depth'] ?? (int) get_option( 'thread_comments_depth', 5 ),
						'reply_text' => __( 'Antworten', 'open3' ),
						'before'     => '<div class="reply">',
						'after'      => '</div>',
					)
				),
				$comment
			);
			?>
		</article>
	<?php
}
