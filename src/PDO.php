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

namespace TASoft\Util;


use TASoft\Util\Mapper\MapperInterface;

class PDO extends \PDO
{
    public $metaTypeKey = 'native_type';
    public $metaNameNey = 'name';

    /**
     * @var MapperInterface|null
     */
    private $typeMapper;

    /**
     * PDO constructor.
     * @param $dsn
     * @param null $username
     * @param null $passwd
     * @param null $options
     */
    public function __construct($dsn, $username = NULL, $passwd = NULL, $options = NULL)
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        if(preg_match("/sqlite/i", $this->getAttribute(\PDO::ATTR_DRIVER_NAME))) {
            $this->metaTypeKey = 'sqlite:decl_type';
        }
    }

    /**
     * Yields selected records from remote SQL host.
     *
     * @param string $sql
     * @param array $arguments
     * @return bool|\Generator
     */
    public function select(string $sql, array $arguments = []) {
        $stmt = $this->prepare($sql);
        if($stmt->execute($arguments)) {
            while ($record = $stmt->fetch())
                yield $record;
            return true;
        }
        return false;
    }

    /**
     * Fetches one row and directly the value of a field name
     *
     * @param string $sql
     * @param string $fieldName
     * @param array $arguments
     * @return |null
     */
    public function selectFieldValue(string $sql, string $fieldName, array $arguments = []) {
        foreach($this->select($sql, $arguments) as $record)
            return $record[$fieldName] ?? NULL;
        return NULL;
    }

    /**
     * Same method as select but will return the first record immediately.
     *
     * @param string $sql
     * @param array $arguments
     * @return array|null
     */
    public function selectOne(string $sql, array $arguments = []): ?array {
        foreach($this->select($sql, $arguments) as $record)
            return $record;
        return NULL;
    }

    /**
     * Executes a query and returns count of affected rows.
     *
     * @param string $sql
     * @param array $arguments
     * @return int
     */
    public function count(string $sql, array $arguments = []): int {
        $stmt = $this->prepare($sql);
        if($stmt->execute($arguments)) {
            return $stmt->rowCount();
        }
        return -1;
    }

    /**
     * Yields selected records from remote SQL host.
     * While fetching from data base, this method tries to resolve known field values into objects.
     *
     * @param string $sql
     * @param array $arguments
     * @return bool|\Generator
     */
    public function selectWithObjects(string $sql, array $arguments = []) {
        $stmt = $this->prepare($sql);
        if($stmt->execute($arguments)) {
            $map = [];
            if($mapper = $this->getTypeMapper()) {
                for($e=0;$e<$stmt->columnCount();$e++) {
                    $meta = $stmt->getColumnMeta($e);
                    if($type = strtoupper($meta[ $this->metaTypeKey ] ?? NULL)) {
                        if($class = $mapper->classForType($type))
                            $map[ $meta[ $this->metaNameNey ] ] = $class;
                    }
                }
            }


            while ($record = $stmt->fetch()) {
                if($map) {
                    foreach($record as $key => &$value) {
                        if(($class = $map[$key] ?? NULL) && $value != NULL)
                            $value = new $class($value);
                    }
                }
                yield $record;
            }

            return true;
        }
        return false;
    }

    /**
     * Selects only one record but by resolving objects.
     *
     * @param string $sql
     * @param array $arguments
     * @return mixed|null
     */
    public function selectOneWithObjects(string $sql, array $arguments = []) {
        foreach($this->selectWithObjects($sql, $arguments) as $record)
            return $record;
        return NULL;
    }

    /**
     * Returns a generator to insert values sent by Generators send method into data base
     *
     * @param string $sql
     * @return \Generator
     */
    public function inject(string $sql) {
        $stmt = $this->prepare($sql);
        while (true) {
            $values = yield;
            $stmt->execute($values);
        }
    }

    /**
     * Returns a generator to insert values sent by Generators send method into data base
     * but in additional it will convert object values into their raw format
     *
     * @param string $sql
     * @return \Generator
     */
    public function injectWithObjects(string $sql) {
        $stmt = $this->prepare($sql);
        while (true) {
            $values = yield;
            if($map = $this->getTypeMapper()) {
                foreach($values as &$value) {
                    if(!is_scalar($value)) {
                        $value = $map->valueForObject($value);
                    }
                }
            }
            $stmt->execute($values);
        }
    }

    /**
     * @return MapperInterface|null
     */
    public function getTypeMapper(): ?MapperInterface
    {
        return $this->typeMapper;
    }

    /**
     * @param MapperInterface|null $typeMapper
     * @return static
     */
    public function setTypeMapper(?MapperInterface $typeMapper): self
    {
        $this->typeMapper = $typeMapper;
        return $this;
    }
}