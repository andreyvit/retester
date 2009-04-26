<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

/**
 * This class manages images and thumbnails.
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class Image {

/* PRIVATE PROPERTIES ************************************************************************** */

	/**
	 * image file path
	 *
	 * @var string
	 */
	var $file;

	/**
	 * desired image width
	 *
	 * @var integer
	 */
	var $width;

	/**
	 * desired image height
	 *
	 * @var integer
	 */
	var $height;

	/**
	 * image type
	 *
	 * @var integer
	 */
	var $type;

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param string $file			image file path
	 * @param integer $width		optional: desired image width
	 * @param integer $height		optional: desired image height
	 * @return Image
	 */
	function Image($file, $width = null, $height = null) {
		$this->file = $file;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * view image with desired width and height
	 */
	function view() {
		if($this->file == '') return;
		list($srcWidth, $srcHeight, $this->type) = @getimagesize($this->file);

		if($srcWidth > $this->width || $srcHeight > $this->height) {
			$srcImg = '';

			switch($this->type) {

				case 1:
					if(function_exists('ImageCreateFromGIF')) {
						$srcImg = @ImageCreateFromGIF($this->file);
					}
					break;

				case 2:
					if(function_exists('ImageCreateFromJPEG')) {
						$srcImg = @ImageCreateFromJPEG($this->file);
					}
					break;

				case 3:
					if(function_exists('ImageCreateFromPNG')) {
						$srcImg = @ImageCreateFromPNG($this->file);
					}
					break;
			}

			if($srcImg) {
				if($this->type != 1 && function_exists('ImageCreateTrueColor')) {
					$dstImg = @ImageCreateTrueColor($this->width, $this->height);
				}
				else $dstImg = @ImageCreate($this->width, $this->height);

				if(function_exists('ImageCopyResampled')) {
					@ImageCopyResampled($dstImg, $srcImg, 0, 0, 0, 0, $this->width, $this->height, $srcWidth, $srcHeight);
				}
				else @ImageCopyResized($dstImg, $srcImg, 0, 0, 0, 0, $this->width, $this->height, $srcWidth, $srcHeight);

				$this->send($dstImg);

				ImageDestroy($srcImg);
				ImageDestroy($dstImg);
			}
			else $this->send();
		}
		else $this->send();
	}

/* PRIVATE METHODS ***************************************************************************** */

	/**
	 * send image
	 *
	 * @param resource $img		image resource
	 */
	function send($img = null) {
		switch($this->type) {

			case 1:
				if($img && function_exists('ImageGIF')) {
					header('Content-type: image/gif');
					@ImageGIF($img);
				}
				else if($img && function_exists('ImagePNG')) {
					header('Content-type: image/png');
					@ImagePNG($img);
				}
				else if($this->file != '') {
					header('Content-type: image/gif');
					readfile($this->file);
				}
				break;

			case 2:
				if($img && function_exists('ImageJPEG')) {
					header('Content-type: image/jpeg');
					@ImageJPEG($img);
				}
				else if($this->file != '') {
					header('Content-type: image/jpeg');
					readfile($this->file);
				}
				break;

			case 3:
				if($img && function_exists('ImagePNG')) {
					header('Content-type: image/png');
					@ImagePNG($img);
				}
				else if($this->file != '') {
					header('Content-type: image/png');
					readfile($this->file);
				}
				break;

			default:
				header('Content-type: image/gif');
				echo base64_decode('R0lGODlhCgAKAIAAAMDAwAAAACH5BAEAAAAALAAAAAAKAAoAAAIIhI+py+0PYysAOw==');
		}
	}
}

?>