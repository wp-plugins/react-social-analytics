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
class OpenReact_SocialConnector_Wordpress_ShareData
{
	// Social client
	protected $_client;

	// Tells us what we're sharing
	protected $_type;
	protected $_id;

	// Share content
	protected $_url;
	protected $_message;
	protected $_title;
	protected $_media;
	protected $_excerpt;

	public function __construct($id = null, $type = null)
	{
		$this->_client = get_react_social_analytics_plugin()->getClient();

		$this->setContent($id, $type);
	}

	/*
	 * Determine share content (title, image, message, etc). Fetches data from $_GET, but might
	 * be extended in the future. I.e., function could accept type and id and fetch relevant data form db.
	 */
	public function setContent($id = null, $type = null)
	{
		if($id && $type)
		{
			$this->_id = (int)$id;
			$this->_type = $type;
			$this->_dataFromPost();
		}
		else if(!empty($_GET['id']))
		{
			$this->_id = (int)$_GET['id'];
			$this->_type = 'post';
			$this->_dataFromPost();
		}
		else if(!empty($_GET['p']))
		{
			$this->_id = (int)$_GET['p'];
			$this->_type = 'post';
			$this->_dataFromPost();
		}
		else
		{
			$this->_type = 'query';
			$this->_dataFromQuery();
		}
	}

	/*
	 * Return sharedata
	 */
	public function getShareData()
	{
		return array(
			'type' => $this->_type,
			'id' => $this->_id,
			'url' => $this->_url,
			'message' => $this->_message,
			'title' => $this->_title,
			'media' => $this->_media,
			'excerpt' => $this->_excerpt,
		);
	}

	/*
	 * Get share data from $_GET
	 */
	protected function _dataFromQuery()
	{
		// Always set url. If empty, referer will be used.
		$this->_setUrl($_GET['url']);

		// Set title
		$this->_setTitle($_GET['title']);

		// Default share message
		$this->_setMessage($_GET['message']);

		// Set media
		if(!empty($_GET['media_type']) && !empty($_GET['media_url']))
			$this->_setMedia(array('url' => $_GET['media_url']), $_GET['media_type']);
	}

	protected function _dataFromPost()
	{
		if(!$post = get_post($this->_id, 'OBJECT'))
			return false;

		$thumb_id = get_post_thumbnail_id($post->ID);

		$this->_setUrl(get_permalink($post->ID));
		$this->_setTitle($post->post_title);
		$this->_setMessage($post->post_title . ', check it out at ' . get_bloginfo('name') . '!');
		$this->_excerpt = $post->post_excerpt;
	}

	/**
	 * Simple typecheck
	 */
	protected function _mediaType($url)
	{
		$pieces = explode('.', $url);
		$extension = array_pop($pieces);

		switch($extension)
		{
			case 'mov':
			case 'flv':
			case 'mp4':
				return 'movie';
			case 'wmv':
			case 'mp3':
			case 'm4p':
				return 'audio';
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
			case 'bmp':
			default:
				return 'picture';
		}
	}

	/*
	 * Set url to share. Url should match hostname
	 */
	protected function _setUrl($url = null)
	{
		if(!$url)
			$url = $_SERVER['HTTP_REFERER'];

		$wp = parse_url(get_bloginfo('url'));
		$usr = parse_url($url);

		if($wp['host'] != $usr['host'])
			$url = get_bloginfo('url');

		$this->_url = $url;
	}

	/*
	 * Set title, or default
	 */
	protected function _setTitle($title = null)
	{
		if(!$title)
			$title = 'Shared from ' . get_bloginfo('name');

		$this->_title = $title;
	}

	/*
	 * Set message, default to 'check out' message
	 */
	protected function _setMessage($message = null)
	{
		if(!$message)
			$message = 'Check out what I just found at ' . get_bloginfo('name') . '!';

		$message = $message . ' ' . $this->_url;

		try
		{
			$message = $this->_client->BitLy->shortenUrlsInText($message, null);
		}
		catch(Exception $e) {} // Ignore bitly errors. Service could be down or over limit

		$this->_message = $message;
	}

	/*
	 * Set media
	 */
	protected function _setMedia($params, $type = null)
	{
		if(!$type)
			$type = $this->_mediaType($params['url']);

		$media['type'] = $type;

		switch($type)
		{
			case 'picture':
				$media['url'] = $params['url'];
				$media['width'] = $params['width'];
				$media['height'] = $params['height'];
			break;
			case 'movie':
				$media['type'] = 'movie';
				$media['url'] = $params['url'];
			break;
			case 'audio':
				$media['type'] = 'audio';
				$media['url'] = $params['url'];
			break;
		}

		$this->_media = $media;
	}
}
