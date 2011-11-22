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
	Cache class interface
*/
interface OpenReact_Cache_Interface
{
	/**
		Get a cache entry corresponding to a key

		Parameters:
			key - (string) The key

		Returns:
			(mixed) The cache entry
	*/
	public function get($key);

	/**
		Get multiple cache entries in one go.
		Keys which were not found are not returned as entries.

		Parameters:
			keys - (array) An array of string keys to

		Returns:
			(array) The found entries
	*/
	public function getMultiple(array $keys);

	/**
		Store a cache entry with a key and a exiration time.
		Replaces the value of entry with the same key if present.

		Parameters:
			key 	- (string) The key
			value	- (mixed) The value to store (must be serializable)
			ttl		- (int) The amount of seconds this data is valid [optional]
	*/
	public function set($key, $value, $ttl = null);

	/**
		Increments a stored entry by an amount.
		If the entry does not exist, nothing happens.

		Parameters:
			key 	- (string) The key
			amount	- (int) The amount to increment by [optional]
			ttl		- (int) The amount of seconds this data is valid [optional]
	*/
	public function increment($key, $amount = 1, $ttl = null);

	/**
		Decrements a stored entry by an amount.
		If the entry does not exist, nothing happens.

		Parameters:
			key 	- (string) The key
			amount	- (int) The amount to increment by [optional]
			ttl		- (int) The amount of seconds this data is valid [optional]
	*/
	public function decrement($key, $amount = 1, $ttl = null);

	/**
		Checks if a value with a certain key exists.

		Parameters:
			key 	- (string) The key

		Returns:
			(boolean) Whether the key exists
	*/
	public function exists($key);

	/**
		Removes a key & value from the cache.

		Parameters:
			key 	- (string) The key

		Returns:
			(boolean) Whether the key was successfully deleted
	*/
	public function delete($key);

	/**
		Remove all entries from the cache
	*/
	public function flush();

	/**
		Remove all expired entries from the cache
	*/
	public function purge();

	/**
		Get all keys from the cache

		Returns:
			(array|Iterator) All the keys
	*/
	public function getAll();
}