<?php

/**
 * fast-image-size base test class
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

class TypeJpeg extends TestCase
{
	/** @var \FastImageSize\FastImageSize */
	protected $imagesize;

	/** @var \FastImageSize\Type\TypeJpeg */
	protected $typeJpeg;

	/** @var string Path to fixtures */
	protected $path;

	public function setUp(): void
	{
		parent::setUp();
		$this->imagesize = new \FastImageSize\FastImageSize();
		$this->typeJpeg = new \FastImageSize\Type\TypeJpeg($this->imagesize);
		$this->path = __DIR__ . '/fixture/';
	}

	public function dataJpegTest()
	{
		return [
			[false, "\xFF\xD8somemorerandomdata1"],
			[false, "\xFF\xD8somedata\xFF\xE0\xFF\xFF\xFF\xFF"],
			[false,
				"\xFF\xD8somedata\xFF\xC0\xFF\xFF\xFF\xFF\xFF\xFF\xFF"
			],
		];
	}

	/**
	 * @dataProvider dataJpegTest
	 */
	public function testJpegLength($expected, $data)
	{
		@file_put_contents($this->path . 'test_file.jpg', $data);

		$this->imagesize->getImageSize($this->path . 'test_file.jpg');

		$this->assertEquals($expected, $this->imagesize->getImageSize($this->path . 'test_file.jpg'));

		@unlink($this->path . 'test_file.jpg');
	}

	public function testSkipStartPaddingInfiniteLoop()
	{
		// Set data
		$reflection = new \ReflectionClass($this->typeJpeg);
		$propertyData = $reflection->getProperty('data');
		$propertyData->setAccessible(true);
		$data = "some data without SOF start marker";
		$propertyData->setValue($this->typeJpeg, $data);

		$propertyLength = $reflection->getProperty('dataLength');
		$propertyLength->setAccessible(true);
		$propertyLength->setValue($this->typeJpeg, strlen($data));

		// Invoke method
		$method = $reflection->getMethod('skipStartPadding');
		$method->setAccessible(true);

		$i = 0;
		$sofStartRead = false;

		$method->invokeArgs($this->typeJpeg, [&$i, &$sofStartRead]);

		$this->assertEquals(strlen($data), $i);
	}
}
