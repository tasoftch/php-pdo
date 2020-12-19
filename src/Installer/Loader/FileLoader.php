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

namespace TASoft\Util\Installer\Loader;

use TASoft\Util\Exception\LoaderException;
use TASoft\Util\PDO;

class FileLoader extends SQLLoader
{
	private $parsedSQL;
	private $parsedSQL_d;
	/**
	 * FileLoader constructor.
	 * @param string $loadSQLFilename
	 * @param string $unloadSQLFilename
	 * @param string $driverName
	 */
	public function __construct(string $loadSQLFilename, string $unloadSQLFilename, string $driverName)
	{
		$name = explode(".", basename($loadSQLFilename))[0];
		if($name != explode(".", basename($unloadSQLFilename))[0])
			throw (new LoaderException("Different filenames are not allowed to be used as loader and unloader", 1))->setLoader($this);

		if(!is_file($loadSQLFilename) || !is_readable($loadSQLFilename))
			throw (new LoaderException("Can not read loader sql file", 1))->setLoader($this);

		if(!is_file($unloadSQLFilename) || !is_readable($unloadSQLFilename))
			throw (new LoaderException("Can not read unloader sql file", 1))->setLoader($this);

		parent::__construct( $name,  $driverName,  file_get_contents($loadSQLFilename),  file_get_contents($unloadSQLFilename));
	}

	/**
	 * @inheritDoc
	 */
	public function loadTable(PDO $PDO, bool $skipContents = false): bool
	{
		if(!$this->parsedSQL) {
			$this->parsedSQL = array_map(function($v) {return trim($v);}, explode("/** DATA */", $this->loadSQL));
		}
		if($PDO->exec( $this->parsedSQL[0] ) !== false) {
			if(!$skipContents && isset($this->parsedSQL[1]))
				return $PDO->exec( $this->parsedSQL[1] ) ? true : false;
			return true;
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function unloadTable(PDO $PDO, bool $truncateOnly = false): bool
	{
		if(!$this->parsedSQL_d) {
			$this->parsedSQL_d = array_map(function($v) {return trim($v);}, explode("/** TRUNCATE */", $this->unloadSQL));
		}

		if($truncateOnly && isset($this->parsedSQL_d[1]))
			return $PDO->exec( $this->parsedSQL_d[1] ) ? true : false;

		return $PDO->exec( $this->parsedSQL_d[0] ) ? true : false;
	}
}