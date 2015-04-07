<?php

/**
 * fast-image-size image type jpeg
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\type;

class typeJpeg extends typeBase
{
	/** @var int JPG max header size. Headers can be bigger, but we'll abort
	 *			going throught he header after this */
	const JPG_MAX_HEADER_SIZE = 24576;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$size = array();

		// Do not force the data length
		$data = $this->fastImageSize->get_image($filename, 0, self::JPG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg
		if ($data[0] !== "\xFF" || $data[1] !== "\xD8")
		{
			return;
		}

		// Look through file for SOF marker
		for ($i = 2 * self::SHORT_SIZE; $i < strlen($data); $i++)
		{
			if ($data[$i] === "\xFF" && in_array($data[$i+1], array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF")))
			{
				// Extract size info from SOF marker
				list(, $unpacked) = unpack("H*", substr($data, $i + self::SHORT_SIZE, 7));

				// Get width and height from unpacked size info
				$size = array(
					'width'		=> hexdec(substr($unpacked, 10, 4)),
					'height'	=> hexdec(substr($unpacked, 6, 4)),
				);

				break;
			}
		}

		$this->fastImageSize->set_size($size);
		$this->fastImageSize->set_image_type(IMAGETYPE_JPEG);
	}
}
