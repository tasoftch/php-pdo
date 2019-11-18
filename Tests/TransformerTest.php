<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
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
 * TransformerTest.php
 * php-pdo
 *
 * Created on 2019-11-18 11:24 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Util\Record\CompactAllTransformer;
use TASoft\Util\Record\CompactByTransformer;
use TASoft\Util\Record\StackByTransformer;

class TransformerTest extends TestCase
{
    public function testTransformer() {
        $path = __DIR__ . "/test.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        $count = $pdo->selectFieldValue("SELECT id FROM INSERTING ORDER BY id DESC LIMIT 1", "id") * 1;

        $c = 0;
        foreach($pdo->select("SELECT * FROM INSERTING") as $record) {
            $c++;
        }
        $this->assertEquals($count, $c);

        $pdo->setTransformer( new CompactAllTransformer() );

        $passed = false;

        foreach($pdo->select("SELECT * FROM INSERTING") as $record) {
            $this->assertFalse($passed);
            $passed = true;
            $this->assertCount($count, $record);
        }
    }

    public function testCompactTransformer() {
        $path = __DIR__ . "/transform.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        $pdo->setTransformer(new CompactByTransformer(["name"]));

        $result = [
            [
                "wert" => "Abplanalp",
                "name" => "Thomas"
            ],
            [
                "wert" => "Maurer",
                "name" => "Daniela"
            ],
            [
                "wert" => "Zaege",
                "name" => "Priska"
            ],
            [
                "wert" => "Abplanalp",
                "name" => "Bettina"
            ],
            [
                "wert" => NULL,
                "name" => "Katrin"
            ]
        ];

        foreach($pdo->select("SELECT wert, name FROM TRANSFORMER LEFT JOIN TRANSFORMER_ADDON ON transformer = TRANSFORMER.id") as $record) {
            $this->assertEquals(array_shift($result), $record);
        }
    }

    public function testStackTransformer() {
        $path = __DIR__ . "/transform.sqlite";
        $pdo = new \TASoft\Util\PDO("sqlite:$path");

        $pdo->setTransformer(new StackByTransformer(["name"], ["wert"]));

        foreach($pdo->select("SELECT wert, name FROM TRANSFORMER LEFT JOIN TRANSFORMER_ADDON ON transformer = TRANSFORMER.id") as $record) {
            print_r($record);
        }
    }
}
