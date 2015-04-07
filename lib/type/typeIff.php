<?php

/**
 * fast-image-size image type iff
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\type;

class typeIff extends typeBase
{
	/** @var int IFF header size. Grab more than what should be needed to make
	 * sure we have the necessary data */
	const IFF_HEADER_SIZE = 32;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->get_image($filename, 0, self::IFF_HEADER_SIZE);

		$signature = substr($data, 0, self::LONG_SIZE );

		// Check if image is IFF
		if ($signature !== 'FORM' && $signature !== 'FOR4')
		{
			return;
		}

		// Amiga version of IFF
		if ($signature === 'FORM')
		{
			$btmhd_position = strpos($data, 'BMHD');
			$size = unpack('nwidth/nheight', substr($data, $btmhd_position + 2 * self::LONG_SIZE, self::LONG_SIZE));
		}
		// Maya version
		else
		{
			$btmhd_position = strpos($data, 'BHD');
			$size = unpack('Nwidth/Nheight', substr($data, $btmhd_position + 2 * self::LONG_SIZE - 1, self::LONG_SIZE * 2));
		}

		$this->fastImageSize->set_size($size);
		$this->fastImageSize->set_image_type(IMAGETYPE_IFF);
	}
}
