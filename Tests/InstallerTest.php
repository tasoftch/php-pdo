<?php
/*
 * MIT License
 *
 * Copyright (d) 2019 TASoft Applications
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

use PHPUnit\Framework\TestCase;
use TASoft\Util\Installer\Loader\FileLoader;
use TASoft\Util\Installer\Loader\SQLLoader;
use TASoft\Util\Installer\Package\DirectoryDriverPackageFactory;
use TASoft\Util\Installer\Package\DirectoryPackageFactory;

class InstallerTest extends TestCase
{
	public function testSQLLoader() {
		$l = new SQLLoader('name', 'mysql', 'create table', 'drop');

		$this->assertEquals("name", $l->getName());
		$this->assertEquals("mysql", $l->getDriverName());

		$pdo = new MockExecPDO(true);
		$this->assertTrue($l->loadTable($pdo));
		$this->assertEquals("create table", $pdo->statement);

		$pdo->rVal = false;
		$this->assertFalse($l->unloadTable($pdo));
		$this->assertEquals("drop", $pdo->statement);
	}

	public function testFileLoader() {
		$fl = new FileLoader(__DIR__ . "/SQL/i/SKY_TEST.sql", __DIR__ . "/SQL/d/SKY_TEST.sql", 'mysql');
		$this->assertEquals("SKY_TEST", $fl->getName());
		$this->assertEquals("mysql", $fl->getDriverName());

		$pdo = new MockExecPDO(true);
		$this->assertTrue($fl->loadTable($pdo));
		$this->assertEquals("CREATE TABLE SKY_TEST ()", $pdo->statement);
		$pdo->rVal = false;
		$this->assertFalse($fl->unloadTable($pdo));
		$this->assertEquals("DROP TABLE SKY_TEST", $pdo->statement);
	}

	public function testFileLoaderWithContents() {
		$fl = new FileLoader(__DIR__ . "/SQL/i/SKY_TEST_C.sql", __DIR__ . "/SQL/d/SKY_TEST_C.sql", 'mysql');

		$pdo = new MockExecPDO(true);
		$this->assertTrue($fl->loadTable($pdo, true));
		$this->assertEquals("CREATE TABLE SKY_TEST_C (id int,name text);", $pdo->statement);

		$this->assertTrue($fl->loadTable($pdo, false));
		$this->assertEquals("INSERT INTO SKY_TEST_C (id, name) VALUES (1, 'hello');", $pdo->statement);

		$pdo->rVal = false;
		$this->assertFalse($fl->unloadTable($pdo, true));
		$this->assertEquals("TRUNCATE TABLE SKY_TEST_C;", $pdo->statement);

		$this->assertFalse($fl->unloadTable($pdo, false));
		$this->assertEquals("DROP TABLE SKY_TEST_C;", $pdo->statement);
	}

	public function testPackageFactory() {
		$df = new DirectoryPackageFactory( __DIR__ ."/SQL/Package/Test", 'mysql');
		$this->assertEquals("Test", $df->getName());

		$this->assertCount(2, $ld =  $df->getLoaders());
		// print_r($ld);
	}

	public function testPackageFactoryFactory() {
		$df = new DirectoryDriverPackageFactory(__DIR__ ."/SQL/Package/Contents");
		$this->assertEquals("Contents", $df->getName());
	}
}

class MockExecPDO extends \TASoft\Util\PDO {
	public $statement;
	public $rVal;

	public function __construct($returnValue)
	{
		$this->rVal = $returnValue;
	}

	public function exec($statement)
	{
		$this->statement = $statement;
		return $this->rVal;
	}
}
