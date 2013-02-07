<?php
/**
 * This file is part of CDNThumbnailer.
 * For the full copyright and license information, please view the LICENne
 * file that was distributed with this source code.
 *
 * @license See the LICENCE file distributed with the source code
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Image
 */

/**
 * Standard image representation
 * Define a canvas for different image types
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Image
 */
abstract class AbstractImage
{
	/**
	 * Image width
	 * @var Integer
	 */
	protected $width;

	/**
	 * Image height
	 * @var Integer
	 */
	protected $height;

	/**
	 * Image type. Can only be one of the supported format (JPG, PNG, GIF)
	 * Use the predefined constant to decode the type
	 * @var Integer
	 */
	protected $type;

	/**
	 * Image resource
	 * Word resource is used for the current image object (GD resource, Imagick instance, ...)
	 */
	protected $resource;

	/**
	 * AbstractImage constructor
	 */
	public function __construct( $sPath ) {
		$this->width = 0;
		$this->height = 0;
		$this->type = 0;
		$this->resource = null;

		if( !is_file($sPath) || !is_readable( $sPath ))
			throw new Exception("File path given is not a valid one!!");
			
		//Initialize image resource
		$this->retrieveType($sPath);
		$this->buildResource($sPath);
	}

	/**
	 * AbstractImage destructor
	 */
	public function __destruct() {
		//Free resource memory (depends on which library is used)
		$this->destroyResource();

		unset( $this->resource );
	}

	/**
	 * Width accessor
	 * @return Integer
	 */
	public function getWidth() {
		return $this->width;
	}
	/**
	 * Height accessor
	 * @return Integer
	 */
	public function getHeight() {
		return $this->height;
	}
	/**
	 * Type accessor
	 * @return Integer
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Retrieve image type for the given file
	 * @param String $sPath The path to be loaded as Image
	 */
	protected function retrieveType($sPath) {
		//If method to extract image type exists, use it
		if( function_exists("exif_imagetype") )
			$this->type = exif_imagetype($sPath);
		//Else use extension detection
		else {
			$sExtension = strtolower(substr($sPath, strrpos($sPath, '.') + 1 ));
			switch ($sExtension) {
				case 'png':
					$this->type = IMAGETYPE_PNG;
					break;
				case 'jpeg':
				case 'jpg':
					$this->type = IMAGETYPE_JPEG;
					break;
				case 'gif':
					$this->type = IMAGETYPE_GIF;
					break;
			}
		}

		if( !in_array($this->type, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG)) )
			throw new Exception('Image type given is not a valid one, only GIF, PNG and JPG are allowed');
	}

	/**
	 * Specific resize & crop process
	 * Try to get the most important part of the picture
	 * @param Integer $iWidth Width of the result image
	 * @param Integer $iHeight Height of the result image
	 */
	public function resizeAndCrop( $iWidth, $iHeight ) {
		$iRatio = (int) $iWidth / (int) $iHeight;

    // if media is a square
    if ($iRatio == 1)
    {
      if ($this->width > $this->height)
        $this->resize(null, $iHeight);
      else
        $this->resize($iWidth, null);
    }
    // horizontal format
    elseif ($iRatio > 1)
    {
      $iTmpRatio = $this->width / $iWidth;
      if (($this->height / $iTmpRatio) < $iHeight)
        $this->resize(null, $iHeight);
      else
        $this->resize($iWidth, null);
    }
    // vertical format
    elseif ($iRatio < 1)
    {
      $iTmpRatio = $this->height / $iHeight;
      if (($this->width / $iTmpRatio) < $iWidth)
        $this->resize($iWidth, '');
      else
        $this->resize('', $iHeight);
    }

    $this->crop(
    	$this->width/2-$iWidth/2,
			$this->height/2-$iHeight/2,
			$iWidth,
    	$iHeight
    );
	}

	/**
	 * Build an image resource from a given file
	 * @param String $sPath The path to be loaded as Image
	 */
	abstract protected function buildResource( $sPath );

	/**
	 * Destroy image resource if loaded
	 */ 
	abstract protected function destroyResource();

	/**
	 * Resize the picture at a given size
	 * @param Integer $iWidth Width of the result image
	 * @param Integer $iHeight Height of the result image
	 */ 
	abstract public function resize( $iWidth = null, $iHeight = null );
	/**
	 * Crop the picture from center at a given size
	 * @param Integer $iX X position to start crop
	 * @param Integer $iY Y position to start crop
	 * @param Integer $iWidth Width to crop
	 * @param Integer $iHeight Height to crop
	 */ 
	abstract public function crop( $iX, $iY, $iWidth, $iHeight );

	/**
	 * Save the current image to the given filepath
	 * @param String $sPath path to use to save image
	 */
	abstract public function save( $sPath );
}