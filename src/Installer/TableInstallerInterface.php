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

namespace TASoft\Util\Installer;


use TASoft\Util\PDO;

interface TableInstallerInterface extends InstallerInterface
{
	/**
	 * Checks if the package is able to be installed on a given PDO
	 *
	 * @param PDO $PDO
	 * @return bool
	 */
	public static function canInstall(PDO $PDO): bool;

	/**
	 * Initializes the table package.
	 *
	 * @param PDO $PDO
	 * @param bool $skipContents
	 * @return int
	 */
	public static function install(PDO  $PDO, bool $skipContents = false): int;

	/**
	 * Returns true, if the package is completely installed on given PDO.
	 *
	 * @param PDO $PDO
	 * @return bool
	 */
	public static function isInstalled(PDO $PDO): bool;
}