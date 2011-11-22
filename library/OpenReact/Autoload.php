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
	Simple class autoloading via static methods.

	To properly initialize an OpenReact environment, include this class and call OpenReact_Autoload::register()
*/
class OpenReact_Autoload
{
	/** (array) Library paths which there will be searched for OpenReact classes on autoload */
	public static $_libraryPaths;

	/**
		Register the OpenReact_Autoload::autoload() class loader with the specified library directory(s).
		Also registers the OpenReact_Exception::autocreate() class loader.

		Parameters:
			libraryPaths - (null|string|array) library directory (or directories) where OpenReact classes can be found. If NULL is passed, only the library directory of the OpenReact_Autoload class is used.
	*/
	public static function register($libraryPaths = NULL)
	{
		if (isset(self::$_libraryPaths))
			throw new OpenReact_Autoload_AlreadyRegisteredException('The OpenReact autoloader is already registered.');

		if (!isset($libraryPaths))
			$libraryPaths = array(realpath(dirname(dirname(__FILE__))));

		if (is_string($libraryPaths))
			$libraryPaths = array($libraryPaths);

		self::$_libraryPaths = $libraryPaths;

		foreach ($libraryPaths as $path)
			self::addIncludePath($path);

		self::autoload('OpenReact_Exception');
		spl_autoload_register(array('OpenReact_Exception', 'autocreate'));
		spl_autoload_register(array('OpenReact_Autoload', 'autoload'));
	}

	/**
		Get paths where the autoloader is currently searching for OpenReact classes.

		Returns:
			(array) library paths
	*/
	public static function getLibraryPaths()
	{
		return self::$_libraryPaths;
	}

	/**
		Add a path to the PHP 'include_path'.
		Any paths added will also be searched for OpenReact classes by the autoloader.

		Parameters:
			path - (string) path to add
	*/
	public static function addIncludePath($path)
	{
		ini_set('include_path', rtrim($path, '/') . PATH_SEPARATOR . ini_get('include_path'));
	}

	/**
		Try to autoload a class.
		Will only autoload classes beginning with 'OpenReact_'.

		Parameters:
			className - (string) class to autoload

		Returns:
			(boolean) if the class was loaded
	*/
	public static function autoload($className)
	{
		if (strpos($className, 'OpenReact_') !== 0)
			return false;

		require (str_replace('_', '/', $className) . '.php');
		return true;
	}
}