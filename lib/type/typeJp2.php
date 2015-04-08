<?php

/**
 * fast-image-size image type jpeg 2000
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\type;

class typeJp2 extends typeBase
{
	/** @var string JPEG 2000 signature */
	const JPEG_2000_SIGNATURE = "\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A\x87\x0A";

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->get_image($filename, 0, typeJpeg::JPEG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg 2000
		if (substr($data, 0, strlen(self::JPEG_2000_SIGNATURE)) !== self::JPEG_2000_SIGNATURE)
		{
			return;
		}

		// Get SOC position before starting to search for SIZ
		$soc_position = strpos($data, "\xFF\x4F");

		// Make sure we do not get SIZ before SOC
		$data = substr($data, $soc_position);

		$siz_position = strpos($data, "\xFF\x51");

		// Remove SIZ and everything before
		$data = substr($data, $siz_position + self::SHORT_SIZE);

		// Acquire size info from data
		$size = unpack('Nwidth/Nheight', substr($data, self::LONG_SIZE, self::LONG_SIZE * 2));

		$this->fastImageSize->set_size($size);
		$this->fastImageSize->set_image_type(IMAGETYPE_JPEG2000);
	}
}
