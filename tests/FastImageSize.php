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

class FastImageSize extends TestCase
{
	/** @var \FastImageSize\FastImageSize */
	protected $imageSize;

	/** @var string Path to fixtures */
	protected $path;

	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		if (!defined('IMAGETYPE_WEBP'))
		{
			define('IMAGETYPE_WEBP', 18);
		}
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->imageSize = new \FastImageSize\FastImageSize();
		$this->path = __DIR__ . '/fixture/';
	}

	public function dataGetImageSize(): array
	{
		return [
			['foobar', 'image/bmp', false],
			['png', 'image/png', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['gif', 'image/png', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_GIF]],
			['png', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['gif', 'image/gif', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_GIF]],
			['jpg', 'image/gif', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['gif', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_GIF]],
			['jpg', 'image/jpg', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['jpg', 'image/jpeg', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['png', 'image/jpg', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['jpg', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['psd', 'image/psd', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_PSD]],
			['psd', 'image/photoshop', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_PSD]],
			['jpg', 'image/psd', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['psd', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_PSD]],
			['bmp', 'image/bmp', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_BMP]],
			['png', 'image/bmp', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['bmp', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_BMP]],
			['tif', 'image/tif', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_TIFF_II]],
			['png', 'image/tif', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['tif', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_TIFF_II]],
			['tif_compressed', 'image/tif', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_II]],
			['png', 'image/tiff', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['tif_compressed', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_II]],
			['tif_msb', 'image/tif', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_MM]],
			['tif_msb', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_TIFF_MM]],
			['tif_error.tif', '', ['width' => 1920, 'height' => 1030, 'type' => IMAGETYPE_TIFF_II]],
			['wbmp', 'image/wbmp', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_WBMP]],
			['foobar', 'image/wbmp', false],
			['wbmp', 'image/vnd.wap.wbmp', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_WBMP]],
			['png', 'image/vnd.wap.wbmp', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['wbmp', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_WBMP]],
			['iff', 'image/iff', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF]],
			['iff', 'image/x-iff', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF]],
			['iff_maya', 'iamge/iff', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF]],
			['png', 'image/iff', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['png', 'image/x-iff', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['iff', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF]],
			['iff_maya', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_IFF]],
			['foobar', 'image/iff', false],
			['jp2', 'image/jp2', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000]],
			['jp2', 'image/jpx', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000]],
			['jp2', 'image/jpm', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000]],
			['jpg', 'image/jp2', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['jpx', 'image/jpx', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000]],
			['jp2', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000]],
			['jpx', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_JPEG2000]],
			['ico', 'image/x-icon', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO]],
			['ico', 'image/icon', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO]],
			['ico', 'image/ico', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO]],
			['ico', 'image/vnd.microsoft.icon', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO]],
			['ico', '', ['width' => 2, 'height' => 1, 'type' => IMAGETYPE_ICO]],
			['foobar', 'image/x-icon', false],
			['png', 'image/icon', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['meh', '', false],
			['meh', 'image/meh', false],
			['exif.jpg', 'image/jpeg', ['width' => 100, 'height' => 100, 'type' => IMAGETYPE_JPEG]],
			['phpBB_logo.jpg', '', ['width' => 152, 'height' => 53, 'type' => IMAGETYPE_JPEG]],
			['phpBB_logo.jpg', 'image/jpg', ['width' => 152, 'height' => 53, 'type' => IMAGETYPE_JPEG]],
			['dog.jpg', '', ['width' => 300, 'height' => 300, 'type' => IMAGETYPE_JPEG]],
			// Capital file names
			['JPGL', 'image/jpg', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['JPGL', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['PNGL', 'image/png', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['PNGL', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['JPGL', 'image/png', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			// Capital extesion
			['jpg.JPG', 'image/jpg', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['jpg.JPG', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['png.PNG', 'image/png', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['png.PNG', '', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_PNG]],
			['jpg.JPG', 'image/png', ['width' => 1, 'height' => 1, 'type' => IMAGETYPE_JPEG]],
			['supercup.jpg', 'image/jpg', ['width' => 700, 'height' => 525, 'type' => IMAGETYPE_JPEG]],
			['641.jpg', 'image/jpg', ['width' => 641, 'height' => 399, 'type' => IMAGETYPE_JPEG]],
			['CCITT_1.TIF', '', ['width' => 1728, 'height' => 2376, 'type' => IMAGETYPE_TIFF_II]],
			['simple.webp', 'image/webp', ['width' => 550, 'height' => 368, 'type' => IMAGETYPE_WEBP]],
			['simple.webp', '', ['width' => 550, 'height' => 368, 'type' => IMAGETYPE_WEBP]],
			['simple.webp', 'image/jpeg', ['width' => 550, 'height' => 368, 'type' => IMAGETYPE_WEBP]],
			['lossless.webp', 'image/webp', ['width' => 386, 'height' => 395, 'type' => IMAGETYPE_WEBP]],
			['lossless.webp', '', ['width' => 386, 'height' => 395, 'type' => IMAGETYPE_WEBP]],
			['lossless.webp', 'image/jpeg', ['width' => 386, 'height' => 395, 'type' => IMAGETYPE_WEBP]],
			['extended.webp', 'image/webp', ['width' => 386, 'height' => 395, 'type' => IMAGETYPE_WEBP]],
			['extended.webp', '', ['width' => 386, 'height' => 395, 'type' => IMAGETYPE_WEBP]],
			['extended.webp', 'image/jpeg', ['width' => 386, 'height' => 395, 'type' => IMAGETYPE_WEBP]],
			['wrong_format.webp', 'image/webp', false],
			['no_riff.webp', 'image/webp', false],
		];
	}

	/**
	 * @dataProvider dataGetImageSize
	 */
	public function test_getImageSize($file, $mime_type, $expected)
	{
		$this->assertEquals($expected, $this->imageSize->getImageSize($this->path . $file, $mime_type));
	}

	public function dataGetImageSizeRemote(): array
	{
		return [
			[[
				'width'		=> 80,
				'height'	=> 80,
				'type'		=> IMAGETYPE_JPEG,
			], 'https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0.jpg'],
			[[
				'width'		=> 1100,
				'height'	=> 729,
				'type'		=> IMAGETYPE_JPEG,
			], 'http://www.techspot.com/articles-info/1121/images/P34WS-12.jpg'],
			[
				false,
				'https://www.google.com/just_some_random_test_for_a_dummy_url_that_hopefully_never_exists/articles-info/1121/images/P34WS-12.jpg'
			],
		];
	}

	/**
	 * @dataProvider dataGetImageSizeRemote
	 */
	public function test_getImageSize_remote($expected, $url)
	{
		$this->assertSame($expected, $this->imageSize->getImageSize($url));
	}

	public function test_memory_usage()
	{
		$mem_start = memory_get_usage();
		for ($i = 0; $i < 50000; $i++) {

			$FastImageSize = new \FastImageSize\FastImageSize();
			$path = $this->path . "jpg.JPG";
			$imageSize = $FastImageSize->getImageSize($path);
			unset($FastImageSize);
			unset($imageSize);
		}
		$mem_end = memory_get_usage();

		// Memory usage should be more or less the same after 50.000 calls, so we allow a small increase of 512 bytes
		$this->assertLessThan(512, $mem_end - $mem_start);
	}
}
