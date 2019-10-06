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

namespace TASoft\Util\Mapper;


class MapperChain implements MapperInterface
{
    /**
     * @var MapperInterface[]
     */
    private $mappers;

    /**
     * MapperChain constructor.
     * @param MapperInterface[] $mappers
     */
    public function __construct(array $mappers = [])
    {
        $this->mappers = $mappers;
    }

    /**
     * @return MapperInterface[]
     */
    public function getMappers(): array
    {
        return $this->mappers;
    }

    /**
     * @param MapperInterface[] $mappers
     * @return static
     */
    public function setMappers(array $mappers): self
    {
        $this->mappers = $mappers;
        return $this;
    }

    /**
     * @param MapperInterface $mapper
     * @return static
     */
    public function addMapper(MapperInterface $mapper): self {
        if(!in_array($mapper, $this->mappers))
            $this->mappers[] = $mapper;
        return $this;
    }

    /**
     * @param MapperInterface $mapper
     * @return static
     */
    public function removeMapper(MapperInterface $mapper): self {
        if(($idx = array_search($mapper, $this->mappers)) !== false)
            unset($this->mappers[$idx]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function classForType(string $type): ?string
    {
        foreach($this->getMappers() as $mapper) {
            if($class = $mapper->classForType($type))
                return $class;
        }
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function valueForObject($object)
    {
        foreach($this->getMappers() as $mapper) {
            if($value = $mapper->valueForObject($object))
                return $value;
        }
        return NULL;
    }
}