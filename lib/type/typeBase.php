<?php

/**
 * fast-image-size image type base
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fastImageSize\type;

use \fastImageSize\fastImageSize;

abstract class typeBase implements typeInterface
{
	/** @var fastImageSize */
	protected $fastImageSize;

	/**
	 * Base constructor for image types
	 *
	 * @param fastImageSize $fastImageSize
	 */
	public function __construct(fastImageSize $fastImageSize)
	{
		$this->fastImageSize = $fastImageSize;
	}
}
