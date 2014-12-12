<?php
/**
 * This file is part of CDNThumbnailer.
 * For the full copyright and license information, please view the LICENCE
 * file that was distributed with this source code.
 *
 * @license See the LICENCE file distributed with the source code
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Image
 */

/**
 * Factory for Image implementation
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Image
 */
final class ImageFactory
{
	protected function __construct() {}

	/**
	 * Initialize a valid AbstractImage implementation
	 * @param string $path
	 * @return AbstractImage
	 */
	public static function build($path) {
		//If image magick use it
		if( extension_loaded('imagick') ) {
			require_once dirname(__FILE__).'/ImagickImage.php';
			$oResized = new ImagickImage($path);
		//Else just use GD
		} elseif( extension_loaded('gd') ) {
			require_once dirname(__FILE__).'/GDImage.php';
			$oResized = new GDImage($path);
		} else {
			throw new RuntimeException('There is no valid implementation to be used to manipulate image (GD, Imagick)');
		}

		return $oResized;
	}
}