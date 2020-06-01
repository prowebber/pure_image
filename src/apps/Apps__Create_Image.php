<?php
namespace pure_image\apps;


use pure_image\Channel;
use pure_image\generate\Generate__Jpeg;

class Apps__Create_Image{
	//---------------	Class-Wide Variables	-------------
	private $ch;
	private $jpeg;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Apps__Create_Image Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch   = $ch;
		$this->jpeg = new Generate__Jpeg();
	}
	
	
	
	/**
	 * Apps__Create_Image Controller Function
	 */
	public function image(){
		if(!$this->ch->errorFree()) return false;               # Don't continue if an error exists
		
		echo "<hr>";
		foreach($this->ch->output as $image){           # Loop through each image to be created
			echo "<h3>Image</h3><pre>" . print_r($image, TRUE) . "</pre>";
			
			$method = $image['method'];
			
			if($method == 'fit'){
				$is_crop_needed = $image['fit']['is_crop_needed'];
				$save_path      = $image['save_path'];
				$jpeg_quality   = $image['quality'];
				
				// Resize the image
				$output_width  = $image['fit']['resize']['width'];
				$output_height = $image['fit']['resize']['height'];
				$this->jpeg->make($this->ch->source, $output_width, $output_height, $save_path, $jpeg_quality);     # Make the image
				
				// If a crop is needed to fit the image
				if($is_crop_needed){
					$output_width  = $image['fit']['crop']['width'];
					$output_height = $image['fit']['crop']['height'];
					$x_pos         = $image['fit']['crop']['x'];
					$y_pos         = $image['fit']['crop']['y'];
					
					$this->jpeg->crop($save_path, $output_width, $output_height, $x_pos, $y_pos, $jpeg_quality);
				}
			}
		}
	}
}

?>