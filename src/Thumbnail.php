<?php
namespace Cleverleap\Thumbnail;

class Thumbnail {

	protected $width = 400;								// width of thumbnail
	protected $height = 400;							// height of thumbnail
	protected $crop = FALSE;							// whether thumbnail should be cropped
	protected $errorPicture = 'images/photo.jpg';		// picture displayed when picture is not found
	protected $cacheDir = 'userdata/cacheimages/';		// directory to store resized images as cache
	protected $cacheEnabled = TRUE;
	protected $compressionStrength = 90;				// applies to JPEG pictures


	public function __construct($config = NULL) {
		if($config) {
			foreach($config as $key=>$val) {
				if(isset($this->$key)) {
					$this->$key = $val;
				}
			}
		}
	}
	
	/**
     * Set the width of final thumbnail 
     * @param int
     */
	public function setWidth($width) {
		$this->width = $width;
		return $this;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	/**
     * Set the height of final thumbnail 
     * @param int
     */
	public function setHeight($height) {
		$this->height = $height;
		return $this;
	}
	
	public function getHeight() {
		return $this->height;
	}

	/**
     * Whether image should be cropped and centered to match width and height
     * @param bool
     */
	public function crop($crop=FALSE) {
		$this->crop = $crop;
		return $this;
	}
	
	public function isCrop() {
		return $this->crop;
	}

	/**
	 * Generates thumbnail from supplied picture and sends it to the browser immediatelly
	 * @param string Path to picture fot thumbnail
	 */
	public function make($path) {
		$as = explode('?', $path);

		$src = $as[0];

		/* GENERATING PICTURE */

		if(empty($src) || !file_exists($src)) $src = $this->errorPicture; 
		
		$imagesize = getimagesize($src); // index[2] returns filetype flag. 2 - JPG, 3 - PNG
		$imgtype = $imagesize[2];

		/* Sending HTTP headers */
		header('Cache-Control: max-age=15000, must-revalidate');
		header('Cache-control: cache');

		if($imgtype == 1) {
			header('Content-Type: image/gif');
			$extension = 'gif';
			}
		if($imgtype == 2) {
			header('Content-Type: image/jpeg');
			$extension = 'jpeg';
			}
		if($imgtype == 3) {
			header('Content-Type: image/png');
			$extension = 'png';
			}
		
		$cachefile = md5($_SERVER['HTTP_HOST'].$src).'-'.$this->getWidth().$this->getHeight().$this->isCrop().'.'.$extension;

		if ($this->cacheEnabled && file_exists($this->cacheDir.$cachefile) && (filemtime($this->cacheDir.$cachefile) >= filemtime($src))) {
			readfile($this->cacheDir.$cachefile);
			exit;
		} else {
			if($imgtype == 1) $entry_img = imagecreatefromgif($src);
			if($imgtype == 2) $entry_img = imagecreatefromjpeg($src); // we read image file
			if($imgtype == 3) $entry_img = imagecreatefrompng($src);
	
			if($this->crop == TRUE) { 
				// jaky je pomer nejkratší strany cíle ke zdroji
				$pct = $this->getWidth()/$imagesize[0];
				if($imagesize[1]*$pct < $this->getHeight()) $pct = $this->getHeight()/$imagesize[1];
				
				// transitivní rozměr - crop area
				$tr_width = round($this->getWidth()/$pct); 
				$tr_height = round($this->getHeight()/$pct);
				
				// oriznuti zdroj. obrazku
				$source_x = round(($imagesize[0]-$tr_width)/2); 
				$source_y = round(($imagesize[1]-$tr_height)/2); 
				
				$final_width = $this->getWidth();
				$final_height = $this->getHeight();
				$imagesize[0] = $tr_width;
				$imagesize[1] = $tr_height;
				
				//error_log("PCT: $pct\nTR width: $tr_width\nTR height: $tr_height\nX: $source_x\nY: $source_y");
			} else {
				$pct = $this->getWidth()/$imagesize[0];
				if($imagesize[1]*$pct > $this->getHeight()) $pct = $this->getHeight()/$imagesize[1];
	
				$final_width = $imagesize[0]*$pct;
				$final_height = $imagesize[1]*$pct;
				$source_x = 0;
				$source_y = 0;
			}
				
			// we create new image
			$final_img = imagecreatetruecolor($final_width,$final_height);
			
			// final image, entry image, destination x, y, source x, y, destination width, height, src width, height
			imagecopyresampled($final_img,$entry_img,0,0,$source_x,$source_y,$final_width,$final_height,$imagesize[0],$imagesize[1]);
	
			if($imgtype == 1) { // GIF
				imagegif($final_img);
				if($this->cacheEnabled) imagegif($final_img, $this->cacheDir.$cachefile);
			}
			if($imgtype == 2) { // JPEG
				imagejpeg($final_img,NULL,$this->compressionStrength);
				if($this->cacheEnabled) imagejpeg($final_img, $this->cacheDir.$cachefile, $this->compressionStrength);
			}
			if($imgtype == 3) { // PNG
				imagepng($final_img);
				if($this->cacheEnabled) imagepng($final_img,$this->cacheDir.$cachefile);
			}
	
			imagedestroy($entry_img);
			imagedestroy($final_img);
			exit;
		}
	}
}