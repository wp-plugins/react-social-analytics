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
class OpenReact_SocialConnector
{
	public static function magicQuotesUndo($array)
	{
		if ((!function_exists('get_magic_quotes_gpc') || !get_magic_quotes_gpc()) && !defined('SOCIALCONNECTOR_FORCE_STRIPSLASHES'))
			return $array;

		if (!is_array($array))
			return stripslashes($array);

		foreach ($array as $k => $v)
			$array[self::magicQuotesUndo($k)] = self::magicQuotesUndo($v);

		return $array;
	}
}