<?php

/**
 * fast-image-size image type base
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

use \FastImageSize\FastImageSize;
use FastImageSize\StreamReader;

abstract class TypeBase implements TypeInterface
{
	/** @var FastImageSize */
	protected $fastImageSize;

	/** @var StreamReader */
	protected $streamReader;

	/**
	 * Base constructor for image types
	 *
	 * @param FastImageSize $fastImageSize
	 * @param StreamReader $streamReader
	 */
	public function __construct(FastImageSize $fastImageSize, StreamReader $streamReader)
	{
		$this->fastImageSize = $fastImageSize;
		$this->streamReader = $streamReader;
	}
}
