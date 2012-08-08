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

	Copyright (c) 2012 React B.V. (http://www.react.com)
*/
class OpenReact_SocialConnector_Helper_Html
{
	/**
	 * Prints list of providers
	 *
	 * @param array $providers Array with available providers 'providers' and connected providers 'userProviders'
	 * @param string $type Prints list or form based on type (radio, checkbox, disconnect, list)
	 * @return HTML fragment
	 */
	public function listProviders($type = 'list', $options = null)
	{
		try
		{
			$providers = get_react_social_analytics_plugin()->getProviders();
			$userProviders = get_react_social_analytics_plugin()->getUserProviders();
		}
		catch (OpenReact_XmlRpc_Client_ResponseFaultException $e)
		{
			return false;
		}

		$order = get_option('reactProviderOrder');
		if (is_array($order))
			$providers = array_merge(array_intersect($order, $providers), array_diff($providers, $order));

		$output = '';
		foreach($providers as $provider)
		{
			$connected = in_array($provider, $userProviders);
			$provider = htmlspecialchars($provider);

			switch($type)
			{
				case 'radio':
					$fragment = self::_listProvider($provider, $connected, $type);
					break;
				case 'checkbox':
					$fragment = self::_listProvider($provider, $connected, $type);
					break;
				case 'disconnect':
					if($connected)
						$fragment = self::_listProvider($provider, $connected, $type);
					else
						$fragment = self::_listProvider($provider, $connected, 'list');
					break;
				case 'connect':
					$fragment = self::_listProvider($provider, $connected, $type);
					break;
				case 'connectAny':
					$fragment = self::_listProvider($provider, array(), $type);
					break;
				case 'share':
					if(isset($options['url']))
						$url = $options['url'];
					else
						$url = get_react_social_analytics_plugin()->getCurrentUrl();

					$fragment = self::_listProvider($provider, $connected, 'share', $url);
					break;
				case 'list':
					$fragment = self::_listProvider($provider, $connected, 'list');
					break;
				default:
					throw new Exception('Invalid type parameter');
			}

			$output .= $fragment;
		}

		return '<ul class="react-social-providers ' . $type . '">' . $output . '</ul>';
	}

	protected static function _listProvider($provider, $connected, $type, $link = null)
	{
		$provider = htmlspecialchars(trim($provider));
		$providerName = $provider;
		$iconClassName = 'react-social-analytics-network-icon';
		$parentClassName =  strtolower($provider);

		if($connected)
		{
			$parentClassName .= ' connected';
		}

		switch($type)
		{
			case 'radio':
			case 'checkbox':
				$html = <<< EOT
					<input type="{$type}" name="provider" id="provider_{$provider}"
						value="{$provider}" style="float: left; margin-right: 5px;" />
					<label for="provider_{$provider}">
						<span class="{$iconClassName}">{$providerName}</span>
					</label>
EOT;
			break;
			case 'connectAny':
			case 'connect':
				if ($connected)
					$html = '<span class="' . $iconClassName . '" title="'
						. __('You are connected with', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN)
						. ' '. $provider .'">' . $provider . '</span>';
				else
					$html = '<a href="'. get_option('siteurl') .'?action=requestToken&amp;provider='
						. $provider .'" title="' . sprintf(__('Connect %s', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), $provider)
						. '" class="' . $iconClassName . '"><span>' . $provider . '</span></a>';
			break;
			case 'share':
				if (is_null($link))
					$link = get_option('siteurl');

				$shareLink = $link . (strpos($link, '?') === false ? '?' : '&amp;') . 'provider='. $provider;
				$parentClassName .= ' small';

				if ($connected)
					$title = sprintf(__('Share this using %s', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), $provider);
				else
					$title = sprintf(__('Connect to %s and share this', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN), $provider);

				$html = '<a href="'. $shareLink .'" title="' . htmlspecialchars($title) . '" class="react-social-share ' . $iconClassName . '"> </a>';
			break;
			case 'list':
				$html = $providerName;
			break;
			default:
				throw new Exception('Invalid type parameter');
		}

		return '<li class="' . $parentClassName . '">' . $html . '</li>';
	}

	public function doctype()
	{
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	}

	public function head($title)
	{
?>
	<head>
		<title><?php bloginfo('name'); ?> &rsaquo; <?php
			_e('Connected', REACT_SOCIAL_ANALYTICS_TEXTDOMAIN); ?></title>
		<meta http-equiv="Content-Type"
			content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<script type="text/javascript"
			src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	</head>
<?php
	}
}
