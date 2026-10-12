<div class="art-footer">
    <div class="art-footer-inner">
        <div class="art-footer-text">
<p>
<?php
 global $default_footer_content;
 $footer_content = stripslashes(get_option('art_footer_content')); 
 if ($footer_content === false) $footer_content = $default_footer_content;
 echo $footer_content;
?>
</p>
</div>
    </div>
    <div class="art-footer-background">
    </div>
</div>
		<div class="cleared"></div>
    </div>
</div>
<div class="cleared"></div>
<p class="art-page-footer"></p>
</div>
<!-- <?php printf(__('%d queries. %s seconds.', 'kubrick'), get_num_queries(), timer_stop(0, 3)); ?> -->
<?php ob_start(); wp_footer(); $content = ob_get_clean(); if (strlen($content)) echo '<div>' . $content . '</div>'; ?>
<?php if ( !is_user_logged_in() ) { ?>
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.mapsmarker.com/piwik/" : "http://www.mapsmarker.com/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 3);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="https://www.mapsmarker.com/piwik/piwik.php?idsite=3" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Tag -->
<?php }; ?>
</body>
</html>