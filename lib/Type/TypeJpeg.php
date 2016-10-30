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

use \FastImageSize\FastImageSize;
use \FastImageSize\StreamReader;
use \GuzzleHttp\Exception\RequestException;

class TypeJpeg extends TypeBase
{
	/** @var TypeJpegHelper JPEG type helper class */
	protected $jpegHelper;

	/**
	 * TypeJpeg constructor.
	 *
	 * @param FastImageSize $fastImageSize
	 * @param StreamReader $streamReader
	 */
	public function __construct(FastImageSize $fastImageSize, StreamReader $streamReader)
	{
		parent::__construct($fastImageSize, $streamReader);

		$this->jpegHelper = new TypeJpegHelper();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$this->jpegHelper->resetData();

		// Try acquiring seekable image data
		try
		{
			$this->jpegHelper->setRequestBody($this->streamReader->getSeekableImageData($filename, 0));

			// Get first part of data
			$this->jpegHelper->readDataFromStream(0);
		}
		catch (RequestException $exception)
		{
			// Do not force the data length
			$this->jpegHelper->setData($this->streamReader->getImage($filename, 0, TypeJpegHelper::JPEG_MAX_HEADER_SIZE, false));
		}

		// Check if file is jpeg
		if (!$this->jpegHelper->isValidJpeg())
		{
			return;
		}

		// Look through file for SOF marker
		if ($this->jpegHelper->isSeekable())
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
	 * Get size info from image data
	 *
	 * @return array An array with the image's size info or an empty array if
	 *		size info couldn't be found
	 */
	protected function getSizeInfo()
	{
		$size = array();
		// since we check $i + 1 we need to stop one step earlier
		$dataLength = $this->jpegHelper->dataLength();

		// Look through file for SOF marker
		for ($i = 0; $i < $dataLength; $i++)
		{
			// Only look for EXIF and XMP app marker once, other types more often
			if (!$this->jpegHelper->checkForAppMarker($i))
			{
				break;
			}

			// Break if SOF marker was evaluated
			if ($this->jpegHelper->checkForSofMarker($i, $size))
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
		$i = 0;

		// Look through file for SOF marker
		while (true)
		{
			$this->jpegHelper->readDataFromStream($i);
			// Only look for EXIF and XMP app marker once, other types more often
			if (!$this->jpegHelper->checkForAppMarker($i) && !$this->jpegHelper->readDataFromStream($i))
			{
				break;
			}

			if ($this->jpegHelper->checkForSofMarker($i, $size))
			{
				break;
			}

			$i++;
		}

		return $size;
	}
}
