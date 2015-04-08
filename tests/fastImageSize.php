<?php

/**
 * fast-image-size base test class
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\tests;

require_once __DIR__ . '/../vendor/autoload.php';

class fastImageSize extends \PHPUnit_Framework_TestCase
{
	/** @var \fastImageSize\fastImageSize */
	protected $imageSize;

	/** @var string Path to fixtures */
	protected $path;

	public function setUp()
	{
		parent::setUp();
		$this->imageSize = new \fastImageSize\fastImageSize();
		$this->path = __DIR__ . '/fixture/';
	}

	public function dataGetImageSize()
	{
		return array(
			array('foobar', 'image/bmp', false),
			array('png', 'image/png', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG)),
			array('gif', 'image/png', false),
			array('png', '', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG)),
			array('gif', 'image/gif', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_GIF)),
			array('jpg', 'image/gif', false),
			array('gif', '', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_GIF)),
			array('jpg', 'image/jpg', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG)),
			array('jpg', 'image/jpeg', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG)),
			array('png', 'image/jpg', false),
			array('jpg', '', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG)),
			array('psd', 'image/psd', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_PSD)),
			array('psd', 'image/photoshop', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_PSD)),
			array('jpg', 'image/psd', false),
			array('psd', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_PSD)),
			array('bmp', 'image/bmp', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_BMP)),
			array('png', 'image/bmp', false),
			array('bmp', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_BMP)),
			array('tif', 'image/tif', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_TIFF_II)),
			array('png', 'image/tif', false),
			array('tif', '', array('width' => 1, 'height' => 1, 'type' => IMAGETYPE_TIFF_II)),
			array('tif_compressed', 'image/tif', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_II)),
			array('png', 'image/tiff', false),
			array('tif_compressed', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_II)),
			array('tif_msb', 'image/tif', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_MM)),
			array('tif_msb', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_MM)),
			array('wbmp', 'image/wbmp', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_WBMP)),
			array('wbmp', 'image/vnd.wap.wbmp', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_WBMP)),
			array('png', 'image/vnd.wap.wbmp', false),
			array('wbmp', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_WBMP)),
			array('iff', 'image/iff', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF)),
			array('iff', 'image/x-iff', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF)),
			array('iff_maya', 'iamge/iff', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF)),
			array('png', 'image/iff', false),
			array('png', 'image/x-iff', false),
			array('iff', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF)),
			array('iff_maya', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF)),
			array('jp2', 'image/jp2', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000)),
			array('jp2', 'image/jpx', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000)),
			array('jp2', 'image/jpm', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000)),
			array('jpg', 'image/jp2', false),
			array('jpx', 'image/jpx', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000)),
			array('jp2', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000)),
			array('jpx', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000)),
			array('ico', 'image/x-icon', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO)),
			array('ico', 'image/icon', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO)),
			array('ico', 'image/ico', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO)),
			array('ico', 'image/vnd.microsoft.icon', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO)),
			array('ico', '', array('width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO)),
			array('png', 'image/icon', false),
			array('meh', '', false),
			array('meh', 'image/meh', false),
		);
	}

	/**
	 * @dataProvider dataGetImageSize
	 */
	public function test_getImageSize($file, $mime_type, $expected)
	{
		$this->assertEquals($expected, $this->imageSize->getImageSize($this->path . $file, $mime_type));
	}

	public function test_getImageSize_remote()
	{
		$this->assertSame(array(
			'width'		=> 80,
			'height'	=> 80,
			'type'		=> IMAGETYPE_JPEG,
		),
			$this->imageSize->getImageSize('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg'));
	}
}
