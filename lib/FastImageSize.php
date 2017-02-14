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
	/** @var StreamReader */
	protected $streamReader;

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
	);

	/** @var array Class map that links image extensions/mime types to class */
	protected $classMap;

	/** @var array An array containing the classes of supported image types */
	protected $type;

	/**
	 * Constructor for fastImageSize class
	 */
	public function __construct()
	{
		$this->streamReader = new StreamReader();

		foreach ($this->supportedTypes as $imageType => $extension)
		{
			$className = '\FastImageSize\Type\Type' . mb_convert_case(mb_strtolower($imageType), MB_CASE_TITLE);
			$this->type[$imageType] = new $className($this, $this->streamReader);

			// Create class map
			foreach ($extension as $ext)
			{
				/** @var Type\TypeInterface */
				$this->classMap[$ext] = $this->type[$imageType];
			}
		}
	}

	/**
	 * Get size array
	 *
	 * @return array|bool Size array if size could be evaluated, false if not
	 */
	protected function getSize()
	{
		return sizeof($this->size) > 1 ? $this->size : false;
	}

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
		if (!preg_match('/\.([a-z0-9]+)$/i', $file, $match) && empty($type))
		{
			$this->getImagesizeUnknownType($file);
		}
		else
		{
			$extension = (isset($match[1])) ? $match[1] : preg_replace('/.+\/([a-z0-9-.]+)$/i', '$1', $type);

			$this->getImageSizeByExtension($file, $extension);
		}

		return $this->getSize();
	}

	/**
	 * Get dimensions of image if type is unknown
	 *
	 * @param string $filename Path to file
	 */
	protected function getImagesizeUnknownType($filename)
	{
		// Grab the maximum amount of bytes we might need
		$data = $this->streamReader->getImage($filename, 0, Type\TypeJpegHelper::JPEG_MAX_HEADER_SIZE, false);

		if ($data !== false)
		{
			foreach ($this->type as $imageType)
			{
				$imageType->getSize($filename);

				if (sizeof($this->size) > 1)
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
		$this->streamReader->resetData();
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
}
