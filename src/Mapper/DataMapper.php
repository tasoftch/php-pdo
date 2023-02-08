<?php


namespace TASoft\Util\Mapper;


class DataMapper implements \ArrayAccess, \Countable
{
	private $data = [];
	private $constraints = [];

	/**
	 * DataMapper constructor.
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}


	public function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}

	public function offsetGet($offset): mixed
	{
		if($c = $this->constraints[$offset])
			return $c($this->data[$offset] ?? NULL);

		return $this->data[$offset] ?? NULL;
	}

	public function offsetSet($offset, $value): void
	{
		if($c = $this->constraints[$offset])
			$value = $c($value);

		$this->data[$offset] = $value;
	}

	public function offsetUnset($offset): void
	{
		unset($this->data[$offset]);
	}

	public function count(): int
	{
		return count($this->data);
	}

	/**
	 * @param $offset
	 * @return $this
	 */
	public function makeOffsetNumeric($offset) {
		$this->constraints[$offset] = function($v) { return $v*1; };
		return $this;
	}

	/**
	 * @param $offset
	 * @return $this
	 */
	public function makeOffsetNumericOrNull($offset) {
		$this->constraints[$offset] = function($v) { return $v ? $v*1 : NULL; };
		return $this;
	}

	/**
	 * @param $offset
	 * @return $this
	 */
	public function makeOffsetString($offset) {
		$this->constraints[$offset] = function($v) { return "$v"; };
		return $this;
	}

	/**
	 * @param $offset
	 * @param callable $cb
	 * @return $this
	 */
	public function makeOffset($offset, callable $cb) {
		$this->constraints[$offset] = $cb;
		return $this;
	}
}