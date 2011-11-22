<?php echo get_react_social_html_helper()->doctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<?php echo get_react_social_html_helper()->head('Connected') ?>
	<body class="share-connect">
		<p><?php _e('Connected', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?></p>
		<script type="text/javascript">
			if(parent.opener)
			{
				var provider = <?php
					if(empty($_GET['provider']))
						echo 'false';
					else
						echo '"' . strtolower(preg_replace('~[^\w-]~', '', $_GET['provider'])) . '"';
?>;

				try { parent.opener.ReactSocialAnalytics.connectProvider(provider); }
				catch(e) {};

				window.close();
			}
		</script>
	</body>
</html>
