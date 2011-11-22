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
abstract class OpenReact_SocialConnector_ApplicationAbstract
{
	protected $_client;
	protected $_returnUrl;

	/**
		Returns:
			(array) with (array) 'endpoints' ('http://endpoint' => array('Controller', ..)), (string) 'applicationKey' and (string) 'applicationSecret' keys
	*/
	abstract public function getSettings();

	abstract public function logUserIn($userId);

	/*
	 * Return userId or (int) 0 when empty
	 */
	abstract public function getUserId();

	abstract public function registerUser($provider, $oauthSession);

	abstract public function getDefaultUrl();

	abstract public function getApplicationName();

	public function isUserLoggedIn()
	{
		return ($this->getUserId() > 0);
	}

	public function storeReturnUrl($value)
	{
		return $this->_storeSessionData('returnUrl', $value);
	}

	public function unsetReturnUrl()
	{
		return $this->_storeSessionData('returnUrl', null);
	}

	public function getReturnUrl()
	{
		return $this->_getSessionData('returnUrl');
	}

	public function storePendingSocialAction($value)
	{
		return $this->_storeSessionData('socialAction', $value);
	}

	public function getPendingSocialAction()
	{
		return $this->_getSessionData('socialAction');
	}

	public function removePendingSocialAction()
	{
		return $this->_removeSessionData('socialAction');
	}

	public function hasPendingSocialAction()
	{
		return (null !== $this->getPendingSocialAction());
	}

	public function getRequestData()
	{
		return OpenReact_SocialConnector::magicQuotesUndo($_REQUEST);
	}

	public function getReferer()
	{
		return $this->getReferrer();
	}

	public function getReferrer()
	{
		// 1 'R' => epic typo in HTTP RFC
		$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

		if (false === strpos($referrer, 'http://' . $_SERVER['HTTP_HOST']))
			return $this->getDefaultUrl();

		return $referrer;
	}

	public function redirectUser($url)
	{
		if (!headers_sent())
			header('Location: ' . $url);

		print ('<script type="text/javascript">window.location.href="' . htmlspecialchars($url) . '"</script><noscript>Please continue <a href="' . htmlspecialchars($url) . '">here</a></noscript>');

		die;
	}

	public function getCurrentUrl()
	{
		$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http');

		return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	protected function _storeSessionData($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	protected function _hasSessionData($key)
	{
		return (null !== $this->_getSessionData($key));
	}

	protected function _getSessionData($key)
	{
		if (isset($_SESSION[$key]))
			return $_SESSION[$key];
		return null;
	}

	protected function _removeSessionData($key)
	{
		if (isset($_SESSION[$key]))
			unset($_SESSION[$key]);
	}

	public function socialActionCompleted($provider, $method, $parameters, $result)
	{
	}

	public function pendingSocialAction()
	{
		if (!$this->hasPendingSocialAction())
			return false;

		$postData = $this->getPendingSocialAction();

		// Remove the callback data, preventing replay attacks
		$this->removePendingSocialAction();

		return $this->_client->socialAction($postData['provider'], $postData['method'], $postData['parameters'], true);
	}

	protected function _initClient()
	{
		if (isset($this->_client))
			return;

		$config = $this->getSettings();
		$this->_client = new OpenReact_XmlRpc_ServicesClient($config['endpoints'], null, array($config['applicationKey'], $config['applicationSecret']));
	}
}