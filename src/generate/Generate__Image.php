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
		$raw_px_data = [];                                              # Stores values for each pixel
		
		$pixels = [];                                                   # Stores the grayscale value for each pixel
		for($y = 0; $y < $img_height; $y++){                            # Loop through each pixel vertically
			for($x = 0; $x < $img_width; $x++){                         # Loop through each pixel horizontally
				$rgb = imagecolorat($output, $x, $y);                   # Get the color RGB
				
				$r = ($rgb >> 16) & 0XFF;                               # Red value
				$g = ($rgb >> 8) & 0XFF;                                # Green value
				$b = $rgb & 0XFF;                                       # Blue value
				
				# Specify the greyscale ratios
				$ratio    = floor(($r * 0.299) + ($g * 0.587) + ($b * 0.114));     # Value on a scale of 0-255
				$pixels[] = $ratio;
				
				$key                      = $x . '-' . $y;
				$raw_px_data[$key]['rgb'] = $ratio;
			}
		}
		
		$avg_grey = floor(array_sum($pixels) / count($pixels));         # Get the average grayscale value
		
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
		
		/*
		 * - Determine which pixels are darker than average
		 * - Do the calculations to determine how to rotate the image
		 */
		$i = 0;
		for($y = 0; $y < $img_height; $y++){                            # Loop through each vertical pixel
			for($x = 0; $x < $img_width; $x++){                         # Loop through each horizontal pixel
				$key      = $x . '-' . $y;                              # Key for associative array
				$is_black = ($pixels[$i] > $avg_grey) ? 1 : 0;          # 1 = black; 0 = white;
				
				$raw_px_data[$key]['bit_val'] = $is_black;              # Store the avg color value for each pixel
				
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
		
		$flip_rule = $flip_dir[$darkest_vert . '-' . $darkest_horiz];   # Get the name of the flip rule (for easier reference)
		
		
		/*
		 * Regenerate the hash after flipped (convert to 8x8 image scale to keep 64 bit worth of data)
		 */
		$pixel_map        = [];                                         # Maps the pixels for the final fingerprint
		$diff_fingerprint = "";                                         # Fingerprint based on if the current pixel is brighter than the previous pixel
		$avg_fingerprint  = "";                                         # Fingerprint based on if the pixel is brighter than the average image greyscale color
		$avg_bit_count    = 0;                                          # Counts the total 'bits' (1 values) in the avg_fingerprint
		$diff_bit_count   = 0;                                          # Counts the total 'bits' (1 values) in the diff_fingerprint
		
		$prev_key = NULL;
		for($y = 0; $y < 8; $y++){                                      # Loop through each horizontal pixel
			$y1 = $y * 2;
			$y2 = $y1 + 1;
			
			# Adjust pixel locations for flipping
			if($flip_rule == 'flip_vert' || $flip_rule == 'flip_both'){
				$y1 = 15 - $y2;
				$y2 = $y1 + 1;
			}
			
			for($x = 0; $x < 8; $x++){                                  # Loop through each vertical pixel
				$x1 = $x * 2;
				$x2 = $x1 + 1;
				
				# Adjust pixel locations for flipping
				if($flip_rule == 'flip_horiz' || $flip_rule == 'flip_both'){
					$x1 = 15 - $x2;
					$x2 = $x1 + 1;
				}
				
				$key    = $x . '-' . $y;
				$cell_a = $raw_px_data[$x1 . '-' . $y1];
				$cell_b = $raw_px_data[$x1 . '-' . $y2];
				$cell_c = $raw_px_data[$x2 . '-' . $y1];
				$cell_d = $raw_px_data[$x2 . '-' . $y2];
				
				// Compute the black/white value based off the 4 cells that were occupying this region in the 16x16 thumbnail
				
				# Compute the answers for the average hash
				$black_white_avg    = (($cell_a['bit_val'] + $cell_b['bit_val'] + $cell_c['bit_val'] + $cell_d['bit_val']) / 4);
				$is_darker_than_avg = ($black_white_avg >= .5) ? 1 : 0;             # 1 = this pixel is darker than average; 0 = this pixel is lighter than average
				$avg_fingerprint    .= $is_darker_than_avg;                         # 1 = pixel is darker than the average pixel; 0 = pixel is lighter than the average pixel
				$avg_bit_count      += $is_darker_than_avg;                         # Count the total '1' values in the fingerprint
				
				# Compute the answers for the difference hash
				$rgb_avg             = (($cell_a['rgb'] + $cell_b['rgb'] + $cell_c['rgb'] + $cell_d['rgb']) / 4);
				$prev_rgb_avg        = empty($prev_key) ? 0 : $pixel_map[$prev_key]['avg_rgb'];
				$is_darker_than_prev = ($rgb_avg > $prev_rgb_avg) ? 1 : 0;                 # 1 = this cell is brighter than the previous cell
				$diff_fingerprint    .= $is_darker_than_prev;
				$diff_bit_count      += $is_darker_than_prev;
				
				$pixel_map[$key] = [
					'avg_rgb'      => $rgb_avg,
				];
				
				$prev_key = $key;                                       # Update the previous key
			}
		}
		
		# Rebuild the hash for the rotated image
		$this->ch->output[$img_id]['hash']['avg_grey']              = $avg_grey;
		$this->ch->output[$img_id]['hash']['darkest']['vertical']   = $darkest_vert;
		$this->ch->output[$img_id]['hash']['darkest']['horizontal'] = $darkest_horiz;
		$this->ch->output[$img_id]['hash']['flip_direction']        = empty($flip_rule) ? 'none' : $flip_rule;
		$this->ch->output[$img_id]['hash']['fingerprints']          = [
			'average_hash'         => $avg_fingerprint,
			'average_bit_count'    => $avg_bit_count,
			'difference_hash'      => $diff_fingerprint,
			'difference_bit_count' => $diff_bit_count,
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