<?php

/**
 * fast-image-size base class
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize;

class fastImageSize
{
	/** @var array Size info that is returned */
	protected $size = array();

	/** @var string Data retrieved from remote */
	protected $data = '';

	/** @var array List of supported image types and associated image types */
	protected $supported_types = array(
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
	protected $class_map;

	/** @var array An array containing the classes of supported image types */
	protected $type;

	/**
	 * Constructor for fastImageSize class
	 */
	public function __construct()
	{
		foreach ($this->supported_types as $image_type => $extension)
		{
			$class_name = '\fastImageSize\type\type' . ucfirst(strtolower($image_type));
			$this->type[$image_type] = new $class_name($this);

			// Create class map
			foreach ($extension as $ext)
			{
				/** @var type\typeInterface */
				$this->class_map[$ext] = $this->type[$image_type];
			}
		}
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
		$this->reset_values();

		// Treat image type as unknown if extension or mime type is unknown
		if (!preg_match('/\.([a-z0-9]+)$/i', $file, $match) && empty($type))
		{
			$this->get_imagesize_unknown_type($file);
		}
		else
		{
			$extension = (isset($match[1])) ? $match[1] : preg_replace('/.+\/([a-z0-9-.]+)$/i', '$1', $type);

			$this->getImageSizeByExtension($file, $extension);
		}

		return sizeof($this->size) > 1 ? $this->size : false;
	}

	/**
	 * Get dimensions of image if type is unknown
	 *
	 * @param string $filename Path to file
	 */
	protected function get_imagesize_unknown_type($filename)
	{
		// Grab the maximum amount of bytes we might need
		$data = $this->get_image($filename, 0, type\typeJpeg::JPG_MAX_HEADER_SIZE, false);

		if ($data !== false)
		{
			foreach ($this->type as $image_type)
			{
				$image_type->getSize($filename);

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
		if (isset($this->class_map[$extension]))
		{
			$this->class_map[$extension]->getSize($file);
		}
	}

	/**
	 * Reset values to default
	 */
	protected function reset_values()
	{
		$this->size = array();
		$this->data = '';
	}

	/**
	 * Set mime type based on supplied image
	 *
	 * @param int $type Type of image
	 */
	public function set_image_type($type)
	{
		$this->size['type'] = $type;
	}

	/**
	 * Set size info
	 *
	 * @param array $size Array containing size info for image
	 */
	public function set_size($size)
	{
		$this->size = $size;
	}

	/**
	 * Get image from specified path/source
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $length Maximum length that should be read
	 * @param bool $force_length True if the length needs to be the specified
	 *			length, false if not. Default: true
	 *
	 * @return bool|string Image data or false if result was empty
	 */
	public function get_image($filename, $offset, $length, $force_length = true)
	{
		if (empty($this->data))
		{
			$this->data = @file_get_contents($filename, null, null, $offset, $length);
		}

		// Force length to expected one. Return false if data length
		// is smaller than expected length
		if ($force_length === true)
		{
			return (strlen($this->data) < $length) ? false : substr($this->data, $offset, $length) ;
		}

		return empty($this->data) ? false : $this->data;
	}
}
