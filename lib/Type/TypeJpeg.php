<?php

/**
 * fast-image-size image type jpeg
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeJpeg extends TypeBase
{
	/** @var int JPEG max header size. Headers can be bigger, but we'll abort
	 *			going through the header after this */
	const JPEG_MAX_HEADER_SIZE = 124576;

	const JPEG_CHUNK_SIZE = 8192;

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

	/** @var array JPEG APP markers */
	protected $appMarkers = array(
		"\xE0",
		"\xE1",
		"\xE2",
		"\xE3",
		"\xEC",
		"\xED",
		"\xEE",
	);

	/** @var string|bool $data JPEG data stream */
	protected $data = '';

	protected $dataLength = 0;

	/** @var \GuzzleHttp\Stream\StreamInterface Stream interface */
	protected $requestBody;

	/** @var bool Flag whether exif was found */
	protected $foundExif = false;

	/** @var bool Flag whether xmp was found */
	protected $foundXmp = false;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Try acquiring seekable image data
		try
		{
			$this->requestBody = $this->fastImageSize->getSeekableImageData($filename, 0);

			// Get first part of data
			$this->readDataFromStream(0);
		}
		catch (\GuzzleHttp\Exception\RequestException $exception)
		{
			// Do not force the data length
			$this->data = $this->fastImageSize->getImage($filename, 0, self::JPEG_MAX_HEADER_SIZE, false);
		}

		// Check if file is jpeg
		if (!$this->isValidJpeg())
		{
			return;
		}

		// Look through file for SOF marker
		if ($this->requestBody)
		{
			$size = $this->getSizeInfoFromSeekable();
		}
		else
		{
			$size = $this->getSizeInfo();
		}

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_JPEG);
	}

	/**
	 * Check whether data is valid for a JPEG file
	 *
	 * @return bool True if file is valid JPEG file, false if not
	 */
	protected function isValidJpeg()
	{
		return $this->data !== false && substr($this->data, 0, self::SHORT_SIZE) === self::JPEG_HEADER;
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
	 * Return if current data point is an APP marker
	 *
	 * @param string $firstByte First byte to check
	 * @param string $secondByte Second byte to check
	 *
	 * @return bool True if current data point is APP marker, false if not
	 */
	protected function isAppMarker($firstByte, $secondByte)
	{
		return $firstByte === self::SOF_START_MARKER && in_array($secondByte, $this->appMarkers);
	}

	/**
	 * Return if current data point is a valid APP1 marker (EXIF or XMP)
	 *
	 * @param string $firstByte First byte to check
	 * @param string $secondByte Second byte to check
	 *
	 * @return bool True if current data point is valid APP1 marker, false if not
	 */
	protected function isApp1Marker($firstByte, $secondByte)
	{
		return (!$this->foundExif || !$this->foundXmp) && $firstByte === self::SOF_START_MARKER && $secondByte === $this->appMarkers[1];
	}

	/**
	 * Get size info from image data
	 *
	 * @return array An array with the image's size info or an empty array if
	 *		size info couldn't be found
	 */
	protected function getSizeInfo()
	{
		$size = array();
		// since we check $i + 1 we need to stop one step earlier
		$this->dataLength = strlen($this->data) - 1;
		$this->foundExif = $this->foundXmp = false;

		// Look through file for SOF marker
		for ($i = 0; $i < $this->dataLength; $i++)
		{
			// Only look for EXIF and XMP app marker once, other types more often
			if (!$this->checkForAppMarker($i))
			{
				break;
			}

			// Break if SOF marker was evaluated
			if ($this->checkForSofMarker($i, $size))
			{
				break;
			}
		}

		return $size;
	}

	/**
	 * Get size info from image data
	 *
	 * @return array An array with the image's size info or an empty array if
	 *		size info couldn't be found
	 */
	protected function getSizeInfoFromSeekable()
	{
		$size = array();
		// since we check $i + 1 we need to stop one step earlier
		$this->foundExif = $this->foundXmp = false;
		$i = 0;

		// Look through file for SOF marker
		while (true)
		{
			$this->readDataFromStream($i);
			// Only look for EXIF and XMP app marker once, other types more often
			if (!$this->checkForAppMarker($i) && !$this->readDataFromStream($i))
			{
				break;
			}

			if ($this->checkForSofMarker($i, $size))
			{
				break;
			}

			$i++;
		}

		return $size;
	}

	/**
	 * Check for APP marker in data
	 *
	 * @param int $index Current data index
	 *
	 * @return bool True if searching through data should be continued, false if not
	 */
	protected function checkForAppMarker(&$index)
	{
		if ($this->isApp1Marker($this->data[$index], $this->data[$index + 1]) || $this->isAppMarker($this->data[$index], $this->data[$index + 1]))
		{
			// Extract length from APP marker
			list(, $unpacked) = unpack("H*", substr($this->data, $index + self::SHORT_SIZE, 2));

			$length = hexdec(substr($unpacked, 0, 4));

			$this->setApp1Flags($this->data[$index + 1]);

			// Skip over length of APP header
			$index += (int) $length;

			// Make sure we don't exceed the data length
			if ($index >= $this->dataLength)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check for valid SOF marker at specified index and extract size info if
	 *		marker is valid
	 *
	 * @param int $index Data index
	 * @param array $size Size array
	 * @return bool True if SOF marker was found, false if not
	 */
	protected function checkForSofMarker($index, &$size)
	{
		if ($this->isSofMarker($this->data[$index], $this->data[$index + 1]))
		{
			// Extract size info from SOF marker
			list(, $unpacked) = unpack("H*", substr($this->data, $index + self::LONG_SIZE + 1, self::LONG_SIZE));

			// Get width and height from unpacked size info
			$size = array(
				'width'		=> hexdec(substr($unpacked, 4, 4)),
				'height'	=> hexdec(substr($unpacked, 0, 4)),
			);

			return true;
		}

		return false;
	}

	/**
	 * Set APP1 flags for specified data point
	 *
	 * @param string $data Data point
	 */
	protected function setApp1Flags($data)
	{
		if (!$this->foundExif)
		{
			$this->foundExif = $data === $this->appMarkers[1];
		}
		else if (!$this->foundXmp)
		{
			$this->foundXmp = $data === $this->appMarkers[1];
		}
	}

	/**
	 * Read data from request body
	 *
	 * @param $index
	 * @return bool
	 */
	protected function readDataFromStream($index)
	{
		$this->dataLength = strlen($this->data) - 1;
		if ($index >= $this->dataLength && !$this->requestBody->eof())
		{
			$seekOffset = $index - $this->dataLength - 1;

			// Seek and add junk data if we're jumping forward inside the string
			if ($seekOffset > 0)
			{
				$this->requestBody->seek($seekOffset);
				$this->data .= str_repeat('0', $seekOffset);
			}
			$this->data .= $this->requestBody->read(self::JPEG_CHUNK_SIZE);
		}

		if ($this->requestBody->eof() || $index > self::JPEG_MAX_HEADER_SIZE)
		{
			return false;
		}
		return true;
	}
}
