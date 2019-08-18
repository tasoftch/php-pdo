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
    const META_NATIVE_TYPE_KEY = 'native_type';
    const META_NAME_KEY = 'name';

    /**
     * @var MapperInterface|null
     */
    private $valueMapper;

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
            if($mapper = $this->getValueMapper()) {
                for($e=0;$e<$stmt->columnCount();$e++) {
                    $meta = $stmt->getColumnMeta($e);
                    if($type = strtoupper($meta[ static::META_NATIVE_TYPE_KEY ] ?? NULL)) {
                        if($class = $mapper->classForType($type))
                            $map[ $meta[ static::META_NAME_KEY ] ] = $class;
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
     * @return MapperInterface|null
     */
    public function getValueMapper(): ?MapperInterface
    {
        return $this->valueMapper;
    }

    /**
     * @param MapperInterface|null $valueMapper
     * @return static
     */
    public function setValueMapper(?MapperInterface $valueMapper): self
    {
        $this->valueMapper = $valueMapper;
        return $this;
    }
}