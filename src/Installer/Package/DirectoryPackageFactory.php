<?php
/*
 * MIT License
 *
 * Copyright (c) 2019 TASoft Applications
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

namespace TASoft\Util\Installer\Package;


use TASoft\Util\Exception\LoaderException;
use TASoft\Util\Installer\Loader\FileLoader;

/**
 * A DirectoryPackageFactory reads a directory as package and each *.sql file as a separate table.
 *
 * /package
 *    i/		*.sql files named as table that will be created
 *    d/		*.sql files named as table that will be dropped
 *
 * @package TASoft\Util\Installer\Package
 */
class DirectoryPackageFactory implements PackageFactoryInterface
{
	const LOAD_DIRECTORY = 'i';
	const UNLOAD_DIRECTORY = 'd';

	protected $driverName;
	protected $name;
	private $loaderPairs;

	/**
	 * DirectoryPackageFactory constructor.
	 *
	 * @param string $directoryName
	 * @param string $driverName
	 * @param string $sqlFilePattern
	 */
	public function __construct(string $directoryName, string $driverName, string $sqlFilePattern = '*.sql')
	{
		$loadDir = sprintf("%s%s%s%s", $directoryName, DIRECTORY_SEPARATOR, static::LOAD_DIRECTORY, DIRECTORY_SEPARATOR);
		$unloadDir = sprintf("%s%s%s%s", $directoryName, DIRECTORY_SEPARATOR, static::UNLOAD_DIRECTORY, DIRECTORY_SEPARATOR);

		if(!is_dir($loadDir) || !is_dir($unloadDir))
			throw (new LoaderException("Directory does not exist"))->setLoader($this);

		$tables = [];

		foreach(new \DirectoryIterator($loadDir) as $item) {
			if(fnmatch($sqlFilePattern, $item->getBasename())) {
				$tableName = explode(".", $item->getBasename())[0];
				$tables[$tableName][0] = $item->getRealPath();
			}
		}
		foreach(new \DirectoryIterator($unloadDir) as $item) {
			if(fnmatch($sqlFilePattern, $item->getBasename())) {
				$tableName = explode(".", $item->getBasename())[0];
				$tables[$tableName][1] = $item->getRealPath();
			}
		}

		$this->driverName = $driverName;
		$this->name = basename($directoryName);

		$this->loaderPairs = array_filter($tables, function($v) { return count($v) == 2; });
	}

	/**
	 * @inheritDoc
	 */
	public function getLoaders(): iterable
	{
		$loaders = [];
		foreach($this->loaderPairs as $pair) {
			$loaders[] = new FileLoader($pair[0], $pair[1], $this->driverName);
		}
		return $loaders;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return $this->name;
	}
}