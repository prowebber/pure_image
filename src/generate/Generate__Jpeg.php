<?php
namespace pure_image\generate;


class Generate__Jpeg{
	//---------------	Class-Wide Variables	-------------
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Generate__Jpeg Constructor
	 */
	public function __construct(){
		
	}
	
	
	
	/**
	 * Generate__Jpeg Controller Function
	 */
	public function make($source, $output_width, $output_height, $save_path, $jpeg_quality){
		$source_width  = $source['width_px'];
		$source_height = $source['height_px'];
		$source_path   = $source['abs_path'];
		
		$output = imagecreatetruecolor($output_width, $output_height);      # Create a blank image with the specified dimensions
		$input  = imagecreatefromjpeg($source_path);                        # Copy the source image
		
		# Copy the image and resize + resample
		imagecopyresampled($output, $input, 0, 0, 0, 0, $output_width, $output_height, $source_width, $source_height);
		imagejpeg($output, $save_path, $jpeg_quality);
		
		# Clear the image objects from PHP memory
		imagedestroy($output);
		imagedestroy($input);
	}
	
	
	public function crop($source, $output_width, $output_height, $x_pos, $y_pos, $jpeg_quality){
		$input = imagecreatefromjpeg($source);                  # Copy the source image
		
		$crop_params = [
			'x'      => $x_pos,                                 # Starting x coordinate
			'y'      => $y_pos,                                 # Starting y coordinate
			'width'  => $output_width,                          # Width to crop to
			'height' => $output_height                          # Height to crop to
		];
		
		$cropped_img = imagecrop($input, $crop_params);         # Create the cropped image
		imagejpeg($cropped_img, $source, $jpeg_quality);        # Overwrite the source image with the cropped image
		imagedestroy($cropped_img);                             # Clear the image object from PHP memory
	}
}

?>