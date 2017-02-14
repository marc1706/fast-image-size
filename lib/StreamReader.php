<?php

/**
 * fast-image-size stream reader
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize;

use FastImageSize\Type\TypeJpegHelper;

class StreamReader
{
	/** @var bool Flag whether allow_url_fopen is enabled */
	protected $isFopenEnabled = false;

	/** @var string Data retrieved from remote */
	public $data = '';

	/**
	 * Constructor for fastImageSize class
	 */
	public function __construct()
	{
		$iniGet = new \bantu\IniGetWrapper\IniGetWrapper();
		$this->isFopenEnabled = $iniGet->getBool('allow_url_fopen');
	}

	/**
	 * Reset stream reader data
	 */
	public function resetData()
	{
		$this->data = '';
	}

	/**
	 * Get force length data
	 *
	 * @param int $offset
	 * @param int $length
	 * @return bool|string Returned data if long enough, false if not
	 */
	protected function getForcedLengthData($offset, $length)
	{
		return (strlen($this->data) < ($length + $offset)) ? false : substr($this->data, $offset, $length);
	}

	/**
	 * Get image from specified path/source
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $length Maximum length that should be read
	 * @param bool $forceLength True if the length needs to be the specified
	 *			length, false if not. Default: true
	 *
	 * @return false|string Image data or false if result was empty
	 */
	public function getImage($filename, $offset, $length, $forceLength = true)
	{
		if (empty($this->data))
		{
			$this->getImageData($filename, $offset, $length);
		}

		// Force length to expected one. Return false if data length
		// is smaller than expected length
		if ($forceLength === true)
		{
			return $this->getForcedLengthData($offset, $length);
		}

		return empty($this->data) ? false : $this->data;
	}

	/**
	 * Get image data for specified filename with offset and length
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $length Maximum length that should be read
	 */
	protected function getImageData($filename, $offset, $length)
	{
		// Check if we don't have a valid scheme according to RFC 3986 and
		// try to use file_get_contents in that case
		if (preg_match('#^([a-z][a-z0-9+\-.]+://)#i', $filename))
		{
			try
			{
				$body = $this->getSeekableImageData($filename, $offset);

				while ($body !== null && !$body->eof())
				{
					if (!$this->readDataFromBody($body, $length))
					{
						break;
					}
				}
			}
			catch (\GuzzleHttp\Exception\RequestException $exception)
			{
				// Silently fail in case of issues during guzzle request
			}
		}

		$this->getImageDataFopen($filename, $offset, $length);
	}

	/**
	 * Read data from guzzle request body
	 *
	 * @param \GuzzleHttp\Stream\StreamInterface $body Request body as stream
	 * @param int $length Requested length for file
	 * @return bool True while reading and not having reached the end of the
	 *		targeted length, false if end of targeted length has been reached
	 */
	protected function readDataFromBody(\GuzzleHttp\Stream\StreamInterface $body, $length)
	{
		$readLength = min($length - strlen($this->data), TypeJpegHelper::JPEG_CHUNK_SIZE);
		$this->data .= $body->read($readLength);
		if ($readLength < TypeJpegHelper::JPEG_CHUNK_SIZE || strlen($this->data == $readLength))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get image data using file get contents if data is empty and
	 *		allow_url_fopen is enabled
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $length Maximum length that should be read
	 */
	protected function getImageDataFopen($filename, $offset, $length)
	{
		if ($this->isFopenEnabled && empty($this->data))
		{
			$this->data = @file_get_contents($filename, null, null, $offset, $length);
		}
	}

	/**
	 * Get seekable image data in form of Guzzle stream interface
	 *
	 * @param string $filename Filename / URL to get
	 * @param int $offset Offset for response body
	 * @return \GuzzleHttp\Stream\StreamInterface|null Stream interface of
	 *		requested image or null if it could not be retrieved
	 */
	public function getSeekableImageData($filename, $offset)
	{
		$guzzleClient = new \GuzzleHttp\Client();
		// Set stream to true to not read full file data during request
		$response = $guzzleClient->get($filename, ['stream' => true]);

		$body = $response->getBody();

		if ($offset > 0 && !$body->eof())
		{
			$body->seek($offset);
		}

		return $body;
	}
}
