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


use TASoft\Util\Installer\Loader\LoaderInterface;
use TASoft\Util\Installer\Package\PackageFactoryInterface;
use TASoft\Util\PDO;

final class PackageInstaller implements TableInstallerInterface, TableUninstallerInterface
{
	private static $packages = [];
	/** @var LoaderInterface */
	private static $selected;

	/** @var \PDOException|null */
	private static $problem;

	/**
	 * @param LoaderInterface[]|PackageFactoryInterface[] $loaders
	 * @param string|null $packageName
	 */
	public static function registerLoaders(iterable $loaders, string $packageName = NULL) {
		foreach($loaders as $loader) {
			if($loader instanceof PackageFactoryInterface) {
				$name = $loader->getName();
				foreach($loader->getLoaders() as $ld) {
					self::$packages[$name][ $ld->getName() ][ $ld->getDriverName() ] = $ld;
				}
			} elseif($loader instanceof LoaderInterface) {
				self::$packages[$packageName][ $loader->getName() ][ $loader->getDriverName() ] = $loader;
			}
		}
	}

	/**
	 * @return array
	 */
	public static function getRegisteredPackageNames() {
		return array_keys(self::$packages);
	}

	public static function selectPackages(array $packageNames = NULL) {
		if(NULL === $packageNames)
			self::$selected = self::$packages;
		else {
			self::$selected = array_filter(self::$packages, function($k) use ($packageNames) {
				return in_array($k, $packageNames);
			}, ARRAY_FILTER_USE_KEY);
		}
	}

	/**
	 * @param PDO $PDO
	 * @return array
	 */
	public static function getAvailablePackages(PDO $PDO): array {
		$packages = [];
		foreach(self::$packages as $packageName => $loaders) {
			foreach($loaders as $loaderSet) {
				if($loader = $loaderSet[ strtolower( $PDO->getAttribute(PDO::ATTR_DRIVER_NAME) ) ])
					$packages[] = $loader;
			}
		}
		return $packages;
	}

	/**
	 * @inheritDoc
	 */
	public static function canInstall(PDO $PDO): bool
	{
		if(NULL === self::$selected)
			self::selectPackages();

		foreach(self::$selected as $loaders) {
			if(!in_array($PDO->getAttribute(PDO::ATTR_DRIVER_NAME), array_keys($loaders)))
				return false;
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function install(PDO $PDO, bool $skip = false): int
	{
		if(NULL === self::$selected)
			self::selectPackages();
		foreach(self::$selected as $loaders) {
			foreach($loaders as $drivers) {
				/** @var LoaderInterface $driver */
				if($driver = $drivers[ strtolower( $PDO->getAttribute(PDO::ATTR_DRIVER_NAME) ) ] ?? NULL) {
					try {
						if(!$driver->loadTable($PDO, $skip))
							return self::FAILED;
					} catch (\PDOException $exception) {
						self::$problem = $exception;
						return self::FAILED;
					}
				} else
					return self::FAILED;
			}
		}
		return self::SUCCESS;
	}

	/**
	 * @inheritDoc
	 */
	public static function isInstalled(PDO $PDO): bool
	{
		if(NULL === self::$selected)
			self::selectPackages();

		foreach(self::$selected as $loaders) {
			foreach(array_keys($loaders) as $tableName) {
				try {
					if(!$PDO->exec("SELECT TRUE FROM $tableName"))
						return false;
				} catch (\PDOException $e) {
					return false;
				}
			}
		}
		return true;
	}

	public static function canUninstall(PDO $PDO): bool
	{
		return self::canInstall($PDO);
	}

	public static function uninstall(PDO $PDO, bool $tr = false): int
	{
		if(NULL === self::$selected)
			self::selectPackages();
		foreach(self::$selected as $loaders) {
			foreach($loaders as $drivers) {
				/** @var LoaderInterface $driver */
				if($driver = $drivers[ strtolower( $PDO->getAttribute(PDO::ATTR_DRIVER_NAME) ) ] ?? NULL) {
					try {
						if(!$driver->unloadTable($PDO, $tr))
							return self::FAILED;
					} catch (\PDOException $exception) {
						self::$problem = $exception;
						return self::FAILED;
					}
				} else
					return self::FAILED;
			}
		}
		return self::SUCCESS;
	}

	/**
	 * @return \PDOException|null
	 */
	public static function getProblem(): ?\PDOException
	{
		return self::$problem;
	}
}