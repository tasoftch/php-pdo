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


use TASoft\Util\PDO;

class SQLLoader implements LoaderInterface
{
	/** @var string */
	protected $name;
	/** @var string */
	protected $driver;
	/** @var string */
	protected $loadSQL;
	/** @var string */
	protected $unloadSQL;

	/**
	 * SQLLoader constructor.
	 * @param string $name
	 * @param string $driver
	 * @param string $loadSQL
	 * @param string $unloadSQL
	 */
	public function __construct(string $name, string $driver, string $loadSQL, string $unloadSQL)
	{
		$this->name = $name;
		$this->driver = $driver;
		$this->loadSQL = $loadSQL;
		$this->unloadSQL = $unloadSQL;
	}

	/**
	 * @param string $name
	 * @return static
	 */
	public function setName(string $name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string $driver
	 * @return static
	 */
	public function setDriver(string $driver)
	{
		$this->driver = $driver;
		return $this;
	}

	/**
	 * @param string $loadSQL
	 * @return static
	 */
	public function setLoadSQL(string $loadSQL)
	{
		$this->loadSQL = $loadSQL;
		return $this;
	}

	/**
	 * @param string $unloadSQL
	 * @return static
	 */
	public function setUnloadSQL(string $unloadSQL)
	{
		$this->unloadSQL = $unloadSQL;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @inheritDoc
	 */
	public function getDriverName(): string
	{
		return $this->driver;
	}

	/**
	 * @inheritDoc
	 */
	public function loadTable(PDO $PDO, bool $skipContents = false): bool
	{
		return $PDO->exec( $this->loadSQL ) ? true : false;
	}

	/**
	 * @inheritDoc
	 */
	public function unloadTable(PDO $PDO, bool $truncateOnly = false): bool
	{
		return $PDO->exec( $this->unloadSQL ) ? true : false;
	}
}