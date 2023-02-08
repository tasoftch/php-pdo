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

namespace TASoft\Util\Record;


/**
 * Use objects of the abstract transformer.
 * @package TASoft\Util\Record
 */
class RecordTransformerAdapter implements RecordTransformerInterface, \IteratorAggregate
{
	private $generator;
	private $transformer;

	public function __construct(RecordTransformerInterface $transformer, iterable $selectGenerator)
	{
		$this->generator = $selectGenerator;
		$this->transformer = $transformer;
	}

	public function getIterator(): \Traversable
	{
		return $this();
	}


	public function __invoke()
	{
		foreach($this->generator as $record) {
			$record = $this->transform($record);
			if($record)
				yield $record;
		}

		$record = $this->transform(NULL);
		if($record)
			yield $record;
	}

	/**
	 * Forwarder method
	 * @inheritDoc
	 */
	public function transform($record): ?array
	{
		return $this->getTransformer()->transform($record);
	}

	/**
	 * @return RecordTransformerInterface
	 */
	public function getTransformer(): RecordTransformerInterface
	{
		return $this->transformer;
	}

	/**
	 * @return \Generator
	 */
	public function getGenerator(): \Generator
	{
		return $this->generator;
	}
}