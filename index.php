<?php
/**
 * This file is part of CDNThumbnailer.
 * For the full copyright and license information, please view the LICENCE
 * file that was distributed with this source code.
 *
 * @license See the LICENCE file distributed with the source code
 * @author Stephane HULARD <s.hulard@chstudio.fr>
 * @package Default
 */

//Require configuration
require_once dirname(__FILE__).'/config/config.inc.php';

//File path to be resized
$sPath = $_GET['path'];
//Requested format (11x11)
$aFormat = explode('x', $_GET['format']);
//Image url scheme if image is an external one
$sScheme = isset($_GET['scheme'])?$_GET['scheme']:null;

$query = array_diff_key($_GET, array_flip(array('path', 'format', 'scheme')));
if( count($query) > 0 ) {
	$sPath .= '?'.http_build_query($query);
}

$sCache = realpath(CACHE_FOLDER).(isset($sScheme)?'/'.$sScheme:"");
$sCleanedPath = str_replace(array('?','=','&'), array('_','_','_'), $sPath);

//Define folder structure original contains base files and format folder are in the cache
$sOriginalFile = $sCache.'/original/'.$sCleanedPath;
$sOriginalDir = dirname($sOriginalFile);

$sResizedFile = $sCache.'/'.$_GET['format'].'/'.$sCleanedPath;
$sResizedDir = dirname($sResizedFile);

//If the original file does not exists
if( !is_file($sOriginalFile) ) {
	//If the scheme is defined we try to download image
	if( !is_null($sScheme) ) {
		//Initialize curl handler and make the request
		$oRequest = curl_init($sScheme.'://'.$sPath);
		ob_start();
		curl_exec($oRequest);
		$sContent = ob_get_clean();

		//Retrieve last request details
		$aCurlInfo = curl_getinfo($oRequest);
		//If last request is a "200 OK", continue
		if( isset($aCurlInfo['http_code']) && $aCurlInfo['http_code'] == 200 ) {
			if( !is_dir($sOriginalDir) ) {
				$umask = umask(0);
				mkdir($sOriginalDir, 0777, true);
				umask($umask);
			}
			file_put_contents($sOriginalFile, $sContent);
		//Else, the file can't be retrieved so, send a 404 header
		} else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
			exit();
		}
	//The scheme is not defined and original file is not here, file does not exists
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
		exit();
	}
}

//If resized folder does not exists we add it
if( !is_dir($sResizedDir) ) {
	$umask = umask(0);
	mkdir($sResizedDir, 0777, true);
	umask($umask);
}

try
{
	//If image magick use it
	if( extension_loaded('imagick') ) {
		require_once dirname(__FILE__).'/src/Image/ImagickImage.php';
		$oResized = new ImagickImage($sOriginalFile);
	//Else just use GD
	} else {
		require_once dirname(__FILE__).'/src/Image/GDImage.php';
		$oResized = new GDImage($sOriginalFile);
	}

	if( !is_file($sResizedFile) ) {
		//Use built Image manipulator to resize and save the new file
		$oResized->resizeAndCrop($aFormat[0], $aFormat[1]);
		$oResized->save($sResizedFile);
	}

	//Build valid HTTP Headers for cache and content type/length for a correct navigator management
	$expires = 60*60*24*14;
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
	header("Pragma: public");
	header("Cache-Control: maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($sResizedFile)).' GMT');
	header('Content-Type: '.image_type_to_mime_type($oResized->getType()));
	header('Content-Length: '.filesize($sResizedFile));
	echo file_get_contents($sResizedFile);
}
//If errors are sent during resizing send HTTP 500 Errors
catch( ImagickException $oError )
{
	header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
	echo $oError->getMessage();
}
catch( Exception $oError )
{
	header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
	echo $oError->getMessage();
}