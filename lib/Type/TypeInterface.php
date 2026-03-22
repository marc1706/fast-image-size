<?php

/**
 * fast-image-size image type interface
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

use FastImageSize\ImageReader;

interface TypeInterface
{
	/** @var int 4-byte long size */
	const LONG_SIZE = 4;

	/** @var int 2-byte short size */
	const SHORT_SIZE = 2;

	/**
	 * Get size of supplied image
	 *
	 * @param string $filename File name of image
	 * @param ImageReader $imageReader Image reader
	 *
	 * @return array|null Size info or null if failed
	 */
	public function getSize(string $filename, ImageReader $imageReader): ?array ;
}
