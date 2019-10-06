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


class CallbackMapper implements MapperInterface
{
    /** @var callable */
    private $callback;

    /** @var callable|null */
    private $convertCallback;

    /**
     * CallbackMapper constructor.
     * @param callable $callback
     * @param  callable $convertCallback
     */
    public function __construct(callable $callback = NULL, callable $convertCallback = NULL)
    {
        $this->callback = $callback;
        $this->convertCallback = $convertCallback;
    }

    /**
     * @inheritDoc
     */
    public function classForType(string $type): ?string
    {
        if(is_callable($cb = $this->getCallback()))
            return call_user_func($cb, $type);
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function valueForObject($object)
    {
        if(is_callable($cb = $this->getConvertCallback()))
            return call_user_func($cb, $object);
        return NULL;
    }


    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function setCallback(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getConvertCallback(): ?callable
    {
        return $this->convertCallback;
    }

    /**
     * @param callable|null $convertCallback
     */
    public function setConvertCallback(?callable $convertCallback): void
    {
        $this->convertCallback = $convertCallback;
    }
}