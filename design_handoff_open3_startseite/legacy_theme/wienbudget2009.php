<?php
/*
Template Name: WienBudget2009
*/
?>
<?php get_header(); ?>
<div class="art-content-layout">
    <div class="art-content-layout-row">
<div class="art-layout-cell art-content" style="width:100%;">
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
 <div style="float:right;margin-left:10px;"><g:plusone size="medium"  href="<?php the_permalink() ?>"></g:plusone> </div>
 <div style="float:right;"><?php if (function_exists('Short_Button')){ echo Count_Button(); } ?></div>
  <div style="float:right;margin-right:10px;">
<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-lang="de" data-via="open3" data-related="robertharm" data-url="<?php the_permalink(); ?>">Tweet</a>
 </div>
<h2 class="art-postheader" id="hoch"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'kubrick'), the_title_attribute('echo=0')); ?>">
Wien Budget 2009 - Ausgaben als interaktive Grafik (f&uuml;r Details klicken)</a></h2>
<div class="art-postcontent">
<!-- article-content -->
Für weitere Visualisierungen basierend auf den im <a href="http://data.wien.gv.at" target="_blank">Open Data Katalog der Stadt Wien</a> veröffentlichten Finanzdaten bitte <a href="http://www.open3.at/ogdwien-finanzdaten">hier</a> klicken.
<?php
/*
if(is_ie())
{
echo "Hinweis: f&uuml;r eine optimale Anzeige bitte <a href=\"http://www.mozilla-europe.org/de/\" target=\"_blank\">Firefox</a>, <a href=\"http://www.apple.com/de/safari/\" target=\"_blank\">Safari</a> oder <a href=\"http://www.google.com/chrome\" target=\"_blank\">Google Chrome</a> verwenden!";
}*/
?>
<div id="id-list">
<table style="margin-bottom:10px;">
<tr><td><b>Anzeigeoptionen:</b> </td>
<td><label for="r-sq">Quadrate</label></td><td><input type="radio" id="r-sq" name="layout" checked="checked" value="left" /></td>
<td><label for="r-st">Streifen</label></td><td><input type="radio" id="r-st" name="layout" value="top" /></td>
<td><label for="r-sd">SliceandDice</label></td><td><input type="radio" id="r-sd" name="layout" value="bottom" /></td>
<td><a id="back" href="#hoch" class="white" style="text-decoration:none;font-size:14px;padding:5px;">Eine Ebene h&ouml;her anzeigen</a></td>
</tr></table>
</div>
<div id="container">
<div id="center-container">
<div id="infovis" style="position:relative;width:100%;height:600px;margin:auto;overflow:hidden;"></div>
</div>
<div id="log"></div>
</div>
<div id="container-bottom" style="margin-top:10px;">
<div style="text-align:center;"><a href="http://offener.datenkatalog.at/package/wien-budget-2009-ausgaben" target="_blank">Rohdaten auf http://www.datenkatalog.at anzeigen</a>, <a href="http://www.open3.at/wp-content/uploads/js-wienbudget/wien-budget2009-ausgaben.png">Download als Grafik</a></div>
<?php if (is_search()) the_excerpt(); else the_content(__('Read the rest of this entry &raquo;', 'kubrick')); ?>
<?php if (is_page() or is_single()) wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
</div>
<div class="cleared"></div>
<?php ob_start(); ?>
<?php $icons = array(); ?>
<?php if (!is_page()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postcategoryicon.png" width="18" height="18" alt="" />
<?php printf(__('Posted in %s', 'kubrick'), get_the_category_list(', ')); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page() && get_the_tags()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/posttagicon.png" width="18" height="18" alt="" />
<?php the_tags(__('Tags:', 'kubrick') . ' ', ', ', ' '); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page() && !is_single()): ?><?php ob_start(); ?><img class="art-metadata-icon" src="<?php bloginfo('template_url'); ?>/images/postcommentsicon.png" width="18" height="18" alt="" />
<?php comments_popup_link(__('No Comments &#187;', 'kubrick'), __('1 Comment &#187;', 'kubrick'), __('% Comments &#187;', 'kubrick'), '', __('Comments Closed', 'kubrick') ); ?>
<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (0 != count($icons)): ?>
<div class="art-postfootericons art-metadata-icons">
<?php echo implode(' | ', $icons); ?>
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
<?php comments_template(); ?>
<?php endwhile; endif; ?>
</div>
    </div>
</div>
<div class="cleared"></div>
<?php get_footer(); ?>