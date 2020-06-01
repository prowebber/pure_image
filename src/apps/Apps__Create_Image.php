<?php
namespace pure_image\apps;


use pure_image\Channel;
use pure_image\generate\Generate__Image;

class Apps__Create_Image{
	//---------------	Class-Wide Variables	-------------
	private $ch;
	private $gen_image;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Apps__Create_Image Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch        = $ch;
		$this->gen_image = new Generate__Image();
	}
	
	
	
	public function image(){
		if(!$this->ch->errorFree()) return FALSE;               # Don't continue if an error exists
		$source_type = $this->ch->source['file_type'];          # The type of image the source file is
		
		$source_params = [
			'width_px'  => $this->ch->source['width_px'],
			'height_px' => $this->ch->source['height_px'],
			'abs_path'  => $this->ch->source['abs_path'],
		];
		
		echo "<hr>";
		foreach($this->ch->output as $image){           # Loop through each image to be created
			echo "<h3>Image</h3><pre>" . print_r($image, TRUE) . "</pre>";
			
			$is_crop_needed   = $image['rules']['is_crop_needed'];
			$save_path        = $image['save_path'];
			$quality          = $image['quality'];
			$output_file_type = $image['output_type'];
			$output_type      = $image['output_type'];
			
			// Resize the image
			$output_width  = $image['rules']['resize']['width'];
			$output_height = $image['rules']['resize']['height'];
			
			# Make and convert the image (if the source type has changed)
			$this->gen_image->make($source_params, $source_type, $output_type, $output_width, $output_height, $save_path, $quality);
			$new_source_type = $output_file_type;               # The source will always be the output type after the image has been made
			
			// If a crop is needed to fit the image
			if($is_crop_needed){
				$output_width  = $image['rules']['crop']['width'];
				$output_height = $image['rules']['crop']['height'];
				$x_pos         = $image['rules']['crop']['x'];
				$y_pos         = $image['rules']['crop']['y'];
				
				$this->gen_image->crop($save_path, $new_source_type, $output_width, $output_height, $x_pos, $y_pos, $quality);
			}
		}
	}
}

?>