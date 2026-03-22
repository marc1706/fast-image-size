<?php

/**
 * fast-image-size image type wbmp
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

use FastImageSize\ImageReader;

class TypeWbmp extends TypeBase
{
	/**
	 * {@inheritdoc}
	 */
	public function getSize(string $filename, ImageReader $imageReader): ?array
	{
		$data = $imageReader->getImage($filename, 0, self::LONG_SIZE);

		// Check if image is WBMP
		if ($data === false || !$this->validWBMP($data))
		{
			return null;
		}

		$size = unpack('Cwidth/Cheight', substr($data, self::SHORT_SIZE, self::SHORT_SIZE));

		// Check if dimensions are valid. A file might be recognised as WBMP
		// rather easily (see extra check for JPEG2000).
		if (!$this->validDimensions($size))
		{
			return null;
		}

		$size['type'] = IMAGETYPE_WBMP;
		return $size;
	}

	/**
	 * Return if supplied data might be part of a valid WBMP file
	 *
	 * @param string $data
	 *
	 * @return bool True if data might be part of a valid WBMP file, else false
	 */
	protected function validWBMP(string $data): bool
	{
		return ord($data[0]) === 0 && ord($data[1]) === 0 && $data !== substr(TypeJp2::JPEG_2000_SIGNATURE, 0, self::LONG_SIZE);
	}

	/**
	 * Return whether dimensions are valid
	 *
	 * @param array $size Size array
	 *
	 * @return bool True if dimensions are valid, false if not
	 */
	protected function validDimensions(array $size): bool
	{
		return $size['height'] > 0 && $size['width'] > 0;
	}
}
