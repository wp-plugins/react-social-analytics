<?php

switch (gettype($admin_save_result))
{
	case 'array':
		echo '<div class="error"><p><strong>';
		echo implode('<br />', array_map('htmlspecialchars', $admin_save_result));
		echo '</strong></p></div>';
	break;

	case 'boolean':
		echo '<div id="message" class="updated fade"><p><strong>' . __('Successfully connected to all services and stored your settings.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) . '</strong></p></div>';
	break;
}

if(!empty($cacheConfig) && $cacheConfig['pgcache.cache.query'] === true)
	echo '<div class="error"><p>' . __('<strong>Please disable</strong> caching of pages with query string variables. It may interfere with this plugin. See <em>Performance -> Page Cache -> General -> &quot;Cache URIs with query string variables&quot;</em>.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) . '</p></div>';

?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>

	<h2>React Social Analytics Settings</h2>

	<form method="post" action="<?php echo get_option('siteurl') . '/wp-admin/options-general.php?page=reactSocialAnalyticsOptions';?>">
		<h3>Application</h3>
		<p><strong>Looking for keys?</strong> Log in at <a href="<?php echo REACT_SOCIAL_ANALYTICS_ACCOUNT_URL; ?>" target="_blank">account.react.com</a> and click the name of your application in the applications section.</p>
		<p><strong>Don't have an account yet?</strong> <a href="<?php echo REACT_SOCIAL_ANALYTICS_ACCOUNT_URL; ?>signup" target="_blank">Create one now</a>.</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="reactApplicationKey">Key</label></th>
				<td><input name="react[reactApplicationKey]" type="text" id="reactApplicationKey" value="<?php echo get_option('reactApplicationKey')?>" class="regular-text" style="width:40em;"/></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="reactApplicationSecret">Secret</label></th>
				<td><input name="react[reactApplicationSecret]" type="password" id="reactApplicationSecret" value="<?php echo get_option('reactApplicationSecret')?>" class="regular-text" style="width:40em;"/></td>
			</tr>
			<tr>
				<th>Connected</th>
				<td>
					<?php if($this->connectionTest()): ?>
						<img src="<?php echo admin_url() ?>/images/yes.png" alt="Yes"
							title="Connection works!" id="connection-test-yes" />
					<?php else: ?>
						<img src="<?php echo admin_url() ?>/images/no.png" alt="No" id="connection-test-no" />
							<?php
								if(!get_option('reactApplicationKey') || !get_option('reactApplicationKey')): ?>
									Not connected, please fill in key and secret first
								<?php else: ?>
									Not connected, check key and secret
								<?php endif ?>
					<?php endif ?>
				</td>
			</tr>
		</table>

		<h3>Endpoints</h3>
		<p>All endpoints can be found in <a href="<?php echo REACT_SOCIAL_ANALYTICS_ACCOUNT_URL; ?>docs" target="_blank">our documentation</a>.</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="reactOAuthServiceEndpoint">OAuth service URL</label></th>
				<td><input name="react[reactOAuthServiceEndpoint]" type="text" id="reactOAuthServiceEndpoint" value="<?php echo get_option('reactOAuthServiceEndpoint', REACT_SOCIAL_ANALYTICS_DEFAULT_OAUTHSERVICE_URL)?>" class="regular-text" style="width:40em;"/></td>
			</tr>
<!--
			<tr valign="top">
				<th scope="row"><label for="reactLikeServiceEndpoint">Like service URL</label></th>
				<td><input name="react[reactLikeServiceEndpoint]" type="text" id="reactLikeServiceEndpoint" value="<?php echo get_option('reactLikeServiceEndpoint', REACT_SOCIAL_ANALYTICS_DEFAULT_LIKESERVICE_URL)?>" class="regular-text" style="width:40em;"/></td>
			</tr>
-->
			<tr valign="top">
				<th scope="row"><label for="reactShareServiceEndpoint">Share service URL</label></th>
				<td><input name="react[reactShareServiceEndpoint]" type="text" id="reactShareServiceEndpoint" value="<?php echo get_option('reactShareServiceEndpoint', REACT_SOCIAL_ANALYTICS_DEFAULT_SHARESERVICE_URL)?>" class="regular-text" style="width:40em;"/></td>
			</tr>
		</table>

		<h3>Enable optional services</h3>
		<table class="form-table">
<!--
			<tr valign="top">
				<th scope="row">
					<input type="hidden" name="react[reactLikeServiceEnabled]" value="0" />
					<label><input type="checkbox" value="1"
						<?php checked(get_option('reactLikeServiceEnabled', 0), 1) ?>
						name="react[reactLikeServiceEnabled]" /> Like service</label>
				</th>
			</tr>
-->
			<tr valign="top">
				<th scope="row">
					<input type="hidden" name="react[reactShareServiceEnabled]" value="0" />
					<label><input type="checkbox" value="1"
						<?php checked(get_option('reactShareServiceEnabled', 0), 1) ?>
						name="react[reactShareServiceEnabled]" /> Share service</label>
				</th>
			</tr>
		</table>

		<h3>Share service</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					Default message
				</th>
				<td>
					<textarea name="react[reactShareDefaultMessage]" rows="4" cols="55"><?php
						echo get_option('reactShareDefaultMessage');
					?></textarea>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?>" />
		</p>
	</form>
</div>
