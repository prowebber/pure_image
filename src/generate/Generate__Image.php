<?php
namespace pure_image\generate;


class Generate__Image{
	//---------------	Class-Wide Variables	-------------
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Generate__Image Constructor
	 */
	public function __construct(){
		
	}
	
	
	
	public function make($source, $source_type, $output_type, $output_width, $output_height, $save_path, $quality){
		$source_width  = $source['width_px'];
		$source_height = $source['height_px'];
		$source_path   = $source['abs_path'];
		
		$output = imagecreatetruecolor($output_width, $output_height);      # Create a blank image with the specified dimensions
		
		// Input params
		if($source_type == 'jpg'){                                          # Jpg images
			$input = imagecreatefromjpeg($source_path);                     # Copy the source image
		}
		elseif($source_type == 'png'){                                      # Png images
			$input = imagecreatefrompng($source_path);                      # Copy the source image
		}
		
		# Copy the image and resize + resample
		imagecopyresampled($output, $input, 0, 0, 0, 0, $output_width, $output_height, $source_width, $source_height);
		
		
		// Create the image
		if($output_type == 'jpg'){
			imagejpeg($output, $save_path, $quality);
		}
		else if($output_type == 'png'){
			imagepng($output, $save_path, $quality);
		}
		
		# Clear the image objects from PHP memory
		imagedestroy($output);
		imagedestroy($input);
	}
	
	
	public function crop($source_path, $source_type, $output_width, $output_height, $x_pos, $y_pos, $quality){
		
		// Input params
		if($source_type == 'jpg'){                                          # Jpg images
			$input = imagecreatefromjpeg($source_path);                     # Copy the source image
		}
		elseif($source_type == 'png'){                                      # Png images
			$input = imagecreatefrompng($source_path);                      # Copy the source image
		}
		
		// Crop the image
		$crop_params = [
			'x'      => $x_pos,                                             # Starting x coordinate
			'y'      => $y_pos,                                             # Starting y coordinate
			'width'  => $output_width,                                      # Width to crop to
			'height' => $output_height                                      # Height to crop to
		];
		
		$cropped_img = imagecrop($input, $crop_params);                     # Create the cropped image
		
		// Save the image
		if($source_type == 'jpg'){                                          # Jpg images
			imagejpeg($cropped_img, $source_path, $quality);                # Overwrite the source image with the cropped image
		}
		elseif($source_type == 'png'){                                      # Png images
			imagepng($cropped_img, $source_path, $quality);                 # Overwrite the source image with the cropped image
		}
	}
	
}

?>