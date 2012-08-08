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
class OpenReact_Wordpress_Cache implements OpenReact_Cache_Interface
{
	const TTL_DAY = 86400;
	const TTL_HOUR = 3600;
	const TTL_MINUTE = 60;

	/**
		Get a cache entry corresponding to a key

		Parameters:
			key - (string) The key

		Returns:
			(mixed) The cache entry
	*/
	public function get($key)
	{
		return get_transient('OpenReact_Wordpress_Cache'.md5($key));
	}

	/**
		Store a cache entry with a key and a exiration time.
		Replaces the value of entry with the same key if present.

		Parameters:
			key 	- (string) The key
			value	- (mixed) The value to store (must be serializable)
			ttl		- (int) The amount of seconds this data is valid [optional]
	*/
	public function set($key, $value, $ttl = 0)
	{
		return set_transient('OpenReact_Wordpress_Cache'.md5($key), $value, $ttl);
	}

	public function delete($key)
	{
		return delete_transient($key);
	}

	public function getMultiple(array $keys) { throw new Exception('Not implemented'); }
	public function increment($key, $amount = 1, $ttl = null) { throw new Exception('Not implemented'); }
	public function decrement($key, $amount = 1, $ttl = null) { throw new Exception('Not implemented'); }
	public function exists($key) { throw new Exception('Not implemented'); }
	public function flush() { throw new Exception('Not implemented'); }
	public function purge() { throw new Exception('Not implemented'); }
	public function getAll() { throw new Exception('Not implemented'); }
}
