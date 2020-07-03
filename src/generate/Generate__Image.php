<?php
namespace pure_image\generate;


use pure_image\Channel;

class Generate__Image{
	//---------------	Class-Wide Variables	-------------
	private $ch;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Generate__Image Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch = $ch;
	}
	
	
	
	public function make($source, $source_type, $output_type, $output_width, $output_height, $save_path, $quality, $img_id){
		$is_crop_needed = $this->ch->output[$img_id]['rules']['is_crop_needed'];
		$is_hash        = $this->ch->output[$img_id]['rules']['is_hash'];    # True if creating an image hash
		$source_width   = $source['width_px'];
		$source_height  = $source['height_px'];
		$source_path    = $source['abs_path'];
		
		$output = imagecreatetruecolor($output_width, $output_height);      # Create a blank image with the specified dimensions
		
		// Input params
		if($source_type == 'jpg'){                                          # Jpg images
			$input = imagecreatefromjpeg($source_path);                     # Copy the source image
		}
		elseif($source_type == 'png'){                                      # Png images
			$input = imagecreatefrompng($source_path);                      # Copy the source image
		}
		elseif($source_type == 'gif'){                                      # Gif images
			$input = imagecreatefromgif($source_path);                      # Copy the source image
		}
		
		# Copy the image and resize + resample
		imagecopyresampled($output, $input, 0, 0, 0, 0, $output_width, $output_height, $source_width, $source_height);
		
		// If creating a hashed image
		if($is_hash && !$is_crop_needed){                                   # Handle images that are NOT cropped
			$output = $this->calcHash($img_id, $output);
		}
		
		// Create the image
		if($output_type == 'jpg'){
			imagejpeg($output, $save_path, $quality);
		}
		else if($output_type == 'png'){
			imagepng($output, $save_path, $quality);
		}
		elseif($source_type == 'gif'){                                      # Gif images
			imagegif($output, $save_path);
		}
		
		# Clear the image objects from PHP memory
		imagedestroy($output);
		imagedestroy($input);
	}
	
	
	
	private function calcHash($img_id, $output){
		imagefilter($output, IMG_FILTER_GRAYSCALE);                     # Convert to greyscale
		
		# Get the dimensions
		$img_width  = $this->ch->output[$img_id]['final_width_px'];     # Get the width of the output image
		$img_height = $this->ch->output[$img_id]['final_height_px'];    # Get the height of the output image
		
		// Calculate the average grayscale value
		$pixels = [];                                                   # Stores the grayscale value for each pixel
		for($y = 0; $y < $img_height; $y++){                            # Loop through each pixel vertically
			for($x = 0; $x < $img_width; $x++){                         # Loop through each pixel horizontally
				$rgb = imagecolorat($output, $x, $y);                   # Get the color RGB
				
				$r = ($rgb >> 16) & 0XFF;                               # Red value
				$g = ($rgb >> 8) & 0XFF;                                # Green value
				$b = $rgb & 0XFF;                                       # Blue value
				
				# Specify the greyscale ratios
				$pixels[] = floor(($r * 0.299) + ($g * 0.587) + ($b * 0.114));
				# $pixels[] = floor(($r * 0.2126) + ($g * 0.7152) + ($b * 0.0722));
			}
		}
		
		$avg_grey = floor(array_sum($pixels) / count($pixels));         # Get the average grayscale value
		$black    = imagecolorallocate($output, 0, 0, 0);               # Set the black value for new pixels
		$white    = imagecolorallocate($output, 255, 255, 255);         # Set the white value for new pixels
		
		/*
		 * Rotate/flip the image so mirrored images will be considered duplicates
		 *
		 * Rotate so all images have:
		 * - Darkest side is bottom right
		 * - Lightest side is top left
		 */
		
		# Find the heaviest quarter, keeps track of the total black pixels in the image
		$quarters = [
			'tl' => 0,
			'tr' => 0,
			'bl' => 0,
			'br' => 0,
		];
		
		// Convert the image from grayscale to black and white
		$i = 0;
		for($y = 0; $y < $img_height; $y++){                            # Loop through each vertical pixel
			for($x = 0; $x < $img_width; $x++){                         # Loop through each horizontal pixel
				$is_black = ($pixels[$i] > $avg_grey) ? 0 : 1;          # True if the color is black
				$color    = ($is_black) ? $black : $white;              # Specify the color values to use
				
				imagesetpixel($output, $x, $y, $color);                 # Re-color the image so it is black OR white (no grayscale)
				
				# Left
				if($x < floor($img_width / 2)){
					if($y < floor($img_height / 2)){                    # Top Left
						$quarters['tl'] += $is_black;
					}
					else{                                               # Bottom Left
						$quarters['bl'] += $is_black;
					}
				}
				
				# Right
				else{
					if($y < floor($img_height / 2)){                    # Top Right
						$quarters['tr'] += $is_black;
					}
					else{                                               # Bottom Right
						$quarters['br'] += $is_black;
					}
				}
				$i++;                                                   # Keep track of the index
			}
		}
		
		# Determine which quarter is the darkest
		$darkest_vert  = (($quarters['tl'] + $quarters['tr']) >= ($quarters['bl'] + $quarters['br'])) ? 'top' : 'bottom';
		$darkest_horiz = (($quarters['tr'] + $quarters['br']) >= ($quarters['tl'] + $quarters['bl'])) ? 'right' : 'left';
		
		# Set rules for flipping the image
		$flip_dir = [
			'top-left'     => 'flip_both',                              # Flip horizontal + flip vertical
			'top-right'    => 'flip_vert',                              # Flip vertical
			'bottom-left'  => 'flip_horiz',                             # Flip horizontal
			'bottom-right' => NULL,                                     # No rotation needed
		];
		
		$flip_rule = $flip_dir[$darkest_vert . '-' . $darkest_horiz];
		
		if($flip_rule){                                                 # If the image needs to be flipped
			if($flip_rule == 'flip_both'){
				imageflip($output, IMG_FLIP_BOTH);
			}
			elseif($flip_rule == 'flip_vert'){
				imageflip($output, IMG_FLIP_VERTICAL);
			}
			else{
				imageflip($output, IMG_FLIP_HORIZONTAL);
			}
		}
		
		
		/*
		 * Re-size the image so the hash will fit inside 64 bits
		 */
		$final = imagecreatetruecolor(8, 8);
		imagecopyresampled($final, $output, 0, 0, 0, 0, 8, 8, $img_width, $img_height);
		
		/*
		 * Regenerate the hash after flipped (only do this on a 8x8 image to keep 64 bit worth of data)
		 */
		$binary_hash = "";                                              # Stores the new binary hash val
		$bit_count   = 0;
		$img_height  = 8;                                               # Specify the image width is now 8px
		$img_width   = 8;                                               # Specify the image height is now 8px
		
		for($y = 0; $y < $img_height; $y++){                            # Loop through each horizontal pixel
			for($x = 0; $x < $img_width; $x++){                         # Loop through each vertical pixel
				$rgb = imagecolorat($final, $x, $y);                    # Get the color
				
				$r = ($rgb >> 16) & 0XFF;                               # Red
				$g = ($rgb >> 8) & 0XFF;                                # Green
				$b = $rgb & 0XFF;                                       # Blue
				
				# Get the hex code
				$color_code = floor(($r * 0.299) + ($g * 0.587) + ($b * 0.114));
				$color_val  = ($color_code > 127) ? 0 : 1;            # If it is greater than 255*.5 then it its black (1) otherwise white(2)
				
				$binary_hash .= $color_val;                             # If it is greater than 255*.5 then it its black (1) otherwise white(2)
				$bit_count   += $color_val;                             # Count the total '1' values in the string
			}
		}
		
		# Rebuild the hash for the rotated image
		$this->ch->output[$img_id]['hash']['avg_grey']              = $avg_grey;
		$this->ch->output[$img_id]['hash']['darkest']['vertical']   = $darkest_vert;
		$this->ch->output[$img_id]['hash']['darkest']['horizontal'] = $darkest_horiz;
		$this->ch->output[$img_id]['hash']['flip_direction']        = empty($flip_rule) ? 'none' : $flip_rule;
		$this->ch->output[$img_id]['hash']['values']                = [
			'bin'       => $binary_hash,                                # Binary hash
			'int'       => bindec($binary_hash),                        # Int representation of the binary value (BigInt Unsigned)
			'hex'       => dechex(bindec($binary_hash)),                # Hex representation of the binary value (16 chars)
			'bit_count' => $bit_count,
		];
		
		return $output;
	}
	
	
	
	public function crop($source_path, $source_type, $output_width, $output_height, $x_pos, $y_pos, $quality, $img_id){
		$is_crop_needed = $this->ch->output[$img_id]['rules']['is_crop_needed'];
		$is_hash        = $this->ch->output[$img_id]['rules']['is_hash'];    # True if creating an image hash
		
		// Input params
		if($source_type == 'jpg'){                                          # Jpg images
			$input = imagecreatefromjpeg($source_path);                     # Copy the source image
		}
		elseif($source_type == 'png'){                                      # Png images
			$input = imagecreatefrompng($source_path);                      # Copy the source image
		}
		elseif($source_type == 'gif'){                                      # Gif images
			$input = imagecreatefromgif($source_path);                      # Copy the source image
		}
		
		// Crop the image
		$crop_params = [
			'x'      => $x_pos,                                             # Starting x coordinate
			'y'      => $y_pos,                                             # Starting y coordinate
			'width'  => $output_width,                                      # Width to crop to
			'height' => $output_height                                      # Height to crop to
		];
		
		$cropped_img = imagecrop($input, $crop_params);                     # Create the cropped image
		
		// If creating a hashed image
		if($is_hash && $is_crop_needed){                                    # Handle images that ARE cropped
			$cropped_img = $this->calcHash($img_id, $cropped_img);
		}
		
		// Save the image
		if($source_type == 'jpg'){                                          # Jpg images
			imagejpeg($cropped_img, $source_path, $quality);                # Overwrite the source image with the cropped image
		}
		elseif($source_type == 'png'){                                      # Png images
			imagepng($cropped_img, $source_path, $quality);                 # Overwrite the source image with the cropped image
		}
		elseif($source_type == 'gif'){                                      # Gif images
			imagegif($cropped_img, $source_path);
		}
	}
}

?>