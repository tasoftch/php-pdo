<?php
/*
 * MIT License
 *
 * Copyright (d) 2019 TASoft Applications
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

use PHPUnit\Framework\TestCase;
use TASoft\Util\ChangeDetectorTrait;

class ChangeDetectorTraitTest extends TestCase
{
	public function testPropertyFetching() {
		$obj = new ChangeTestPublicPropertyMockObject();

		$this->assertEquals([
			'name', 'description'
		], $obj->getRelevantPropertyNames());


		$obj = new ChangeTestProtectedPropertyMockObject();
		$this->assertEquals([
			'myObject', 'name', 'description'
		], $obj->getRelevantPropertyNames());

		$obj = new ChangeTestPrivatePropertyMockObject();
		$this->assertEquals([
			'myObjects', 'myObject', 'name', 'description'
		], $obj->getRelevantPropertyNames());

		$obj = new ChangeTestPrivatePropertyMockObject2();
		$this->assertEquals([
			'myObjects2', /* myObjects not anymore! */ 'myObject', 'name', 'description'
		], $obj->getRelevantPropertyNames());

		$obj = new CustomHandlerPropertyMockObject();
		$this->assertEquals([
			'name', 'description'
		], $obj->getRelevantPropertyNames());
	}
}


class ChangeTestPublicPropertyMockObject
{
	use ChangeDetectorTrait;
	public $name;
	public $description;
}

class ChangeTestProtectedPropertyMockObject extends ChangeTestPublicPropertyMockObject {
	protected $myObject;

	/**
	 * @return mixed
	 */
	public function getMyObject()
	{
		return $this->myObject;
	}

	/**
	 * @param mixed $myObject
	 */
	public function setMyObject($myObject): void
	{
		$this->myObject = $myObject;
	}
}

class ChangeTestPrivatePropertyMockObject extends ChangeTestProtectedPropertyMockObject {
	private $myObjects;

	/**
	 * @return mixed
	 */
	public function getMyObjects()
	{
		return $this->myObjects;
	}

	/**
	 * @param mixed $myObjects
	 */
	public function setMyObjects($myObjects): void
	{
		$this->myObjects = $myObjects;
	}
}

class ChangeTestPrivatePropertyMockObject2 extends ChangeTestPrivatePropertyMockObject {
	private $myObjects2;

	/**
	 * @return mixed
	 */
	public function getMyObjects2()
	{
		return $this->myObjects2;
	}

	/**
	 * @param mixed $myObjects2
	 */
	public function setMyObjects2($myObjects2): void
	{
		$this->myObjects2 = $myObjects2;
	}
}

class CustomHandlerPropertyMockObject extends ChangeTestPrivatePropertyMockObject2 {
	public function getRelevantPropertyNames(): array
	{
		return ["name", 'description'];
	}

	public function setMyObject($myObject): void
	{
		$this->willChange("myObject");
		parent::setMyObject($myObject);
		$this->didChange("myObject");
	}
}