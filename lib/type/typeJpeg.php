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
	/** @var int JPEG max header size. Headers can be bigger, but we'll abort
	 *			going throught he header after this */
	const JPEG_MAX_HEADER_SIZE = 24576;

	/** @var string JPEG header */
	const JPEG_HEADER = "\xFF\xD8";

	/** @var string Start of frame marker */
	const SOF_START_MARKER = "\xFF";

	/** @var array JPEG SOF markers */
	protected $sofMarkers = array(
		"\xC0",
		"\xC1",
		"\xC2",
		"\xC3",
		"\xC5",
		"\xC6",
		"\xC7",
		"\xC8",
		"\xC9",
		"\xCA",
		"\xCB",
		"\xCD",
		"\xCE",
		"\xCF"
	);

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Do not force the data length
		$data = $this->fastImageSize->getImage($filename, 0, self::JPEG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg
		if (substr($data, 0, self::SHORT_SIZE) !== self::JPEG_HEADER || $data === false)
		{
			return;
		}

		// Look through file for SOF marker
		$size = $this->getSizeInfo($data);

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->set_image_type(IMAGETYPE_JPEG);
	}

	/**
	 * Return if current data point is an SOF marker
	 *
	 * @param string $firstByte First byte to check
	 * @param string $secondByte Second byte to check
	 *
	 * @return bool True if current data point is SOF marker, false if not
	 */
	protected function isSofMarker($firstByte, $secondByte)
	{
		return $firstByte === self::SOF_START_MARKER && in_array($secondByte, $this->sofMarkers);
	}

	/**
	 * Get size info from image data
	 *
	 * @param string $data JPEG data stream
	 *
	 * @return array An array with the image's size info or an empty array if
	 *		size info couldn't be found
	 */
	protected function getSizeInfo($data)
	{
		$size = array();
		$dataLength = strlen($data);

		// Look through file for SOF marker
		for ($i = 2 * self::SHORT_SIZE; $i < $dataLength; $i++)
		{
			if ($this->isSofMarker($data[$i], $data[$i + 1]))
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

		return $size;
	}
}
