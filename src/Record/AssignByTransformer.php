<?php
/*
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace TASoft\Util\Record;

class AssignByTransformer implements RecordTransformerInterface
{
	private $record;
	private $keyField;
	private $nameField;
	private $multipleValuesKeys;

	/**
	 * @return string
	 */
	public function getKeyField(): string
	{
		return $this->keyField;
	}

	/**
	 * @return string
	 */
	public function getNameField(): string
	{
		return $this->nameField;
	}

	/**
	 * AssignByTransformer constructor.
	 * @param string $keyField
	 * @param string $nameField
	 * @param array $multipleValuesKeys
	 */
	public function __construct(string $keyField = 'id', string $nameField = 'name', array $multipleValuesKeys = [])
	{
		$this->keyField = $keyField;
		$this->nameField = $nameField;
		$this->multipleValuesKeys = $multipleValuesKeys;
	}


	public function transform($record): ?array
	{
		if($record === NULL)
			return $this->record;

		if(isset($record[ $this->getKeyField() ]) && isset($record[ $this->getNameField() ])) {
			$id = $record[ $this->getKeyField() ];
			$value = $record[ $this->getNameField() ];

			if(in_array( $id, $this->getMultipleValuesKeys() ))
				$this->record[ $id ][] = $value;
			else
				$this->record[$id] = $value;
		}
		return NULL;
	}

	/**
	 * @return array
	 */
	public function getMultipleValuesKeys(): array
	{
		return $this->multipleValuesKeys;
	}
}