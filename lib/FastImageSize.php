<?php

/**
 * fast-image-size base class
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize;

class FastImageSize
{
	/** @var array Size info that is returned */
	protected $size = array();

	/** @var string Data retrieved from remote */
	protected $data = '';

	/** @var array List of supported image types and associated image types */
	protected $supportedTypes = array(
		'png'	=> array('png'),
		'gif'	=> array('gif'),
		'jpeg'	=> array(
				'jpeg',
				'jpg',
				'jpe',
				'jif',
				'jfif',
				'jfi',
			),
		'jp2'	=> array(
				'jp2',
				'j2k',
				'jpf',
				'jpg2',
				'jpx',
				'jpm',
			),
		'psd'	=> array(
				'psd',
				'photoshop',
			),
		'bmp'	=> array('bmp'),
		'tif'	=> array(
				'tif',
				'tiff',
			),
		'wbmp'	=> array(
				'wbm',
				'wbmp',
				'vnd.wap.wbmp',
			),
		'iff'	=> array(
				'iff',
				'x-iff',
		),
		'ico'	=> array(
				'ico',
				'vnd.microsoft.icon',
				'x-icon',
				'icon',
		),
		'webp'	=> array(
				'webp',
		)
	);

	/** @var array Class map that links image extensions/mime types to class */
	protected $classMap;

	/** @var array An array containing the classes of supported image types */
	protected $type;

	/** @var array Stream context options for retrieving remote images */
	protected $streamContextOptions = [
		'http' => [
			'timeout' => 5.0,
			'ignore_errors' => true,
		],
	];

	/**
	 * Get image dimensions of supplied image
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $type Mimetype of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	public function getImageSize($file, $type = '')
	{
		// Reset values
		$this->resetValues();

		// Treat image type as unknown if extension or mime type is unknown
		$fileExtension = $this->getFileExtension($file);
		if (empty($fileExtension) && empty($type))
		{
			$this->getImagesizeUnknownType($file);
		}
		else
		{
			$extension = $this->selectImageExtension($fileExtension, $type);

			$this->getImageSizeByExtension($file, $extension);
			
			if (count($this->size) < 2)
			{
				$this->data = '';
				$this->getImagesizeUnknownType($file);
			}
		}

		return count($this->size) > 1 ? $this->size : false;
	}

	/**
	 * Get file extension from supplied file path
	 *
	 * @param string $file Path to file
	 * @return string File extension if found, empty string if not
	 */
	protected function getFileExtension(string $file): string
	{
		if (preg_match('/\.([a-z0-9]+)$/i', $file, $match))
		{
			return $match[1];
		}

		return '';
	}

	/**
	 * Select image extension to use for retrieving dimensions
	 *
	 * @param string $fileExtension File extension from file path
	 * @param string $type Mimetype of image
	 * @return string Image extension to use for retrieving dimensions
	 */
	protected function selectImageExtension(string $fileExtension, string $type): string
	{
		return (empty($type) && !empty($fileExtension)) ? $fileExtension : preg_replace('/.+\/([a-z0-9-.]+)$/i', '$1', $type);
	}

	/**
	 * Get dimensions of image if type is unknown
	 *
	 * @param string $filename Path to file
	 */
	protected function getImagesizeUnknownType($filename)
	{
		// Grab the maximum amount of bytes we might need
		$data = $this->getImage($filename, 0, Type\TypeJpeg::JPEG_MAX_HEADER_SIZE, false);

		if ($data !== false)
		{
			$this->loadAllTypes();
			foreach ($this->type as $imageType)
			{
				$imageType->getSize($filename);

				if (count($this->size) > 1)
				{
					break;
				}
			}
		}
	}

	/**
	 * Get image size by file extension
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $extension Extension/type of image
	 */
	protected function getImageSizeByExtension($file, $extension)
	{
		$extension = strtolower($extension);
		$this->loadExtension($extension);
		if (isset($this->classMap[$extension]))
		{
			$this->classMap[$extension]->getSize($file);
		}
	}

	/**
	 * Reset values to default
	 */
	protected function resetValues()
	{
		$this->size = array();
		$this->data = '';
	}

	/**
	 * Set mime type based on supplied image
	 *
	 * @param int $type Type of image
	 */
	public function setImageType($type)
	{
		$this->size['type'] = $type;
	}

	/**
	 * Set size info
	 *
	 * @param array $size Array containing size info for image
	 */
	public function setSize($size)
	{
		$this->size = $size;
	}

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
	 * Load all supported types
	 */
	protected function loadAllTypes()
	{
		foreach ($this->supportedTypes as $imageType => $extension)
		{
			$this->loadType($imageType);
		}
	}

	/**
	 * Load an image type by extension
	 *
	 * @param string $extension Extension of image
	 */
	protected function loadExtension($extension)
	{
		if (isset($this->classMap[$extension]))
		{
			return;
		}
		foreach ($this->supportedTypes as $imageType => $extensions)
		{
			if (in_array($extension, $extensions, true))
			{
				$this->loadType($imageType);
			}
		}
	}

	/**
	 * Load an image type
	 *
	 * @param string $imageType Mimetype
	 */
	protected function loadType($imageType)
	{
		if (isset($this->type[$imageType]))
		{
			return;
		}

		$className = '\FastImageSize\Type\Type' . mb_convert_case(mb_strtolower($imageType), MB_CASE_TITLE);
		$this->type[$imageType] = new $className($this);

		// Create class map
		foreach ($this->supportedTypes[$imageType] as $ext)
		{
			/** @var Type\TypeInterface */
			$this->classMap[$ext] = $this->type[$imageType];
		}
	}
}
