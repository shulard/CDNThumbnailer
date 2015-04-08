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

require_once dirname(__FILE__).'/AbstractImage.php';

/**
 * Specific ImageMagick Implementation
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Image
 */
class ImagickImage extends AbstractImage
{
	/**
	 * Loaded resource
	 * @var Imagick
	 */
	protected $resource;

	/**
	 * ImagickImage constructor
	 */
	public function __construct( $sPath ) {
		if(!extension_loaded('imagick')) {
			throw new Exception('You do not have the ImageMagick PECL extension installed. This class requires this extension to function properly.');
		}

		parent::__construct( $sPath );
	}

	/**
	 * @see AbstractImage::buildResource
	 */
	protected function buildResource( $sPath ) {
		$this->resource = new Imagick($sPath);
		//Limit Imagick to single thread -- https://bugs.php.net/bug.php?id=61122
		$this->resource->setResourceLimit(6, 1);
		$this->width = $this->resource->getImageWidth();
		$this->height = $this->resource->getImageHeight();
	}

	/**
	 * @see AbstractImage::buildResource
	 */
	protected function destroyResource() {
		if( $this->resource instanceof Imagick ) {
			$this->resource->clear();
		}
	}

	/**
	 * @see AbstractImage::resizeAndCrop
	 */
	public function resizeAndCrop( $iWidth, $iHeight ) {
		$this->resource->cropThumbnailImage($iWidth, $iHeight);
	}

	/**
	 * @see AbstractImage::crop
	 */
	public function crop($iX, $iY, $iWidth, $iHeight) {
		//Be sure that the requested crop is inside the current image
		if( $iX + $iWidth > $this->width || $iY + $iHeight > $this->height ) {
			throw new Exception( 'Crop area requested is outside the current picture !!');
		}

		$this->resource->cropImage($iWidth, $iHeight, $iX, $iY);
		$this->width = $iWidth;
		$this->height = $iHeight;
	}

	/**
	 * @see AbstractImage::resize
	 */
	public function resize( $iWidth = null, $iHeight = null ) {
		//If null given, compute a valid size
		if( is_null( $iWidth ) ) {
			$iWidth = $this->width*$iHeight/$this->height;
		}
		if( is_null( $iHeight ) ) {
			$iHeight = $this->height*$iWidth/$this->width;
		}

		$this->resource->scaleImage($iWidth, $iHeight);
		$this->width = $iWidth;
		$this->height = $iHeight;
	}

	/**
	 * @see AbstractImage::save
	 */
	public function save( $sPath ) {
		$this->resource->writeImage($sPath);
	}
}