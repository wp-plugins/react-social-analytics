<?php
// (C) Copyright React B.V. <http://www.react.com>

/*
Plugin Name:	React Social Analytics
Plugin URI:		http://react.com/
Description:	Plugin to integrate social networks into Wordpress, making your blog more personal and social. As a bonus, you get more insight in who visits your site.
Version:		1.0.3.2
Author:			React.com
Author URI:		http://react.com/
License:		Simplified BSD License
*/

define('REACT_SOCIAL_ANALYTICS_VERSION', '1.0.3.2');
define('REACT_SOCIAL_PLUGIN_ROOT_FOLDER_PATH', dirname(__FILE__));
define('REACT_SOCIAL_PLUGIN_ROOT_FOLDER_NAME', array_pop(explode(DIRECTORY_SEPARATOR, dirname(__FILE__))));
define('REACT_SOCIAL_PLUGIN_NAME', REACT_SOCIAL_PLUGIN_ROOT_FOLDER_NAME . DIRECTORY_SEPARATOR . basename(__FILE__));

require REACT_SOCIAL_PLUGIN_ROOT_FOLDER_PATH . '/React_SocialAnalyticsPluginInit.php';