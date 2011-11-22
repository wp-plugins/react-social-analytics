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
class OpenReact_SocialConnector_Wordpress_User
{
	const SOCIAL_USER_DEFAULT_NAME = 'SocialUser';
	const MAX_NAME_TRIES = 5;
	const NAME_POSTFIX_LENGTH = 8;

	public function __construct()
	{
	}

	/* Register a new user
	 * Logic inspired by register_new_user in wp-login
	 */
	public function create($socialProfile)
	{
		$nickName = !empty($socialProfile['user_name']) ? $socialProfile['user_name'] : $socialProfile['real_name'];
		$nickName = substr(trim($nickName), 0, 50);
		$realName = !empty($socialProfile['real_name']) ? $socialProfile['real_name'] : $nickName;

		if (empty($nickName))
			$nickName = SOCIAL_USER_DEFAULT_NAME;

		$email = !empty($socialProfile['email']) ? trim($socialProfile['email']) : null;

		$login = $this->_getUniqueLogin($nickName);
		$email = $this->_checkEmail($email);

		$data = array(
			'user_login' => $login,
			'user_email' => $email,
			'user_pass'  => wp_generate_password(),
			'nickname'   => $nickName,
			'display_name' => $realName,
			'user_url'     => '',
		);

		$userId = wp_insert_user($data);

		if ($userId instanceof WP_Error)
		{
			if ($userId->get_error_messages('existing_user_email'))
				throw new OpenReact_SocialConnector_Wordpress_User_ExistingEmailException('Email `%s` is already taken.', array($email));

			throw new OpenReact_SocialConnector_Wordpress_User_CreateException('Failed to create Wordpress user.');
		}

		wp_new_user_notification($userId, $data['user_pass']);
		return $userId;
	}

	private function _checkUserName($name)
	{
		$name = sanitize_user($name);

		if ($name == '' || !validate_username($name))
			throw new OpenReact_SocialConnector_Wordpress_User_NameInvalidException('Username %s is invalid.', array($name));

		if (username_exists($name))
			throw new OpenReact_SocialConnector_Wordpress_User_NameExistsException('Username %s is already taken', array($name));

		return $name;
	}

	private function _checkEmail($email)
	{
		$email = apply_filters('user_registration_email', $email);

		if (!is_email($email))
			throw new OpenReact_SocialConnector_Wordpress_User_EmailInvalidException('Email `%s` is not a valid email address.', array($email));

		if (email_exists($email))
			throw new OpenReact_SocialConnector_Wordpress_User_ExistingEmailException('Email `%s` is already taken.', array($email));

		return $email;
	}

	/* Returns $login, with optional numeric suffix to provide uniqueness
	 */
	private function _getUniqueLogin($login)
	{
		$baseName = $login = substr($login, 0, 50);

		for ($tries = 0; $tries < self::MAX_NAME_TRIES; $tries++)
		{
			try
			{
				return $this->_checkUserName($login);
			}
			catch (OpenReact_SocialConnector_Wordpress_User_NameExistsException $e)
			{
				// try next name
				$login = $baseName . '_' . substr(md5(mt_rand()), 0, self::NAME_POSTFIX_LENGTH);
			}
		}

		throw new OpenReact_SocialConnector_Wordpress_User_NameException('Failed to find a unique login name.');
	}

	public function getCurrentWpUserId()
	{
		return wp_get_current_user()->ID;
	}

	public function setLoggedIn($wpUserId)
	{
		$userdata = get_userdata($wpUserId);

		if ($userdata === false)
			throw new OpenReact_SocialConnector_Wordpress_User_NotFoundException('Wordpress user ID `%s` not found.', array($wpUserId));

		wp_set_auth_cookie($wpUserId, true, false);
		wp_set_current_user($wpUserId);

		if ($this->getCurrentWpUserId() === 0)
		{
			throw new OpenReact_SocialConnector_Wordpress_User_NotFoundException('Wordpress user ID `%s` not found.', array($wpUserId));
		}
	}
}
