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
/**
 * Simple widget displaying the social connect icons
 */
class OpenReact_SocialConnector_ProviderWidget extends WP_Widget
{
	public function __construct()
	{
		$name = __('React Social Analytics Login', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		$description = __('Display social network connections list', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN);
		parent::__construct(false, $name, array('description' => $description));
	}

	public function widget($args, $settings)
	{
		$title = $settings['title'];
		if (empty($title))
		{
			$title = $this->_getDefaultName();
		}

		print $args['before_widget'];
		print $args['before_title'] . $title . $args['after_title'];
		if (get_react_social_analytics_plugin()->getSocialError())
		{
			print '<div class="error social-error message">'. htmlspecialchars(get_react_social_analytics_plugin()->getSocialError()) .'</div>';
		}
		print get_react_social_html_helper()->listProviders('connect');
		print $args['after_widget'];
	}

	public function form($settings)
	{
		$id = $this->get_field_id('title');
		$name = $this->get_field_name('title');

		print '<label for="' . htmlspecialchars($id) . '">' . __('Title:', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN) . '</label>';
		print '<input id="' . htmlspecialchars($id)  .'" name="' . htmlspecialchars($name) . '" type="text" value=" ' . htmlspecialchars($settings['title']) . ' " style="width: 100%;" />';
	}

	public function update($new_instance, $old_instance)
	{
		$old_instance['title'] = trim(strip_tags($new_instance['title']));
		if (empty($old_instance['title']))
		{
			$old_instance['title'] = $this->_getDefaultName();
		}

		return $old_instance;
	}

	private function _getDefaultName()
	{
		return __('Connect with');
	}
}
