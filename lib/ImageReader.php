<?php

/**
 * fast-image-size image reader
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize;

class ImageReader
{
	/** @var string Data retrieved from remote */
	protected $data = '';

	/** @var array Stream context options for retrieving remote images */
	protected $streamContextOptions = [
		'http' => [
			'timeout' => 5.0,
			'ignore_errors' => true,
		],
	];

	/**
	 * Get image from specified path/source
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $length Maximum length that should be read, must be greater than 0
	 * @param bool $forceLength True if the length needs to be the specified
	 *			length, false if not. Default: true
	 *
	 * @return false|string Image data or false if result was empty
	 */
	public function getImage(string $filename, int $offset, int $length, bool $forceLength = true)
	{
		if (empty($this->data))
		{
			$this->data = $this->retrieveImageData($filename, $offset, $length) ?: '';
		}

		// Force length to expected one. Return false if data length
		// is smaller than expected length
		if ($forceLength === true)
		{
			return (strlen($this->data) < $length) ? false : substr($this->data, $offset, $length) ;
		}

		return empty($this->data) ? false : $this->data;
	}

	/**
	 * Retrieve image data from specified path/source
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $max_length Maximum length that should be read
	 *
	 * @return false|string Image data or false if result was empty
	 */
	protected function retrieveImageData(string $filename, int $offset, int $max_length)
	{
		$context = $this->create_stream_context();

		// Use @ to suppress warnings from connection/DNS/SSL failures
		$content = @file_get_contents($filename, false, $context, $offset, $max_length);

		if (function_exists('http_get_last_response_headers'))
		{
			$http_response_header = http_get_last_response_headers();
		}

		if (isset($http_response_header))
		{
			// Find the LAST occurrence of "HTTP/" in the headers array
			$statusLine = '';
			foreach (array_reverse($http_response_header) as $header)
			{
				if (strpos($header, 'HTTP/') === 0)
				{
					$statusLine = $header;
					break;
				}
			}

			return strpos($statusLine, '200 OK') !== false ? $content : false;
		}

		return $content;
	}

	/**
	 * Set stream context options for retrieving remote images
	 *
	 * @param array $options Stream context options
	 */
	public function setStreamContextOptions(array $options)
	{
		$this->streamContextOptions = $options;
	}

	/**
	 * Create stream context for retrieving remote images
	 *
	 * @return resource Stream context
	 */
	protected function create_stream_context()
	{
		return stream_context_create($this->streamContextOptions);
	}

	/**
	 * Reset image data
	 */
	public function reset(): void
	{
		$this->data = '';
	}
}
