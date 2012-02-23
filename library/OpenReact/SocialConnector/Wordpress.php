<?php
/**
	OpenReact

  	LICENSE:
  	This source file is subject to the Simplified BSD license that is
  	bundled	with this package in the file LICENSE.txt.
	It is also available through the world-wide-web at this URL:
	http://account.react.com/license/simplified-bsd
	If you did not receive a copy of the license and are unable to
	obtain it through the world-wide-web, please send an email
	to openreact-license@react.com so we can send you a copy immediately.

	Copyright (c) 2011 React B.V. (http://www.react.com)
*/
// Wordpress applies add_magic_quotes, so we must always strip
define('SOCIALCONNECTOR_FORCE_STRIPSLASHES', true);

class OpenReact_SocialConnector_Wordpress extends OpenReact_SocialConnector_ApplicationAbstract
{
	protected $_wordpressUser;
	protected $_pendingTokenRequest;

	protected $_actionHooks = array(
		array(
			'hook' => 'login_head',
			'function' => 'hookLoginHead',
		),
		array(
			'hook' => 'login_form',
			'function' => 'hookLoginForm',
		),
		array(
			'hook' => 'register_form',
			'function' => 'hookRegisterForm'
		),
		array (
			'hook' => 'login_form_register',
			'function' => 'hookLoginFormRegister',
		),
		array(
			'hook' => 'show_user_profile',
			'function' => 'hookShowUserProfile',
		),
		array(
			'hook' => 'personal_options_update',
			'function' => 'hookEditUserProfileUpdate',
		),
		array(
			'hook' => 'wp_head',
			'function' => 'hookHead',
		),
		array(
			'hook' => 'wp_footer',
			'function' => 'hookFooter',
		),
		array(
			'hook' => 'lostpassword_form',
			'function' => 'hookLostPasswordForm',
		),
		array(
			'hook' => 'deleted_user',
			'function' => 'hookDeletedUser',
		),
		array(
			'hook' => 'widgets_init',
			'function' => 'hookWidgetsInit',
		),
		array(
			'hook' => 'wp_login',
			'function' => 'hookLogin',
		),
		array(
			'hook' => 'admin_init',
			'function' => 'hookAdminInit',
		),
		array(
			'hook' => 'admin_notices',
			'function' => 'hookAdminNotices',
		),
		array(
			'hook' => 'user_register',
			'function' => 'hookUserRegister',
		),
		array(
			'hook' => 'plugin_action_links',
			'function' => 'hookAdminPluginLinks',
		),
		array(
			'hook' => 'the_content',
			'function' => 'hookTheContent',
			'priority' => 11,
		),
	);
	protected $_actions = array(
		'accessToken',
		'logout',
		'requestToken',
		'disconnect',
		'registerUserEmailDuplicate',
		'share',
		'shareconnect',
	);
	protected $_config = array(
		'reactOAuthServiceEndpoint' => null,
		'reactLikeServiceEndpoint' => null,
		'reactShareServiceEndpoint' => null,
		'reactApplicationKey' => null,
		'reactApplicationSecret' => null,
		'reactLikeServiceEnabled' => null,
		'reactShareServiceEnabled' => null,
		'reactShareDefaultMessage' => null,
		'reactProviderOrder' => array(),
	);

	private $_cache;
	private $_adminNotices = array();
	private $_adminErrors = array();

	const SOCIAL_ERROR = 'social_error';
	const OAUTH_SESSION_KEY = 'registerSocialSession';
	const MENU_SLUG = 'reactSocialAnalyticsOptions';

	public function __construct()
	{
		$this->_cache = new OpenReact_Wordpress_Cache();
		$this->_wordpressUser = new OpenReact_SocialConnector_Wordpress_User($GLOBALS['wpdb']);
		$this->_init();
	}

	private function _init()
	{
		// http://codex.wordpress.org/I18n_for_WordPress_Developers
		load_plugin_textdomain(REACT_SOCIAL_ANALYTICS_TEXTDOMAIN, null, basename(dirname(__FILE__)));

		$this->_initSettings();

		if ($this->isEnabled())
		{
			$this->_initClient();

			add_action('init', array($this, 'hookInit'));

			foreach ($this->_actionHooks as $hook)
			{
				add_action($hook['hook'], array($this, $hook['function']),
					(isset($hook['priority'])? $hook['priority'] : null), 99);
			}

			if(!empty($_GET['action']))
				foreach ($this->_actions as $action)
				{
					if ($action == $_GET['action'])
						add_action('init', array($this, 'handle' . ucfirst($action) . 'Action'));
				}
		}

		// Regardless of enabled-status, show admin menu
		add_action('admin_menu', array($this, 'hookAdminMenu'));

		register_activation_hook(REACT_SOCIAL_PLUGIN_NAME, array($this, 'hookPluginActivation'));
	}

	public function hookWidgetsInit()
	{
		register_widget('OpenReact_SocialConnector_ProviderWidget');
	}

	public function isEnabled()
	{
		return (null !== $this->getSettings());
	}

	private function _initSettings()
	{
		foreach (array_keys($this->_config) as $configKey)
		{
			$this->_config[$configKey] = get_option($configKey);
		}
	}

	public function getClient()
	{
		return $this->_client;
	}

	public function getSettings()
	{
		if (false === $this->_config['reactOAuthServiceEndpoint'])
			return null;

		return array(
			'endpoints' => array(
				$this->_config['reactOAuthServiceEndpoint'] => array('OAuthServer'),
				$this->_config['reactLikeServiceEndpoint']  => array('Like'),
				$this->_config['reactShareServiceEndpoint'] => array('Share'),
			),
			'applicationKey' => $this->_config['reactApplicationKey'],
			'applicationSecret' => $this->_config['reactApplicationSecret'],
		);
	}

	public function getReturnUrl($allowLoginPage = false)
	{
		if(!$allowLoginPage && $this->_getSessionData('returnUrl') == wp_login_url())
			return $this->getDefaultUrl();

		return $this->_getSessionData('returnUrl');
	}

	public function getReferer()
	{
		$data = OpenReact_SocialConnector::magicQuotesUndo($_POST);

		if (isset($data['http_referer']))
			return $data['http_referer'];
		else
			return parent::getReferer();
	}

	/*
	 * storeReturnUrl is called @ tokenRequest. If there's a pending social action, this means
	 * we're being redirected to a social network for authentication. redirectUser() needs to know this
	 * so we can break out of the iframe layer to prevent problems with framebusters from facebook / linkedin and twitter
	 */
	public function storeReturnUrl($value)
	{
		if($this->hasPendingSocialAction())
			$this->_pendingTokenRequest = true;

		return parent::storeReturnUrl($value);
	}

	public function getApplicationName()
	{
		return get_bloginfo('name');
	}

	/*
	 * Log user in using WP userid.
	 */
	public function logUserIn($userId)
	{
		$this->_wordpressUser->setLoggedIn($userId);
		$this->hookLogin();
		$this->redirectUser($this->getReturnUrl(), true);
		exit;
	}

	public function getUserId()
	{
		return $this->_wordpressUser->getCurrentWpUserId();
	}

	public function registerUser($provider, $oauthSession)
	{
		try
		{
			$profileData = $this->_client->OAuthServer->sessionGetProfile($oauthSession);
			$applicationUserId = $this->_wordpressUser->create($profileData);
		}
		catch (OpenReact_SocialConnector_Wordpress_User_EmailInvalidException $e)
		{
			// no email in the social profile, redirect to registration
			// user will be connected after registration and login
			$this->_storeSessionData(self::OAUTH_SESSION_KEY, $oauthSession);
			$this->_redirectToRegistration($profileData, $provider);
		}
		catch (OpenReact_SocialConnector_Wordpress_User_ExistingEmailException $e)
		{
			// user must login first, will then be connected
			$this->_storeSessionData(self::OAUTH_SESSION_KEY, $oauthSession);
			$this->redirectUser($this->getDefaultUrl() . '?action=registerUserEmailDuplicate&email=' . urlencode($profileData['email']) . '&provider=' . urlencode($provider));
		}
		catch (Exception $e)
		{
			if (strpos(get_class($e), 'OpenReact_SocialConnector_Wordpress_User_') === 0)
				$this->storeSocialError($e, true);
			else
				$this->storeSocialError($e);
			$this->redirectUser($this->getReturnUrl(true));
			exit;
		}

		try
		{
			$this->_client->OAuthServer->tokenSetUserId($applicationUserId, $oauthSession);
		}
		catch (Exception $e)
		{
			$this->storeSocialError($e);
			$this->redirectUser($this->getReturnUrl(true));
			exit;
		}

		// log user in and send him to the profile page
		$this->storeReturnUrl(site_url(''));
		$this->logUserIn($applicationUserId);
		exit();
	}

	/**
	 * Redirect to the registration page, prefilling name if possible
	 * @param (array) $profileData from social network
	 */
	private function _redirectToRegistration($profileData, $provider)
	{
		$name = isset($profileData['user_name']) ? $profileData['user_name'] : '';
		if (empty($name))
		{
			$name = isset($profileData['real_name']) ? $profileData['real_name'] : '';
		}

		$this->redirectUser(site_url('wp-login.php?action=register&reactsocialanalyticsusername=' . urlencode($name)).'&reactsocialanalyticsprovider=' . urlencode($provider));
	}

	public function hookLoginFormRegister()
	{
		if (isset($_GET['reactsocialanalyticsusername'], $_GET['reactsocialanalyticsprovider']))
		{
			global $error;
			$error = sprintf(__('Please provide your email address to complete your registration. Your %1$s account will be connected automatically after this registration.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), htmlentities($_GET['reactsocialanalyticsprovider']));
		}
	}

	public function handleRegisterUserEmailDuplicateAction()
	{
		$userEmail = $_GET['email'];
		$provider = $_GET['provider'];
		require_once (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/registerUserEmailDuplicate.tpl.php');
		exit();
	}

	/*
	 * Check if there is a oauth session stored in the user session and add the network to the logged in user
	 */
	private function _oauthUserSessionCheck($userId, $email = null)
	{
		if (!$this->_getSessionData(self::OAUTH_SESSION_KEY))
			return false;

		try
		{
			$profile = $this->_client->OAuthServer->sessionGetProfile($this->_getSessionData(self::OAUTH_SESSION_KEY));

			// only connect if email matches profile email or profile has no email
			if (empty($profile['email']) || $profile['email'] == $email)
			{
				$this->_client->OAuthServer->tokenSetUserId($userId, $this->_getSessionData(self::OAUTH_SESSION_KEY));
				$this->_cache->delete($this->_getUserProvidersCacheKey($userId));
				$this->_removeSessionData(self::OAUTH_SESSION_KEY);
				return true;
			}
		}
		catch (Exception $e) {} // do nothing

		$this->_removeSessionData(self::OAUTH_SESSION_KEY);
		return false;
	}

	public function hookLogin($credentials = null)
	{
		global $user;
		$loggedInUser = !empty($user) ? $user : wp_get_current_user();
		$this->_oauthUserSessionCheck($loggedInUser->ID, $loggedInUser->user_email);
	}

	public function getDefaultUrl()
	{
		return get_option('siteurl');
	}

	public function storePendingSocialAction($value)
	{
		$this->_pendingTokenRequest = true;

		return parent::storePendingSocialAction($value);
	}

	/*
	 * Redirects user to given url. If there's a pending social action, the user needs to authenticate using a social network.
	 * To prevent any problems with framebusters from twitter and facebook, we'll open the network in top.document
	 */
	public function redirectUser($url, $framebuster = false)
	{
		if (empty($url))
			$url = get_option('siteurl');

		// Enrich $url with extra queryvariable to fool agressive caching
		$url = $url . (strpos($url, '?') ? '&' : '?') . 't=' . time();

		if($this->_pendingTokenRequest || $framebuster)
		{
			$this->_pendingTokenRequest = false;

			require_once (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/redirect.tpl.php');
			exit();
		}
		else
		{
			wp_redirect($url);
			die('Redirecting you to <a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($url) . '</a>');
		}
	}

	public function socialActionCompleted($provider, $method, $parameters, $result)
	{
		if ($provider != 'Hyves' || $method != 'doMediaUpload')
			return;

		// Put media in 'Everyone' album
		try
		{
			foreach ($this->_client->Hyves->albumsGetByUser() as $album)
			{
				if ($album['title'] != 'Everyone' || empty($album['albumid']))
				continue;

				$this->_client->Hyves->albumsAddMedia($album['albumid'], array($result));
			}
		}
		catch (Exception $e)
		{
			// Do nothing, media still uploaded successfully, but wasn't put in an album yet
		}
	}

	/*
	 * The functions below hook into wordpress actions or actionhooks to add or replace functions
	 */
	public function hookInit()
	{
		$this->handleLikePost();
	}

	public function hookHead()
	{
		if($socialPopup = $this->_getSessionData('socialPopup'))
		{
			// Enrich $socialPopup with extra queryvariable to fool agressive caching
			$socialPopup = $socialPopup . (strpos($socialPopup, '?') ? '&' : '?') . 't=' . time();

			$this->_removeSessionData('socialPopup');
		}

		$socialError = $this->getSocialError();

		require_once (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/head.tpl.php');
	}

	public function hookFooter()
	{
		$reactSocialAnalyticsSocialError = $this->getSocialError();
		require_once (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/footer.tpl.php');
	}

	public function hookLoginHead()
	{
		global $error;

		if($this->getSocialError())
			$error = '<strong>Sorry, something went wrong: </strong>' . htmlspecialchars($this->getSocialError());

		require_once (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/head_login.tpl.php');
	}

	public function hookLoginForm()
	{
		$providers = $GLOBALS['HtmlHelper']->listProviders('connectAny');

		if(false === $providers)
			return;

		_e('<h2>Or log in using a social network!</h2>', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		echo $providers;
	}

	public function hookRegisterForm()
	{
		$providers = $GLOBALS['HtmlHelper']->listProviders('connectAny');

		if (false === $providers)
			return;

		// always hide original 'A password will be e-mailed to you.' message, we show a similar message in the proper location if necessary
		print '<style type="text/css">#reg_passmail {display:none;}</style>';

		// do not show providers if plugin already used to select a provider
		if (empty($_GET['reactsocialanalyticsprovider']))
		{
			echo '<p id="reg_passmail2">';
			_e('A password will be e-mailed to you.');
			echo '</p><br />';

			_e('<h2>Or register using a social network!</h2>', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
			echo $providers;
		}

		// suggest name
		if (isset($_GET['reactsocialanalyticsusername']))
		{
			$nameSuggestion = htmlentities($_GET['reactsocialanalyticsusername']);

			print '<script type="text/javascript">';
			print 'if (typeof(jQuery) !== "undefined") { jQuery(document).ready(function() {jQuery("#user_login").attr("value", "' . $nameSuggestion. '");}); }';
			print '</script>';
		}
	}

	public function hookPluginActivation()
	{
		if (get_option('reactOAuthServiceEndpoint') === false)
		{
			// First activation
			add_option('reactOAuthServiceEndpoint', REACT_SOCIAL_ANALYTICS_DEFAULT_OAUTHSERVICE_URL);
			add_option('reactLikeServiceEndpoint', REACT_SOCIAL_ANALYTICS_DEFAULT_LIKESERVICE_URL);
			add_option('reactShareServiceEndpoint', REACT_SOCIAL_ANALYTICS_DEFAULT_SHARESERVICE_URL);
			add_option('reactLikeServiceEnabled', 0);
			add_option('reactShareServiceEnabled', 1);
			add_option('reactShareDefaultMessage', 'Look what I found!');
			add_option('reactProviderOrder', array('Facebook', 'Twitter', 'Google', 'LinkedIn'));
		}
		add_option('reactRedirectAfterActivate', true);
	}

	public function handleRequestTokenAction()
	{
		$this->_pendingTokenRequest = true;
		$data = OpenReact_SocialConnector::magicQuotesUndo($_GET);

		if (empty($data['provider']))
			throw new OpenReact_SocialConnector_Wordpress_ProviderRequiredException('No provider specified');

		// client will redirect
		if(isset($_GET['shareConnect']))
			$this->storeReturnUrl(site_url('?action=shareconnect&provider=' . $data['provider']));
		else
			$this->storeReturnUrl($this->getReferer());

		try
		{
			$defaultUrl = $this->getDefaultUrl();

			// If the default URL is a host-only (no path), then add a '/' because facebook does not allow redirects without a path
			$urlData = parse_url($defaultUrl);
			if (!isset($urlData['path']) || $urlData['path'] = '')
				$defaultUrl = $defaultUrl . '/';

			$result = $this->_client->OAuthServer->tokenRequest($data['provider'], $defaultUrl . '?action=accessToken');
		}
		catch (Exception $e)
		{
			$this->storeSocialError($e);
			$this->redirectUser($this->getReturnUrl(true));
			exit;
		}

		$this->redirectUser($result['redirectUrl']);
	}

	/**
	 This method is called during init, to handle the access token regardless of the specified action
	 Some providers like to specify an action parameter as well, which would break stuff
	 */
	public function handleAccessTokenAction()
	{
		$this->_pendingTokenRequest = false;

		try
		{
			$result = $this->_client->OAuthServer->tokenAccess($this->getRequestData());
		}
		catch (Exception $e)
		{
			$this->storeSocialError($e);

			 // Use default url, other urls may trigger 'action'->...->'token access exception'->'action'->...->'token access exception' loops
			if (strpos($this->getReturnUrl(), 'action=accessToken') === false)
				$this->redirectUser($this->getReturnUrl());
			else
				$this->redirectUser($this->getDefaultUrl());

			exit();
		}

		try
		{
			$returnUrl = $this->getReturnUrl();

			if (empty($result['connectedWithProvider']))
				throw new OpenReact_SocialConnector_Wordpress_AuthenticationFailedException('External authentication failed.');

			if (!empty($result['applicationUserId']))
			{
				if (!$this->isUserLoggedIn())
					$this->logUserIn($result['applicationUserId']);
				else if ($this->getUserId() != $result['applicationUserId'])
				{
					$e = new OpenReact_SocialConnector_Wordpress_AccountConnectedToDifferentUserException('That %s account is already in use by another user. Please log out if you wish to log in with that user.', array($result['connectedWithProvider']));

					$this->storeSocialError($e, true);
					$this->redirectUser($this->getReturnUrl(true));
					exit;
				}
			}
			else
			{
				// Access allowed, but user unknown at the server
				if (!$this->isUserLoggedIn())
				{
					return $this->registerUser($result['connectedWithProvider'], $result['reactOAuthSession']);
				}

				// React user known, store id at server
				$this->_client->OAuthServer->tokenSetUserId($this->getUserId(), $result['reactOAuthSession']);
			}

			// The result of the pending action is lost, but that doesn't matter since the callee shouldn't expect a result
			$this->pendingSocialAction();

			$this->unsetReturnUrl();

			return $this->redirectUser($returnUrl);
		}
		catch (Exception $e)
		{
			if (strpos(get_class($e), 'OpenReact_SocialConnector_Wordpress_User_') === 0)
				$this->storeSocialError($e, true);
			else
				$this->storeSocialError($e);
			$this->redirectUser($this->getReturnUrl(true));
			exit;
		}
	}

	public function handleDisconnectAction()
	{
		$data = OpenReact_SocialConnector::magicQuotesUndo($_POST);
		try
		{
			$this->_client->OAuthServer->userRemoveProvider($this->getUserId(), $data['provider']);
		}
		catch (Exception $e)
		{
			$this->storeSocialError($e);
			$this->redirectUser($this->getReferer());
			exit;
		}
		$this->redirectUser($this->getReferer());
	}

	public function getShareTemplateTag($url, $title = '', $imgUrl = '', $comment = '', $echo = true)
	{
		if(!$this->_config['reactShareServiceEnabled'])
			return;

		$share_arguments = array('url' => $url, 'title' => $title, 'img_url' => $imgUrl, 'comment' => $comment);

		$url = site_url('?action=share');
		foreach ($share_arguments as $k => $v)
		{
			$url .= '&' . $k . '=' .  urlencode($v);
		}

		// Available in share-tag.tpl.php
		$shareProviders = $GLOBALS['HtmlHelper']->listProviders('share', array('url' => $url));

		if(!$echo)
		{
			ob_start();
			require (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/share-tag.tpl.php');
			return ob_get_clean();
		}

		require (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/share-tag.tpl.php');
	}

	/**
	 * Share the message with all specified providers
	 * Please note that $title and $imgUrl only work if they're all provided
	 */
	public function handleShareAction()
	{
		if(!$this->_config['reactShareServiceEnabled'])
			return;

		$data = $this->_getShareData();
		$userProviders = $this->getUserProviders($this->getUserId());

		if ('GET' == $_SERVER['REQUEST_METHOD'])
		{
			require_once (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/share.tpl.php');
		}
		else if($this->_shareDataIsValid() === true)
		{
			try
			{
				$this->_share($_POST['providers'], $_POST['message'], $_POST['url'],
					$_POST['title'], $_POST['img_url']);
				exit('success');
			}
			catch(Exception $e)
			{
				exit($e->getMessage());
			}
		}
		else
			exit($this->_getShareDataError());

		exit();
	}

	public function handleShareconnectAction()
	{
		require REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/share-connect.tpl.php';
		exit();
	}

	private function _getShareData()
	{
		static $data = array();

		if(empty($data))
		{
			$data['providers'] = array();

			foreach(array('url', 'title', 'img_url', 'comment') as $k)
				$data[$k] = isset($_REQUEST[$k])? urldecode((string)$_REQUEST[$k]) : '';

			if(!empty($_POST['providers']) && is_array($_POST['providers']))
				$data['providers'] = $_POST['providers'];

			$data = OpenReact_SocialConnector::magicQuotesUndo($data);
		}

		return $data;
	}

	private function _shareDataIsValid()
	{
		return isset($_POST['message'], $_POST['url'], $_POST['title'],
			$_POST['img_url'], $_POST['_wpnonce'])
			&& $_POST['message'] != ''
			&& !ctype_space($_POST['message'])
			&& isset($_POST['providers'])
			&& is_array($_POST['providers'])
			&& count($_POST['providers']) > 0
			&& wp_verify_nonce($_POST['_wpnonce'], 'react-social-analytics-share');
	}

	private function _getShareDataError()
	{
		if(empty($_POST['message']) || ctype_space($_POST['message']))
			return __('Please enter a message.');

		if(empty($_POST['providers']))
			return __('Please pick at least one social network provider to share on.');

		return __('Could not send your message at this time. Try again later.');
	}

	private function _share($providers, $message, $url, $title = '', $imgUrl = '')
	{
		$title = ($title == '' || ctype_space($title))? null : $title;
		$imgUrl = ($imgUrl == '' || ctype_space($imgUrl))? null : $imgUrl;

		try
		{
			$message = $this->_client->BitLy->shortenUrlsInText($message, null);
		}
		catch(Exception $e) {} // Ignore bitly errors. Service could be down or over limit

		try
		{
			$result = $this->_client->Share->postMessage(
				$this->getUserId(), $message, $providers, $url, $imgUrl, $title);
		}
		catch (Exception $e)
		{
			throw $e;
		}

		return $result;
	}

	public function handleLogoutAction()
	{
		check_admin_referer('log-out');
		wp_logout();
		session_destroy();

		$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : $this->getDefaultUrl();
		wp_safe_redirect( $redirect_to );
		exit();
	}

	public function hookAdminMenu()
	{
		add_options_page('React Social Analytics', 'React Social Analytics', 'administrator', self::MENU_SLUG, array($this, 'showAdminPage'));
	}

	public function hookAdminPluginLinks($links, $pluginName)
	{
		if ($pluginName === REACT_SOCIAL_PLUGIN_NAME)
		{
			$links[] = '<a href="options-general.php?page=reactSocialAnalyticsOptions">'.__("Settings", REACT_SOCIAL_ANALYTICS_TEXTDOMAIN).'</a>';
		}
		return $links;
 	}

	public function showAdminPage()
	{
		// This variable will be read from admin.tpl.php
		$admin_save_result = $cacheConfig = null;

		if(file_exists('W3TC_CONFIG_PATH'))
			$cacheConfig = include('W3TC_CONFIG_PATH');

		if ('POST' == $_SERVER['REQUEST_METHOD'])
		{
			$data = OpenReact_SocialConnector::magicQuotesUndo($_POST);
			$admin_save_result = $this->_adminMenuSave($data['react']);
		}

		require (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/admin.tpl.php');
	}

	protected function _adminMenuSave($settings)
	{
		try
		{
			if (isset($settings['reactOAuthServiceEndpoint']))
				$this->_validateServiceUrl($settings['reactOAuthServiceEndpoint']);
			if (isset($settings['reactLikeServiceEndpoint']))
				$this->_validateServiceUrl($settings['reactLikeServiceEndpoint']);
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		foreach (array_keys($this->_config) as $configKey)
			if (isset($settings[$configKey]))
				update_option($configKey, $settings[$configKey]);

		// Reset settings, reinit client, try calling all services
		$this->_initSettings();
		$this->_client = null;
		$this->_initClient();

		$serviceErrors = array();

		try
		{
			$this->_client->OAuthServer->getProviders();
		}
		catch (Exception $e)
		{
			$serviceErrors[] = __('Could not connect to the OAuthService, please verify your settings.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
			$serviceErrors[] = __('OAuthService technical message: ', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) . $e->getMessage();
		}

		if ($this->_config['reactLikeServiceEnabled'])
		{
			try
			{
				$this->_client->Like->get('', '');
			}
			catch (Exception $e)
			{
				if (!$this->_isLikeNotYetRatedException($e))
				{
					$serviceErrors[] = __('Could not connect to the LikeService, please verify your settings.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
					update_option('reactLikeServiceEnabled', 0);
					$serviceErrors[] = __('LikeService technical message: ', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) . $e->getMessage();
				}
			}
		}

		if ($this->_config['reactShareServiceEnabled'])
		{
			try
			{
				$this->_client->Share->getSupportedProviders();
			}
			catch (Exception $e)
			{
				$serviceErrors[] = __('Could not connect to the ShareService, please verify your settings.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
				update_option('reactShareServiceEnabled', 0);
				$serviceErrors[] = __('ShareService technical message: ', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) . $e->getMessage();
			}
		}

		if (count($serviceErrors))
			return $serviceErrors;

		return true;
	}

	protected function _validateServiceUrl($url)
	{
		$parts = parse_url($url);

		if (!isset($parts['host']))
			throw new Exception('Incomplete url: '.$url);

		$host = substr($parts['host'], strrpos($parts['host'], '.react.'));

		if (!in_array($host, array('.react.com', '.react.nl')))
			throw new Exception('Invalid host name: '.$url);

		return true;
	}

	public function hookLostPasswordForm()
	{
		if(empty($_GET['email']))
			return;

		$userEmail = $_GET['email'];
		echo '<script type="text/javascript">document.getElementById("user_login").value = "' . $userEmail . '";</script>';
	}

	public function hookDeletedUser($userId)
	{
		$this->removeAllUserProviders($userId);
	}

	public function hookShowUserProfile($user)
	{
		$providers = $this->getUserProviders($user->id);
		require(REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/profile_providers.tpl.php');
	}

	public function hookEditUserProfileUpdate($userId)
	{
		$removeProviders = !empty($_POST['react-social-analytics']) ? $_POST['react-social-analytics'] : array();
		$removeProviders = array_keys($removeProviders);

		if (count($removeProviders))
		{
			try
			{
				$this->removeUserProviders($userId, $removeProviders);
			}
			catch(Exception $e)
			{
				// FIXME: provide feedback
				//throw $e;
			}
		}

	}

	public function getProviders()
	{
		$providers = $this->_cache->get('providers');

		if (!is_array($providers))
		{
			$providers = $this->_client->OAuthServer->getProviders();
			$this->_cache->set('providers', $providers,  OpenReact_Wordpress_Cache::TTL_DAY * 7);
		}

		return $providers;
	}

	/**
	 * Get the providers associated with the currently logged in user, or the user specified by $userId
	 */
	public function getUserProviders($userId = null)
	{
		if (!$userId)
			$userId = $this->getUserId();

		if (!$userId)
			return array();

		$providers = $this->_cache->get($this->_getUserProvidersCacheKey($userId));

		if (!is_array($providers))
		{
			$result = $this->_client->OAuthServer->userGetProviders($userId);
			$providers = array();

			if (isset($result['connectedWithProviders']))
				$providers = $result['connectedWithProviders'];

			$this->_cache->set($this->_getUserProvidersCacheKey($userId), $providers, OpenReact_Wordpress_Cache::TTL_DAY * 7);
		}

		return $providers;
	}

	public function removeUserProviders($userId, $providers)
	{
		$this->_cache->delete($this->_getUserProvidersCacheKey($userId));

		foreach ($providers as $provider)
		{
			$this->_client->OAuthServer->userRemoveProvider($userId, $provider);
		}
	}

	public function removeAllUserProviders($userId)
	{
		$providers = $this->getUserProviders($userId);
		$this->removeUserProviders($userId, $providers);
	}

	private function _getUserProvidersCacheKey($userId)
	{
		return 'userProviders' . $userId;
	}

	public function handleLikePost()
	{
		if(!$this->_config['reactLikeServiceEnabled']
			|| $_SERVER['REQUEST_METHOD'] != 'POST'
			|| !isset($_POST['reactSocialAnalyticsLikePost'],
					$_POST['category'],
					$_POST['resourceUri'])
			|| !wp_verify_nonce($_REQUEST['_wpnonce'], 'react-social-analytics-like-nonce'))
				return;

			try
			{
				$likeUserId = $this->getUserId();

				if (!$likeUserId)
					return;

				$this->_client->Like->set($_POST['category'], $_POST['resourceUri'], $likeUserId, 1);
			}
			catch(Exception $e)
			{
				$msg = sprintf(
					__('Sorry, could not vote because of the following error:<br /> %s',
						REACT_SOCIAL_ANALYTICS_TEXTDOMAIN),
					$e->getMessage());

				if($this->_isAjaxPost())
					exit($msg);

				return $msg;
			}

			if($this->_isAjaxPost())
				exit('success');
	}

	public function getLikeTemplateTag($category, $resourceUri, $echo = true)
	{
		if(!$this->_config['reactLikeServiceEnabled'])
			return;

		$likeUserId = $this->getUserId();
		$rating = $this->_getLikeRating($category, $resourceUri, $likeUserId);
		$hasRated = $count = $countOthers = 0;

		if($rating !== false)
		{
			$hasRated = isset($rating['userRating']['rating']);
			$count = isset($rating['aggregates']['countPositive']) ? $rating['aggregates']['countPositive'] : 0;
			$countOthers = $hasRated ? $count - 1 : $count;
		}

		if(!$echo)
		{
			ob_start();
			require (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/like.tpl.php');
			return ob_get_clean();
		}

		require (REACT_SOCIAL_ANALYTICS_APPLICATION_PATH . '/templates/like.tpl.php');
	}

	private function _getLikeRating($category, $resourceUri, $likeUserId)
	{
		try
		{
			return $this->_client->Like->get($category, $resourceUri, $likeUserId);
		}
		catch (Exception $e)
		{
			if (!$this->_isLikeNotYetRatedException($e))
			{
				$this->setSocialErrorMessage($e);
			}
		}

		return false;
	}

	private function _isLikeNotYetRatedException($e)
	{
		return $e->getCode() == 30701;
	}

	public function setSocialErrorMessage($e, $useExceptionMessage = false)
	{
		if ($e instanceof Exception)
		{
			if ($useExceptionMessage || WP_DEBUG)
				$GLOBALS['reactSocialAnalyticsSocialError'] = $e->getMessage();
			else
				$GLOBALS['reactSocialAnalyticsSocialError'] = __('Sorry, something went wrong while processing your social network request.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		}
		else
		{
			$GLOBALS['reactSocialAnalyticsSocialError'] = $e;
		}

		return $GLOBALS['reactSocialAnalyticsSocialError'];
	}

	public function getSocialError()
	{
		if (isset($GLOBALS['reactSocialAnalyticsSocialError']))
			return $GLOBALS['reactSocialAnalyticsSocialError'];

		if ($socialError = $this->_getSessionData(self::SOCIAL_ERROR))
		{
			if (!isset($socialError['message']))
				$socialError['message'] = __('Sorry, something went wrong while processing your social network request.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);;

			if (WP_DEBUG)
			{
				$this->setSocialErrorMessage($socialError['class'] .': '. (isset($socialError['exceptionMessage']) ? $socialError['exceptionMessage'] : $socialError['message']));
			}
			else
			{
				$this->setSocialErrorMessage($socialError['message']);
			}
			$this->_removeSessionData(self::SOCIAL_ERROR);

			return $socialError['message'];
		}

		return false;
	}

	public function storeSocialError($e, $useExceptionMessage = false)
	{
		if (is_object($e) && $e instanceof Exception)
		{
			$error = array(
				'class' => get_class($e),
				'message' => $useExceptionMessage ? $e->getMessage() : null,
				'exceptionMessage' => $e->getMessage(),
				'type' => 'social_exception',
			);
		}
		else if (is_array($e))
		{
			$error = array(
				'class' => isset($e['class']) ? $e['class'] : null,
				'message' => isset($e['message']) ? $e['message'] : 'unknown message',
				'type' => 'social_error'
			);
		}
		else
		{
			$error = array(
				'class' => null,
				'message' => $e,
				'type' => 'social_error'
			);
		}

		$this->_storeSessionData(self::SOCIAL_ERROR, $error);
	}

	public function getShareProviders()
	{
		try
		{
			return $this->_client->Share->getSupportedProviders();
		}
		catch (Exception $e)
		{
			return array();
		}
	}

	public function hookAdminInit()
	{
		if (!get_option('users_can_register'))
		{
			$this->_adminErrors[] = __('<strong>React Social Analytics:</strong> User registration is disabled in your wordpress install, please <a href="'. admin_url('options-general.php') .'">enable it here</a> (toggle \'Membership\' option), or the React Social Analytics plugin cannot work!', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		}

		if (empty($this->_config['reactApplicationKey']) || empty($this->_config['reactApplicationSecret']))
		{
			if ($GLOBALS['plugin_page'] != self::MENU_SLUG)
			{
				$this->_adminErrors[] = sprintf(__(
					'<strong>React Social Analytics:</strong> Almost done! Complete your configuration by entering your application key and secret at <a href="%s">the plugin settings page</a>.',
					REACT_SOCIAL_ANALYTICS_TEXTDOMAIN),
					admin_url() . 'options-general.php?page=' . self::MENU_SLUG
				);
			}
			else
			{
				if (empty($_REQUEST['react']) || empty($_REQUEST['react']['reactApplicationKey']) || empty($_REQUEST['react']['reactApplicationKey']))
					$this->_adminErrors[] = sprintf(
						__('<strong>React Social Analytics:</strong> Almost done! Complete your configuration by entering your application key and secret, which you can obtain at <a href="%s" target="_blank">account.react.com</a>.', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN),
						REACT_SOCIAL_ANALYTICS_ACCOUNT_URL
					);
			}
		}

		if (get_option('reactRedirectAfterActivate', false))
		{
			delete_option('reactRedirectAfterActivate');
			wp_redirect(admin_url('options-general.php?page=reactSocialAnalyticsOptions'));
		}
	}

	public function hookAdminNotices()
	{
		if (!empty($this->_adminNotices))
		{
			foreach ($this->_adminNotices as $notice)
				echo '<div class="updated"><p>' . $notice . '</p></div>';
		}
		if (!empty($this->_adminErrors))
		{
			foreach ($this->_adminErrors as $error)
				echo '<div class="error"><p>' . $error . '</p></div>';
		}
	}

	public function connectionTest()
	{
		// Cannot test without credentials
		if (empty($this->_config['reactApplicationKey']) || empty($this->_config['reactApplicationSecret']))
			return false;

		try
		{
			$this->_client->OAuthServer->getProviders();
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	public function hookUserRegister($userId)
	{
		$user = get_userdata($userId);

		if(false !== $user)
		{
			// Log in after after social registration and entering email.
			if($this->_oauthUserSessionCheck($user->ID, $user->user_email))
				$this->logUserIn($user->ID);
		}
	}

	public function hookTheContent($content)
	{
		if(!is_single())
			return $content;

		global $post;

		$image = '';

		if(function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID))
		{
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID ));
			$image = $image[0];
		}

		$resourceUri = $post->ID;
		$category = $post->post_type;
		$like = $this->getLikeTemplateTag($category, $resourceUri, false);
		$share = $this->getShareTemplateTag(
			get_permalink(), get_the_title(), $image,
			__($this->_config['reactShareDefaultMessage'], REACT_SOCIAL_ANALYTICS_TEXTDOMAIN),
			false);

		return $content . '<div class="react-social-analytics-links">' . $like . $share . '</div>';
	}

	private function _isAjaxPost()
	{
		return !empty($_POST['reactSocialAjaxPost']);
	}
}
