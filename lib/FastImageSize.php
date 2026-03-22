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

use FastImageSize\Type\TypeInterface;

class FastImageSize
{
	/** @var array Size info that is returned */
	protected $size = array();

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

	/** @var ImageReader */
	protected $imageReader;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->imageReader = new ImageReader();
	}

	/**
	 * Get image dimensions of supplied image
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $type Mimetype of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	public function getImageSize(string $file, string $type = '')
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
				$this->imageReader->reset();
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
	protected function getImagesizeUnknownType(string $filename)
	{
		// Grab the maximum amount of bytes we might need
		$data = $this->getImage($filename, 0, Type\TypeJpeg::JPEG_MAX_HEADER_SIZE, false);

		if ($data !== false)
		{
			$this->loadAllTypes();
			foreach ($this->type as $imageType)
			{
				$result = $imageType->getSize($filename, $this->imageReader);
				if ($result)
				{
					$this->size = $result;
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
	protected function getImageSizeByExtension(string $file, string $extension)
	{
		$extension = strtolower($extension);
		$this->loadExtension($extension);
		if (isset($this->classMap[$extension]))
		{
			$result = $this->type[$this->classMap[$extension]]->getSize($file, $this->imageReader);
			if ($result)
			{
				$this->size = $result;
			}
		}
	}

	/**
	 * Reset values to default
	 */
	protected function resetValues()
	{
		$this->size = array();
		$this->imageReader->reset();
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
		return $this->imageReader->getImage($filename, $offset, $length, $forceLength);
	}

	/**
	 * Set stream context options for retrieving remote images
	 *
	 * @param array $options Stream context options
	 */
	public function setStreamContextOptions(array $options)
	{
		$this->imageReader->setStreamContextOptions($options);
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
	protected function loadExtension(string $extension)
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
	protected function loadType(string $imageType): void
	{
		if (isset($this->type[$imageType]))
		{
			return;
		}

		$typeInstance = $this->loadTypeClass($imageType);
		if (!$typeInstance)
		{
			return;
		}

		$this->type[$imageType] = $typeInstance;

		// Create class map
		foreach ($this->supportedTypes[$imageType] as $ext)
		{
			$this->classMap[$ext] = $imageType;
		}
	}

	/**
	 * Load class for an image type
	 *
	 * @param string $imageType Mimetype
	 * @return TypeInterface|null Instance of type class if successful, null if not
	 */
	protected function loadTypeClass(string $imageType): ?TypeInterface
	{
		$className = '\FastImageSize\Type\Type' . ucfirst($imageType);
		$filePath = __DIR__ . '/Type/Type' . ucfirst($imageType) . '.php';
		if (!class_exists($className, false))
		{
			if (is_file($filePath))
			{
				require_once $filePath;
			}
			else
			{
				return null;
			}
		}

		return new $className();
	}
}
