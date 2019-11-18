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
 * The compact by transformer allows you to stack records on given field names.
 * Example: compact by id, so all values are stacked until id changes:
 *  $record1 : {'id' => 1, 'name' => 'John'} => Returns NULL
 *  $record2 : {'id' => 1, 'name' => 'Paul'} => Returns NULL
 *  $record3 : {'id' => 2, 'name' => 'Jane'} => Returns {'id' => 1, 'name' => 'Paul'}
 *  NULL (fin)                               => Returns {'id' => 2, 'name' => 'Jane'}
 *
 * @package TASoft\Util\Record
 */
class CompactByTransformer implements RecordTransformerInterface
{
    /** @var array */
    private $fieldNames = [];
    private $record;
    private $ignoreNull = true;

    /**
     * CompactByTransformer constructor.
     * @param array $fieldNames
     */
    public function __construct(array $fieldNames = ['id'])
    {
        $this->fieldNames = $fieldNames;
    }



    public function transform($record): ?array
    {
        if(NULL === $record)
            return $this->record;

        if(NULL === $this->record) {
            $this->record = $this->assignRecord($record);
            return NULL;
        }

        $equal = true;
        foreach($this->getFieldNames() as $fn) {
            if($this->record[$fn] != $record[$fn]) {
                $equal = false;
                break;
            }
        }

        if($equal)
            $this->record = $this->mergeRecords($this->record, $record);
        else {
            $r = $this->record;
            $this->record = $this->assignRecord($record);
            return $r;
        }
        return NULL;
    }


    protected function mergeRecords($existingRecord, $newRecord): array {
        if($this->isIgnoreNull()) {
            foreach($newRecord as $key => $value) {
                if(NULL === $existingRecord[$key])
                    $existingRecord[$key] = $value;
            }
            return $existingRecord;
        } else {
            return array_merge($existingRecord, $newRecord);
        }
    }

    protected function assignRecord($record): array {
        return $record;
    }

    /**
     * @param string $fieldName
     * @return static
     */
    public function addFieldName(string $fieldName) {
        if(!in_array($fieldName, $this->fieldNames))
            $this->fieldNames[] = $fieldName;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldNames(): array
    {
        return $this->fieldNames;
    }

    /**
     * @param array $fieldNames
     * @return static
     */
    public function setFieldNames(array $fieldNames)
    {
        $this->fieldNames = $fieldNames;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreNull(): bool
    {
        return $this->ignoreNull;
    }

    /**
     * @param bool $ignoreNull
     */
    public function setIgnoreNull(bool $ignoreNull): void
    {
        $this->ignoreNull = $ignoreNull;
    }
}