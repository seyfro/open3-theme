<?php get_header(); ?>
<div class="art-content-layout">
    <div class="art-content-layout-row">
<div class="art-layout-cell art-content">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
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
<meta property="og:description" content="<?php the_excerpt(); ?>"/>
<h2 class="art-postheader">
<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'kubrick'), the_title_attribute('echo=0')); ?>">
<?php the_title(); ?>
</a>
</h2>
<?php ob_start(); ?>
<?php $icons = array(); ?>
<?php if (!is_page()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postdateicon.png" width="18" height="18" alt="" />
<?php the_time(__('F jS, Y', 'kubrick')) ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postauthoricon.png" width="14" height="14" alt="" />
Autor: <?php the_author_link() ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (current_user_can('edit_post', $post->ID)): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postediticon.png" width="14" height="14" alt="" />
<?php edit_post_link(__('e', 'kubrick'), ''); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (0 != count($icons)): ?>
<div class="art-postheadericons art-metadata-icons">
<?php echo implode(' | ', $icons); ?>
 <div style="float:right;margin-left:10px;"><g:plusone size="medium"  href="<?php the_permalink() ?>"></g:plusone> </div>
 <div style="float:right;"><?php if (function_exists('Short_Button')){ echo Count_Button(); } ?></div>
</div>
<?php endif; ?>
<?php $metadataContent = ob_get_clean(); ?>
<?php if (trim($metadataContent) != ''): ?>
<div class="art-postmetadataheader">
<?php echo $metadataContent; ?>
</div>
<?php endif; ?>
<div class="art-postcontent">
    <!-- article-content -->
          <?php if (is_search()) the_excerpt(); else the_content(__('Read the rest of this entry &raquo;', 'kubrick')); ?>
          <?php if (is_page() or is_single()) wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
        
    <!-- /article-content -->
</div>
<div class="cleared"></div>
<?php ob_start(); ?>
<?php $icons = array(); ?>
<?php if (!is_page()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postcategoryicon.png" width="18" height="18" alt="" />
<?php printf(__('Kategorie: %s', 'kubrick'), get_the_category_list(', ')); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page() && get_the_tags()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/posttagicon.png" width="18" height="18" alt="" />
<?php the_tags(__('Themen:', 'kubrick') . ' ', ', ', ' '); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page() && !is_single()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postcommentsicon.png" width="18" height="18" alt="" />
<?php comments_popup_link(__('Kommentar hinzuf&uuml;gen &#187;', 'kubrick'), __('1 Kommentar &#187;', 'kubrick'), __('% Kommentare &#187;', 'kubrick'), '', __('Comments Closed', 'kubrick') ); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (0 != count($icons)): ?>
<div class="art-postfootericons art-metadata-icons">
<?php echo implode(' | ', $icons); ?>
<br/>
 <div style="float:right;margin-left:10px;"><g:plusone size="medium"  href="<?php the_permalink() ?>"></g:plusone> </div>
 <div style="float:right;"><?php if (function_exists('Short_Button')){ echo Count_Button(); } ?></div>
</div>
<?php endif; ?>
<?php $metadataContent = ob_get_clean(); ?>
<?php if (trim($metadataContent) != ''): ?>
<div class="art-postmetadatafooter">
<?php echo $metadataContent; ?>
</div>
<?php endif; ?>
</div>
		<div class="cleared"></div>
    </div>
</div>
<?php
$prev_link = get_previous_post_link('&laquo; %link');
$next_link = get_next_post_link('%link &raquo;');
?>
<?php if ($prev_link || $next_link): ?>
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
<div class="navigation">
	<div class="alignleft"><?php echo $prev_link; ?></div>
	<div class="alignright"><?php echo $next_link; ?></div>
</div>
    <!-- /article-content -->
</div>
<div class="cleared"></div>
</div>
		<div class="cleared"></div>
    </div>
</div>
<?php endif; ?>
<?php comments_template(); ?>
<?php endwhile; ?>
<?php else: ?>
<h2 class="center"><?php _e('Sorry, no posts matched your criteria.', 'kubrick'); ?></h2>
<?php endif; ?>
</div>
<?php include (TEMPLATEPATH . '/sidebar1.php'); ?>
    </div>
</div>
<div class="cleared"></div>
<?php get_footer(); ?>