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
 * Specific GD Implementation
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Image
 */
class GDImage extends AbstractImage
{
	/**
	 * GDImage constructor
	 */
	public function __construct( $sPath ) {
		if(!extension_loaded('gd')) {
			throw new Exception('You do not have the GD Library installed.  This class requires the GD library to function properly.');
		}

		parent::__construct( $sPath );
	}

	/**
	 * @see AbstractImage::buildResource
	 */
	protected function buildResource( $sPath ) {
		$this->resource = imagecreatefromstring(file_get_contents($sPath));
		$this->width = imagesx($this->resource);
		$this->height = imagesy($this->resource);
	}

	/**
	 * @see AbstractImage::buildResource
	 */
	protected function destroyResource() {
		if(is_resource($this->resource))
			imagedestroy($this->resource);
	}

	/**
	 * @see AbstractImage::resize
	 */
	public function resize( $iWidth = null, $iHeight = null ) {
		//If null given, compute a valid size
		if( is_null( $iWidth ) )
			$iWidth = $this->width*$iHeight/$this->height;
		if( is_null( $iHeight ) )
			$iHeight = $this->height*$iWidth/$this->width;

		//Build result resource
		$oCurrent = $this->resource;
		if(function_exists("ImageCreateTrueColor"))
			$oResized = ImageCreateTrueColor($iWidth,$iHeight);
		else
			$oResized = ImageCreate($iWidth,$iHeight);
		//Compute resize
		imagecopyresampled( $oResized, $this->resource, 0, 0, 0, 0, $iWidth, $iHeight, $this->width, $this->height );

		//Destroy previous resource and fill properties
		imagedestroy($oCurrent);
		$this->resource = $oResized;
		$this->width = $iWidth;
		$this->height = $iHeight;
	}

	/**
	 * @see AbstractImage::crop
	 */
	public function crop($iX, $iY, $iWidth, $iHeight) {
		//Be sure that the requested crop is inside the current image
		if( $iX + $iWidth > $this->width || $iY + $iHeight > $this->height ) {
			throw new Exception( 'Crop area requested is outside the current picture !!');
		}

		//Build result resource
		$oCurrent = $this->resource;
		if(function_exists("ImageCreateTrueColor")) {
			$oResized = ImageCreateTrueColor($iWidth,$iHeight);
		} else {
			$oResized = ImageCreate($iWidth,$iHeight);
		}
		//Compute resize
		imagecopyresampled( $oResized, $this->resource, 0, 0, $iX, $iY, $iWidth, $iHeight, $iWidth, $iHeight );

		//Destroy previous resource and fill properties
		imagedestroy($oCurrent);
		$this->resource = $oResized;
		$this->width = $iWidth;
		$this->height = $iHeight;
	}

	/**
	 * @see AbstractImage::save
	 */
	public function save( $sPath ) {
		//GD Can't save image without using a type function
		switch ($this->type) {
			case IMAGETYPE_PNG:
				imagepng($this->resource,$sPath);
				break;
			case IMAGETYPE_GIF:
				imagegif($this->resource,$sPath);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($this->resource,$sPath,100);
				break;
		}
	}
}