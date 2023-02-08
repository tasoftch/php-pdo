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

namespace TASoft\Util\ValueObject;


use TASoft\Util\ValueObject\DateLocale\DateLocale;

abstract class AbstractDateObject extends \DateTime
{
    public $useDateLocale = true;

    const DEFAULT_FORMAT = '';

    public function format($format): string
    {
        $string = "";
        for($e=0;$e<strlen($format);$e++) {
            $char = $format[$e];
            $string .= $this->convertSymbol($char);
        }
        return $string;
    }

    protected function convertSymbol($symbol) {
        if($this->useDateLocale) {
            switch ($symbol) {
                case 'l': return DateLocale::getLocale()->getLongDayNames()[ parent::format("w")*1 ];
                case 'D': return DateLocale::getLocale()->getShortDayNames()[ parent::format("w")*1 ];
                case 'F': return DateLocale::getLocale()->getLongMonthNames()[ parent::format("m")-1 ];
                case 'M': return DateLocale::getLocale()->getShortMonthNames()[ parent::format("m")-1 ];
            }
        }
        return parent::format($symbol);
    }

    public function __toString()
    {
        return $this->format( static::DEFAULT_FORMAT );
    }
}