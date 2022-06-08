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


use Closure;
use TASoft\Util\Mapper\DataMapper;
use TASoft\Util\Mapper\MapperInterface;
use Throwable;

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
            while ($record = $stmt->fetch()) {
                yield $record;
            }
            return true;
        }
        return false;
    }

	/**
	 * Selects rows by sql and arguments and stacks the values according to the passed fieldname(s).
	 *
	 * @param string $sql
	 * @param string|array $fieldName
	 * @param array $arguments
	 * @return array
	 */
	public function selectStack(string $sql, $fieldName, array $arguments = []) {
		$stack = [];
		foreach($this->select($sql, $arguments) as $record) {
			if(is_string($fieldName))
				$stack[] = $record[$fieldName];
			elseif(is_iterable($fieldName)) {
				$d=[];
				foreach($fieldName as $key => $default) {
					if(is_numeric($key)) {$key = $default;$default = NULL;}
					$d[] = $record[$key] ?? $default;
				}
				$stack[] = $d;
			}
		}
		return $stack;
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
     * Returns a generator to insert or update values sent by Generators send method into data base
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
	 * @param string $sql
	 * @param callable|null $map
	 * @return \Generator
	 */
    public function injectFiltered(string $sql, callable $map = NULL) {
		$stmt = $this->prepare($sql);
		if(preg_match_all("/:(\w+)/i", $sql, $ms)) {
			$filter = function(&$k, &$v) use ($ms, $map) {
				if($map && !$map($k, $v))
					return false;
				if(!in_array($k, $ms[1]))
					return false;
				return true;
			};
		} else {
			$filter = function(&$k, &$v) use ($map) {
				if($map && !$map($k, $v))
					return false;
				return true;
			};
		}
		while (true) {
			$values = [];
			foreach (yield as $k => $v) {
				if($filter($k, $v))
					$values[$k] = $v;
			}
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
	 * @param string $tableName
	 * @param array|DataMapper $data
	 * @param int|null $existingID
	 * @param string ...$fieldValues
	 */
    public function update(string $tableName, $data, int $existingID = NULL, ...$fieldValues) {
    	if($fieldValues) {
    		if(NULL === $existingID) {
    			$fv = implode(",", array_map(function($v) {
    				return "`$v`";
				}, $fieldValues));
				$vv = implode(",", array_pad([], count($fieldValues), '?'));
				$sql = $this->inject("INSERT INTO $tableName ($fv) VALUES ($vv)");
			} else {
    			$fv = [];
    			foreach($fieldValues as $v)
    				$fv[] = "`$v`=?";
    			$fv = implode(",", $fv);
				$sql = $this->inject("UPDATE $tableName SET $fv WHERE id = $existingID");
			}

			$prepared = [];
			foreach ($fieldValues as $k) {
				$prepared[] = $data[$k] ?? NULL;
			}

			$sql->send($prepared);
		}
	}

    /**
     * Performs SQL queries in transaction mode.
     *
     * @param callable $callbackToPerformTransaction The callback is called in transaction mode. If an exception was thrown, nothing is done in the database.
     * @param bool $propagateException If true, the exception is thrown out of this method
     * @param bool $bindToPDO
     * @return bool
     * @throws Throwable
     */
    public function transaction(callable $callbackToPerformTransaction, bool $propagateException = true, bool $bindToPDO = false): bool {
        if($callbackToPerformTransaction instanceof Closure && $bindToPDO)
            $callbackToPerformTransaction = $callbackToPerformTransaction->bindTo($this, get_class($this));

        if($this->inTransaction()) {
            // PDO can not stack transactions.
            call_user_func($callbackToPerformTransaction);
        } else {
            try {
                $this->beginTransaction();
                call_user_func($callbackToPerformTransaction);
                $this->commit();
            } catch (Throwable $exception) {
                $this->rollBack();
                if($propagateException)
                    throw $exception;
                return false;
            }
        }
        return true;
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