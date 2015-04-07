<?php

/**
 * fast-image-size image type tif
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\type;

class typeTif extends typeBase
{
	/** @var int TIF header size. The header might be larger but the dimensions
	 *			should be in the first 512 bytes */
	const TIF_HEADER_SIZE = 512;

	/** @var int TIF tag for image height */
	const TIF_TAG_IMAGE_HEIGHT = 257;

	/** @var int TIF tag for image width */
	const TIF_TAG_IMAGE_WIDTH = 256;

	/** @var int TIF tag type for short */
	const TIF_TAG_TYPE_SHORT = 3;

	/** @var int TIF IFD entry size */
	const TIF_IFD_ENTRY_SIZE = 12;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Do not force length of header
		$data = $this->fastImageSize->get_image($filename, 0, self::TIF_HEADER_SIZE, false);

		$size = array();

		$signature = substr($data, 0, self::SHORT_SIZE);

		if ($signature !== "II" && $signature !== "MM")
		{
			return;
		}

		if ($signature === "II")
		{
			$type_long = 'V';
			$type_short = 'v';
			$size['type'] = IMAGETYPE_TIFF_II;
		}
		else
		{
			$type_long = 'N';
			$type_short = 'n';
			$size['type'] = IMAGETYPE_TIFF_MM;
		}

		// Get offset of IFD
		list(, $offset) = unpack($type_long, substr($data, self::LONG_SIZE, self::LONG_SIZE));

		// Get size of IFD
		list(, $size_ifd) = unpack($type_short, substr($data, $offset, self::SHORT_SIZE));

		// Skip 2 bytes that define the IFD size
		$offset += self::SHORT_SIZE;

		// Filter through IFD
		for ($i = 0; $i < $size_ifd; $i++)
		{
			// Get IFD tag
			$type = unpack($type_short, substr($data, $offset, self::SHORT_SIZE));

			// Get field type of tag
			$field_type = unpack($type_short . 'type', substr($data, $offset + self::SHORT_SIZE, self::SHORT_SIZE));

			// Get IFD entry
			$ifd_value = substr($data, $offset + 2 * self::LONG_SIZE, self::LONG_SIZE);

			// Get actual dimensions from IFD
			if ($type[1] === self::TIF_TAG_IMAGE_HEIGHT)
			{
				$size = array_merge($size, ($field_type['type'] === self::TIF_TAG_TYPE_SHORT) ? unpack($type_short . 'height', $ifd_value) : unpack($type_long . 'height', $ifd_value));
			}
			else if ($type[1] === self::TIF_TAG_IMAGE_WIDTH)
			{
				$size = array_merge($size, ($field_type['type'] === self::TIF_TAG_TYPE_SHORT) ? unpack($type_short .'width', $ifd_value) : unpack($type_long . 'width', $ifd_value));
			}

			$offset += self::TIF_IFD_ENTRY_SIZE;
		}

		$this->fastImageSize->set_size($size);
	}
}