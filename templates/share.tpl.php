<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type"
			content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title><?php bloginfo('name'); ?> &rsaquo; <?php
			_e('Share this with your friends!', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?></title>
		<link type="text/css" rel="stylesheet"
			href="<?php print REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/social-network-icons.css'; ?>" />
		<link type="text/css" rel="stylesheet"
			href="<?php print REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/popup.css'; ?>" />
		<!--[if IE 6]>
			<link type="text/css" rel="stylesheet"
				href="<?php print REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/ie6.css'; ?>" />
		<![endif]-->
		<!--[if IE 7]>
			<link type="text/css" rel="stylesheet"
				href="<?php print REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/ie7.css'; ?>" />
		<![endif]-->
		<!--[if IE 8]>
			<link type="text/css" rel="stylesheet"
				href="<?php print REACT_SOCIAL_ANALYTICS_APPLICATION_URL . '/css/ie8.css'; ?>" />
		<![endif]-->
	</head>
	<body id="react-social">
		<h1><?php _e('Share this with your friends!', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?></h1>
		<form action="?action=share" method="post">
			<div id="form-head">
				<div id="hidden-form-fields">
					<input type="hidden" name="http_referer"
						value="<?php echo get_react_social_analytics_plugin()->getReferer(); ?>" />
					<input type="hidden" name="url"
						value="<?php echo htmlspecialchars($data['url']) ?>" />
					<input type="hidden" name="title"
						value="<?php echo htmlspecialchars($data['title']) ?>" />
					<input type="hidden" name="img_url"
						value="<?php echo htmlspecialchars($data['img_url']) ?>" />
					<?php wp_nonce_field('react-social-analytics-share') ?>
				</div>
				<div id="socialmedia-networks">
					<p>Share this on:</p>
					<ul>
						<?php foreach($this->getProviders() as $providerName): ?>
							<?php
							if(!in_array($providerName, $this->getShareProviders()))
								continue;

							$connected = in_array($providerName, $userProviders);
							?>
						<li class="<?php echo strtolower($providerName);
								echo $connected? ' connected selected' : '' ?>"
							title="<?php echo $connected
								? __(sprintf('Remove %s from selection?', $providerName), REACT_SOCIAL_ANALYTICS_TEXTDOMAIN)
								: __(sprintf('Add %s to selection', $providerName), REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) ?>">
							<input type="checkbox" name="providers[]" <?php
								echo $connected? ' checked="checked" ' : '' ?>
								id="react-social-provider-<?php echo $providerName ?>"
								value="<?php echo $providerName ?>" class="provider-checkbox" />
							<label for="react-social-provider-<?php echo $providerName ?>">
								<span class="react-social-analytics-network-icon">
									<span class="name"><?php echo $providerName ?></span>
								</span>
							</label>
						</li>
						<?php endforeach ?>
					</ul>
				</div>
			</div>
			<fieldset>
				<dl>
					<dt><label for="share-message"><?php
							_e('Your message', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN)
					?></label></dt>
					<dd>
						<textarea id="share-message" name="message" rows="5" cols="80"><?php
							echo htmlspecialchars($data['comment'])
						?></textarea>
						<p>The URL will be added to your message automatically.</p>
					</dd>
				</dl>
			</fieldset>
			<div id="share-submit-wrapper">
				<img id="ajax-indicator" alt="Sending..." title="" style="display: none;"
					src="<?php print REACT_SOCIAL_ANALYTICS_APPLICATION_URL ?>/images/loader.gif" />
				<input id="share-button" type="submit"
					value="<?php _e('Share', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?>" />
			</div>
		</form>
		<p id="react-social-share-result">
			<span id="react-social-share-success" style="display: none"><?php
				_e('Thank you for sharing!') ?></span>
			<span id="react-social-share-error" style="display: none"><?php
				_e('Oops, there was an error while sending your message.') ?></span>
			<span id="react-social-share-error-detail" style="display: none">&nbsp;</span>
		</p>
		<script type="text/javascript"
			src="<?php echo get_bloginfo('url') ?>/wp-includes/js/jquery/jquery.js"></script>
		<script type="text/javascript"
			src="<?php echo REACT_SOCIAL_ANALYTICS_APPLICATION_URL ?>/js/analytics.js"></script>
		<!--[if IE 6]>
			<script type="text/javascript">ReactSocialAnalytics.fixIE6();</script>
		<![endif]-->
	</body>
</html>
