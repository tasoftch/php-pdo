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


class StackByTransformer extends CompactByTransformer
{
    private $stackBy = [];

    public function __construct(array $fieldNames = ['id'], array $stackBy = [])
    {
        parent::__construct($fieldNames);
        $this->stackBy = $stackBy;
    }

    protected function mergeRecords($existingRecord, $newRecord): array
    {
        $stack = $this->getStackBy();
        foreach($newRecord as $key => $value) {
            if(in_array($key, $stack)) {
                $existingRecord[$key][] = $value;
            }
            else
                $existingRecord[$key] = $this->isIgnoreNull() ? (NULL === $existingRecord[$key] ? $value : $existingRecord[$key]) : $value;
        }
        return $existingRecord;
    }

    protected function assignRecord($record): array
    {
        $stack = $this->getStackBy();
        foreach($record as $key => &$value) {
            if(in_array($key, $stack))
                $value = NULL === $value ? [] : [$value];
        }
        return $record;
    }


    /**
     * @return array
     */
    public function getStackBy(): array
    {
        return $this->stackBy;
    }

    /**
     * @param array $stackBy
     */
    public function setStackBy(array $stackBy): void
    {
        $this->stackBy = $stackBy;
    }
}