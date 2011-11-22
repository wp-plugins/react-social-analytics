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

require_once (dirname(__FILE__) . '/config/config.php');

// Start session to support storing return urls etc. in socialconnector
session_start();

define('REACT_SOCIAL_ANALYTICS_APPLICATION_PATH', dirname(__FILE__));

define('REACT_SOCIAL_ANALYTICS_APPLICATION_URL', WP_PLUGIN_URL . '/' . REACT_SOCIAL_PLUGIN_ROOT_FOLDER_NAME . $config['urlSuffix']);
define('REACT_SOCIAL_ANALYTICS_DEFAULT_OAUTHSERVICE_URL', 'https://social.react.com/XmlRpc_v2');
define('REACT_SOCIAL_ANALYTICS_DEFAULT_SHARESERVICE_URL', 'https://share.react.com/XmlRpc_v2');
define('REACT_SOCIAL_ANALYTICS_DEFAULT_LIKESERVICE_URL', 'https://like.react.com/XmlRpc_v2');
define('REACT_SOCIAL_ANALYTICS_ACCOUNT_URL', 'https://account.react.com/');
define('REACT_SOCIAL_ANALYTICS_TEXTDOMAIN', 'React_SocialAnalyticsPlugin');

// Traverse all directories in the directory of entry; this enables support for symlinked plugins
$wpPath = dirname($_SERVER['SCRIPT_FILENAME']);
while (!file_exists($wpPath . DIRECTORY_SEPARATOR . 'wp-load.php') && (DIRECTORY_SEPARATOR != $wpPath))
	$wpPath = dirname($wpPath);

require_once ($wpPath . DIRECTORY_SEPARATOR . 'wp-load.php');

// Enable OpenReact autoloader
require_once ($config['autoloaderClassFile']);
OpenReact_Autoload::register($config['autoloaderLibrary']);

// HtmlHelper for global use
$HtmlHelper = new OpenReact_SocialConnector_Helper_Html();
// Construct plugin, which will add all the Wordpress hooks
$React_SocialAnalyticsPlugin = new OpenReact_SocialConnector_Wordpress();

function get_react_social_analytics_plugin()
{
	return $GLOBALS['React_SocialAnalyticsPlugin'];
}

function get_react_social_html_helper()
{
	return $GLOBALS['HtmlHelper'];
}

/**
 * Template tag to display social network Share functionality
 */
function react_social_analytics_share($url, $title = '', $img_url = '', $comment = '')
{
	get_react_social_analytics_plugin()->getShareTemplateTag($url, $title, $img_url, $comment);
}

/**
 * Template tag to display the the amount of upvotes and a upvote button using React Like Service.
 *
 * @param (string) $category Category of the resource to like
 * @param (string) $resourceUri Uri/id of the resource to like
 */
function react_social_analytics_like($category, $resourceUri)
{
	get_react_social_analytics_plugin()->getLikeTemplateTag($category, $resourceUri);
}
