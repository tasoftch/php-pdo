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

namespace TASoft\Util;


class AbstractGenericPDOResource extends AbstractRecordPDOResource
{
	protected $propertyMap = [
		// 'record-name' => 'propertyName',
		// 'record-name' => function($propertyName, &$propertyValue) {} => new property name
	];

	/**
	 * List all property values that represent arrays and must have unique values.
	 *
	 * @var array
	 */
	protected $uniqueArrayValueKeys = [];

	/**
	 * AbstractGenericPDOResource constructor.
	 * @param array|iterable $record
	 * @param callable[]|null $valueMap
	 */
	public function __construct($record, string $prefix = NULL, array $valueMap = NULL)
	{
		if(is_array($record)) {
			parent::__construct($record);
			$this->parseRecord($record, $prefix, $valueMap);
		} elseif(is_iterable($record)) {
			$f = 0;
			foreach($record as $r) {
				if(is_array($r)) {
					if(0==$f) {
						parent::__construct($r);
						$f=1;
					}
					$this->parseRecord($r, $prefix, $valueMap);
				}
			}
			$this->completeGeneric();
		}
	}

	/**
	 * Detect if a property expects an array.
	 * By default, if the initial value is an array, it will assume an array property.
	 *
	 * @param string $propertyName
	 * @return bool
	 */
	protected function isArrayProperty(string $propertyName): bool {
		return is_array($this->$propertyName ?? NULL);
	}

	/**
	 * Detect if a property expects an boolean.
	 * By default, if the initial value is true or false, it will cast incoming values as bool.
	 *
	 * @param string $propertyName
	 * @return bool
	 */
	protected function isBoolProperty(string $propertyName): bool {
		return is_bool($this->$propertyName ?? NULL);
	}

	/**
	 * Detect if a property expects a number.
	 * By default, if the initial value is numeric, it will cast incoming values as number.
	 *
	 * @param string $propertyName
	 * @return bool
	 */
	protected function isNumericProperty(string $propertyName): bool {
		return is_numeric($this->$propertyName ?? NULL);
	}

	/**
	 * Detect if a property expects a string.
	 * By default, if the initial value is a string, it will cast incoming values as string.
	 *
	 * @param string $propertyName
	 * @return bool
	 */
	protected function isStringProperty(string $propertyName): bool {
		return is_string($this->$propertyName ?? NULL);
	}

	/**
	 * This method gets called to assign the property's values
	 *
	 * @param string $propertyName
	 * @param $value
	 */
	protected function assignValue(string $propertyName, $value) {
		if(isset($this->$propertyName))
			$this->$propertyName = $value;
	}

	/**
	 * This method gets called to assign the property's array values
	 *
	 * @param string $propertyName
	 * @param $value
	 */
	protected function assignArrayValue(string $propertyName, $value) {
		if(isset($this->$propertyName))
			$this->$propertyName[] = $value;
	}

	/**
	 * Map a record key to a property name.
	 *
	 * @param string $recordKey
	 * @param $propertyValue
	 * @param array|null $propertyMap
	 * @return string
	 */
	protected function mapProperty(string $recordKey, &$propertyValue, ?array $propertyMap): string {
		$k = $propertyMap[$recordKey] ?? $recordKey;
		if(is_callable($k)) {
			return $k($recordKey, $propertyValue) ?: $recordKey;
		}
		return $k;
	}

	/**
	 * Called at final to complete the resource.
	 * Probably make array values unique
	 */
	protected function completeGeneric() {
		foreach($this->uniqueArrayValueKeys as $key) {
			if(is_array($this->$key))
				$this->$key = array_unique($this->$key);
		}

		unset($this->propertyMap);
		unset($this->uniqueArrayValueKeys);
	}

	/**
	 * @param $record
	 * @param string|null $prefix
	 * @param callable[]|null $valueMap
	 */
	protected function parseRecord($record, ?string $prefix, ?array $valueMap) {
		foreach($record as $key => $value) {
			if(NULL !== $prefix) {
				if(stripos($key, $prefix) === 0) {
					$key = substr($key, strlen($prefix) + 1);
				}
			}

			if($m = $valueMap[$key] ?? NULL)
				$value = is_callable($m) ? $m($value) : $value;

			$key = $this->mapProperty($key, $value, $valueMap);

			if($this->isBoolProperty($key))
				$this->assignValue($key, (bool)$value);
			elseif($this->isNumericProperty($key))
				$this->assignValue($key, $value * 1);
			elseif($this->isStringProperty($key))
				$this->assignValue($key, (string)$value);
			elseif($this->isArrayProperty($key))
				$this->assignArrayValue($key, $value);
			else
				$this->assignValue($key, $value);
		}
	}
}