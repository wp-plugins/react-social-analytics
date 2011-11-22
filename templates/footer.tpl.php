<script type="text/javascript">
	// these are used in share.js
	var reactSocialAnalyticsSocialErrorHeader = '<?php _e('Oops!', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?>';
	var reactSocialAnalyticsSocialErrorIntro = '<?php _e('Unfortunately, something went wrong...', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?>';
	var reactSocialAnalyticsSocialError = <?php isset($reactSocialAnalyticsSocialError) ? print('"' . $reactSocialAnalyticsSocialError . '"') : print('null') ?>;
	var reactSocialAnalyticsSocialPopup = <?php isset($socialPopup) ? print('"' . $socialPopup . '"') : print('null') ?>;
</script>
