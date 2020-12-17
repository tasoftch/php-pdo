<?php
/**
 * Copyright (d) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
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
 */

/**
 * SimpleFetchingTest.php
 * php-pdo
 *
 * Created on 2019-08-18 20:35 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Util\Mapper\DateMapper;
use TASoft\Util\Mapper\MapperChain;
use TASoft\Util\ValueObject\Date;
use TASoft\Util\ValueObject\Time;

class SimpleFetchingTest extends TestCase
{
    public function testFetching() {
        $path = __DIR__ . "/test.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        foreach($pdo->select("SELECT * FROM TEST") as $record) {
            $this->assertEquals("thomas", $record["name"]);
        }
    }

    public function testInserting() {
        $path = __DIR__ . "/test.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        $insert = $pdo->inject("INSERT INTO INSERTING (wert) VALUES (?)");

        $count = $pdo->count("SELECT * FROM INSERTING");
        // count does not work in SQLite
        $this->assertEquals(0, $count);

        $count = $pdo->selectFieldValue("SELECT id FROM INSERTING ORDER BY id DESC LIMIT 1", "id");

        $insert->send(["Thomas"]);
        $insert->send(["Abplanalp"]);

        $count2 = $pdo->selectFieldValue("SELECT id FROM INSERTING ORDER BY id DESC LIMIT 1", "id");

        $this->assertEquals(2, $count2 - $count);
    }

    public function testMapping() {
        $path = __DIR__ . "/test.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        $mapper = new MapperChain();
        $pdo->setTypeMapper($mapper);

        $this->assertSame($mapper, $pdo->getTypeMapper());

        $record = $pdo->selectOne("SELECT * FROM TEST");
        $this->assertEquals(["id" => 1, "name" => "thomas", "b_date" => "1986-07-08 00:00:00", "test_field" => "{6, 9}"], $record);

        $mapper->addMapper( new DateMapper() );

        $record = $pdo->selectOneWithObjects("SELECT * FROM TEST");
        $this->assertInstanceOf(DateTime::class, $record["b_date"]);
    }

    public function testConverting() {
        $path = __DIR__ . "/test.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        $pdo->setTypeMapper(new DateMapper());

        $gen = $pdo->injectWithObjects("INSERT INTO MAP_TEST (datum, datum_zeit, zeit) VALUES (?, ?, ?)");

        $datum = new Date("1986-07-09");
        $datum_zeit = new \TASoft\Util\ValueObject\DateTime("1990-02-16 15:43:12");
        $zeit = new Time("18:09:34");

        $gen->send([$datum, $datum_zeit, $zeit]);

        $test = $pdo->selectOne("SELECT * FROM MAP_TEST");

        $this->assertEquals([
            "datum" => '1986-07-09',
            "datum_zeit" => '1990-02-16 15:43:12',
            "zeit" => '18:09:34'
        ], $test);

        $pdo->exec("DELETE FROM MAP_TEST WHERE 1");
    }
}
