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

	/** @var array Size info array */
	protected $size;

	/** @var string Bit type of long field */
	protected $type_long;

	/** @var string Bit type of short field */
	protected $type_short;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Do not force length of header
		$data = $this->fastImageSize->get_image($filename, 0, self::TIF_HEADER_SIZE, false);

		$this->size = array();

		$signature = substr($data, 0, self::SHORT_SIZE);

		if ($signature !== "II" && $signature !== "MM")
		{
			return;
		}

		// Set byte type
		$this->setByteType($signature);

		// Get offset of IFD
		list(, $offset) = unpack($this->type_long, substr($data, self::LONG_SIZE, self::LONG_SIZE));

		// Get size of IFD
		list(, $size_ifd) = unpack($this->type_short, substr($data, $offset, self::SHORT_SIZE));

		// Skip 2 bytes that define the IFD size
		$offset += self::SHORT_SIZE;

		// Filter through IFD
		for ($i = 0; $i < $size_ifd; $i++)
		{
			// Get IFD tag
			$type = unpack($this->type_short, substr($data, $offset, self::SHORT_SIZE));

			// Get field type of tag
			$field_type = unpack($this->type_short . 'type', substr($data, $offset + self::SHORT_SIZE, self::SHORT_SIZE));

			// Get IFD entry
			$ifd_value = substr($data, $offset + 2 * self::LONG_SIZE, self::LONG_SIZE);

			// Set size of field
			$this->setSizeInfo($type[1], $field_type['type'], $ifd_value);

			$offset += self::TIF_IFD_ENTRY_SIZE;
		}

		$this->fastImageSize->set_size($this->size);
	}

	/**
	 * Set byte type based on signature in header
	 *
	 * @param string $signature Header signature
	 */
	protected function setByteType($signature)
	{
		if ($signature === "II")
		{
			$this->type_long = 'V';
			$this->type_short = 'v';
			$this->size['type'] = IMAGETYPE_TIFF_II;
		}
		else
		{
			$this->type_long = 'N';
			$this->type_short = 'n';
			$this->size['type'] = IMAGETYPE_TIFF_MM;
		}
	}

	/**
	 * Set size info
	 *
	 * @param int $dimension_type Type of dimension. Either width or height
	 * @param int $field_length Length of field. Either short or long
	 * @param string $ifd_value String value of IFD field
	 */
	protected function setSizeInfo($dimension_type, $field_length, $ifd_value)
	{
		// Set size of field
		$field_size = $field_length === self::TIF_TAG_TYPE_SHORT ? $this->type_short : $this->type_long;

		// Get actual dimensions from IFD
		if ($dimension_type === self::TIF_TAG_IMAGE_HEIGHT)
		{
			$this->size = array_merge($this->size, unpack($field_size . 'height', $ifd_value));
		}
		else if ($dimension_type === self::TIF_TAG_IMAGE_WIDTH)
		{
			$this->size = array_merge($this->size, unpack($field_size . 'width', $ifd_value));
		}
	}
}
