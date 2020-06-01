<?php
namespace pure_image\apps;


use pure_image\Channel;
use pure_image\helper\Helper__Common;

class Apps__Add_Image{
	//---------------	Class-Wide Variables	-------------
	private $ch;
	private $helper;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Apps__Add_Image Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch     = $ch;
		$this->helper = new Helper__Common($this->ch);
	}
	
	
	
	/**
	 * Add the image by specifying the path
	 *
	 * @param $path {String} The absolute image path (can be located on your
	 *              machine or can be a tmp upload location)
	 *
	 * @return bool Returns False on error
	 */
	public function image($path){
		$this->ch->output = [];             # Reset the output array
		
		# Verify the source image exists before continuing
		if(!file_exists($path)){
			$this->ch->addErr("The image: '$path' was not found or does not exist.");
			return FALSE;
		}
		
		$image_size_info = getimagesize($path);
		$image_width     = $image_size_info[0] ?? NULL;
		$image_height    = $image_size_info[1] ?? NULL;
		$image_mime      = $image_size_info['mime'] ?? NULL;
		$image_size      = filesize($path);
		$size_mb         = $this->helper->bytesToMb($image_size);
		$file_type       = $this->helper->getImageTypeByMime($image_mime);
		$path_info       = pathinfo($path);                     # Get file info
		$this->ch->checkInputFileSize();                        # Verify the input file is not too large
		if(!$this->ch->errorFree()) return FALSE;               # Don't continue if an error exists
		
		// Record source image properties
		$this->ch->source['width_px']   = $image_width;
		$this->ch->source['height_px']  = $image_height;
		$this->ch->source['img_mime']   = $image_mime;
		$this->ch->source['abs_path']   = $path;
		$this->ch->source['file_name']  = $path_info['basename'];
		$this->ch->source['img_name']   = $path_info['filename'];
		$this->ch->source['file_type']  = $file_type;
		$this->ch->source['size_bytes'] = $image_size;
		$this->ch->source['size_mb']    = $size_mb;
	}
}

?>