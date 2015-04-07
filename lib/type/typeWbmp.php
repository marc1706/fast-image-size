<?php

/**
 * fast-image-size image type wbmp
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\type;

class typeWbmp extends typeBase
{
	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->get_image($filename, 0, self::LONG_SIZE);

		// Check if image is WBMP
		if (ord($data[0]) !== 0 || ord($data[1]) !== 0 || $data === substr(typeJp2::JPEG_2000_SIGNATURE, 0, 4))
		{
			return;
		}

		$size = unpack('Cwidth/Cheight', substr($data, self::SHORT_SIZE, self::SHORT_SIZE));

		$this->fastImageSize->set_size($size);
		$this->fastImageSize->set_image_type(IMAGETYPE_WBMP);
	}
}