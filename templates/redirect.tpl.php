<?php echo get_react_social_html_helper()->doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<?php echo get_react_social_html_helper()->head('Redirecting') ?>
	<body class="redirect">
		<p><?php _e('Redirecting', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?>&hellip;</p>

		<script type="text/javascript">
			top.document.location = '<?php echo $url; ?>';
		</script>
	</body>
</html>
