<?php
// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');
	if ( post_password_required() ) { ?>
		<p class="nocomments">Dieser Beitrag ist passwortgesch&uuml;tzt - bitte geben Sie das Passwort ein, um den Beitrag anzuzeigen.</p>
	<?php
		return;
	}
?>
<!-- You can start editing here. -->
<div class="art-post">
    <div class="art-post-tl"></div>
    <div class="art-post-tr"></div>
    <div class="art-post-bl"></div>
    <div class="art-post-br"></div>
    <div class="art-post-tc"></div>
    <div class="art-post-bc"></div>
    <div class="art-post-cl"></div>
    <div class="art-post-cr"></div>
    <div class="art-post-cc"></div>
    <div class="art-post-body">
<div class="art-post-inner art-article">
<div class="art-postcontent">
    <!-- article-content -->
<div class="CommentsSection">
    
<div class="CommentsForm" id="skiplink-kommentarformular">
        <?php if ( comments_open() ) : ?>
        
        <div id="respond">
        
        <div class="CommentsTitle">
        <?php if (is_page(28824)) {; ?>
        <h3>Beitrag einreichen</h3>
        <?php } else {; ?>
        <h3><?php comment_form_title( 'Kommentar hinzuf&uuml;gen', 'Kommentar hinzuf&uuml;gen zu %s' ); ?></h3>
        <?php }; ?>
        </div>
        
        <div class="cancel-comment-reply">
            <?php cancel_comment_reply_link(); ?>
        </div>
        
        <?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
        <p>Sie m&uuml;ssen <a href="<?php echo wp_login_url( get_permalink() ); ?>">eingeloggt</a> sein, um einen Kommentar hinzuf&uuml;gen zu k&ouml;nnen.</p>
        <?php else : ?>
        <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
        
        <?php if ( is_user_logged_in() ) : ?>
        <p><span style="padding:3px;background:#CCCCCC;">Eingeloggt als <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Abmelden">Abmelden &raquo;</a></span></p>
        <?php else : ?>
        <p class="CommentFormName"><input type="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" <?php if ($req) echo "aria-required='true'"; ?> maxlength="100" />
        <label for="author" style="width:365px;"><small>Name (gerne auch Nickname)<?php if ($req) echo "(ben&ouml;tigt)"; ?></small></label></p>
        <p class="CommentFormEmail" style="clear:both;"><input type="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" <?php if ($req) echo "aria-required='true'"; ?> />
        <label for="email" style="width:365px;"><small>E-Mail (wird nicht ver&ouml;ffentlicht, sollte aber echt sein) <?php if ($req) echo "(ben&ouml;tigt)"; ?></small></label></p>
        <p class="CommentFormWebsite" style="clear:both;"><input type="text" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" maxlength="100" />
        <label for="url" style="width:365px;"><small><?php if (is_page(28824)) {; ?>Youtube-Link (optional, bringt aber Pluspunkte :-) )<?php } else {; ?>Webseite<?php }; ?></small></label></p>
        <?php endif; ?>
        
        <p>
<?php if (is_page(28824)) {; ?><br/>Maximal 2011 Zeichen<?php } else {; ?><?php }; ?>        
<textarea name="comment" id="comment" style="height:150px;" maxlength="2011"></textarea></p>
        
        <p><input name="submit" type="submit" id="submit" value="absenden" />
        <?php comment_id_fields(); ?>
        </p>
        <?php do_action('comment_form', $post->ID); ?>
        </form>
        <?php endif; // If registration required and not logged in ?>
        </div>
       
        <?php endif; // if you delete this the sky will fall on your head ?>
    </div>
    
    
    <div class="CommentsList" id="skiplink-kommentare">

        <?php if ( have_comments() ) : ?>
            <div class="CommentsTitle" style="clear:both;">
            <?php if (is_page(28824)) {; ?>
       	     <h3 id="comments">Alle 23 Einreichungen</h3>
            <?php } else {; ?>
             <h3 id="comments">Kommentare</h3>
            <?php }; ?>
            </div>
     
<?php post_comments_feed_link( $link_text = '<p style="margin:0px;padding:0px;"><span style="padding:4px;background:#CCCCCC;"><img src="https://www.open3.at/wp-content/uploads/rss-icon-comments.png" alt="RSS Comments Icon" /> Kommentare per RSS abonnieren</span></p>', $post_id = '', $feed = 'rss2' ) ?>
        
            <div class="navigation">
                <div class="alignleft"><?php previous_comments_link() ?></div>
                <div class="alignright"><?php next_comments_link() ?></div>
            </div>

            <ol class="commentlist" style="margin:20px 0;">
		<?php wp_list_comments('type=comment&callback=uflex_comment&avatar_size=100'); ?>
            </ol>
 <?php if (is_page(28824)) {; ?>
            <div class="CommentsTitle" style="clear:both;"><h3 id="comments">Links zum Poetry Contest</h3></div>
        <?php } else {; ?>
            <div class="CommentsTitle" style="clear:both;"><h3 id="comments">Links zum Artikel</h3></div>
        <?php }; ?>
<p> <span style="padding:3px;background:#CCCCCC;width:90%;"><a href="http://de.wikipedia.org/wiki/Trackback" title="Wikipedia-Eintrag zu Trackbacks anzeigen" target="_blank">Trackback-URL:</a></span> <input type="text" style="width:409px;font-size:90%;font-family:Verdana,Arial;padding:2px;" value="<?php trackback_url(); ?>"/></p>
                   
<?php foreach($comments as $comment) : ?>  
<?php if (($comment->comment_type == 'pingback') || ($comment->comment_type == 'trackback')) : ?>  
<ul style="padding:0 20px 7px 0;">
<li id="comment-<?php comment_ID(); ?>" style="list-style-type:none;background:none;">  
<div style="background:#FFFFFF;width:95%;padding:7px 7px 0 7px;">
<p><?php comment_type(); ?> von <?php comment_author_link(); ?> am <?php comment_date(); ?> um <?php comment_time(); ?> Uhr</p>
<?php comment_text(); ?> 
</div>
</li>
</ul>  
<?php endif; ?>  
<?php endforeach; ?>  
            <div class="navigation">
                <div class="alignleft"><?php previous_comments_link() ?></div>
                <div class="alignright"><?php next_comments_link() ?></div>
            </div>
         <?php else : // this is displayed if there are no comments so far ?>
            <?php if ( comments_open() ) : ?>
                <!-- If comments are open, but there are no comments. -->
                <div class="CommentsTitle" style="clear:both;">
                <?php if (is_page(28824)) {; ?>
       	        <h3 id="comments">Bisherige Einreichungen</h3></div>
                <p class="nocomments"><span style="padding:3px;background:#CCCCCC;width:90%;">Derzeit noch keine Einreichungen - Sie k&ouml;nnten dies aber &auml;ndern :-)</span></p>
                <?php } else {; ?>
                <h3 id="comments">Bisherige Kommentare</h3></div>
                <p class="nocomments"><span style="padding:3px;background:#CCCCCC;width:90%;">Derzeit noch keine Kommentare</span></p>
                <?php }; ?>
                
                
        
             <?php else : // comments are closed ?>
                <!-- If comments are closed. -->
                <!--<div class="CommentsTitle"><h3 id="comments">Bisherige Kommentare</h3></div>
                <p class="nocomments">Die Kommentarfunktion f&uuml;r diesen Beitrag wurden deaktiviert.</p>-->
        
            <?php endif; ?>
            
        <?php endif; ?>
        
        
</div>
</div>        
</div>
</div>
        
</div>
</div>