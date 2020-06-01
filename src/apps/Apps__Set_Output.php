<?php
namespace pure_image\apps;


use pure_image\Channel;
use pure_image\helper\Helper__Common;


/**
 * Class Apps__Set_Output
 *
 * Specify the output params for the image
 *
 *
 * @package pure_image\apps
 */
class Apps__Set_Output{
	//---------------	Class-Wide Variables	-------------
	private $params;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Apps__Set_Output Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch     = $ch;
		$this->helper = new Helper__Common($this->ch);
	}
	
	
	
	/**
	 * Apps__Set_Output Controller Function
	 */
	public function image($params){
		if(!$this->ch->errorFree()) return FALSE;               # Don't continue if an error exists
		
		$method    = $params['method'] ?? NULL;
		$width     = $params['width'] ?? NULL;
		$height    = $params['height'] ?? NULL;
		$save_path = $params['save_path'] ?? NULL;
		$quality   = $params['quality'] ?? 65;
		
		$this->params = [
			'method'       => $method,
			'width_px'     => $width,
			'height_px'    => $height,
			'save_path'    => $save_path,
			'quality'      => $quality,
			'longest_side' => [
				'source'    => NULL,
				'source_px' => NULL,
				'output'    => NULL,
				'output_px' => NULL,
			],
			'fit'          => [
				'is_crop_needed'  => FALSE,        # True if the image must be cropped to fit
				
				# Dimensions needed for a perfect fit
				'calc_dimensions' => [
					'ratio'  => NULL,               # The dimension ratio between source and output
					'width'  => NULL,               # The needed width
					'height' => NULL,               # The needed height
				],
				
				# If the image needs to be resized, this is what it will be resized to
				'resize'          => [
					'width'  => NULL,
					'height' => NULL,
				],
				
				# If the image needs to be cropped in order to fit
				'crop'            => [
					'x'      => NULL,               # The start coord for x position
					'y'      => NULL,               # The start coord for y position
					'width'  => NULL,               # The width to crop
					'height' => NULL,               # The height to crop
				],
			],
			'resize_cover' => [
				'is_perfect' => FALSE,                # True if the image fill scale perfectly to this size
				'needed'     => [
					'width'  => NULL,               # The width needed to be a perfect fit
					'height' => NULL,               # The height needed to be a perfect fit
				],
				'set'        => [
					'width'  => NULL,               # The width the image will need to be set to
					'height' => NULL,               # The height the image will need to be set to
				],
				'crop'       => [
					'x'      => NULL,               # Starting position for x coordinate
					'y'      => NULL,               # Starting position for y coordinate
					'width'  => NULL,               # Final width (px)
					'height' => NULL,               # Final height (px)
				],
			],
		];
		
		// If fitting the image to the specified dimensions
		if($method == 'fit'){
		
		}
		
		$this->calcDimensions();                    # Calculate image dimensions
		$this->calcFit();
		$this->calcCover();
		
		
		$this->ch->output[] = $this->params;            # Add to the output
	}
	
	
	
	private function calcDimensions(){
		$width_source  = $this->ch->source['width_px'];
		$height_source = $this->ch->source['height_px'];
		$width_out     = $this->params['width_px'];
		$height_out    = $this->params['height_px'];
		
		# Get the longest sides
		$source_longest_side_px = $this->helper->getLargestSide($width_source, $height_source);
		$source_longest_side    = ($width_source == $height_source) ? 'equal' : (($width_source > $height_source) ? 'width' : 'height');
		$out_longest_side_px    = $this->helper->getLargestSide($width_out, $height_out);
		$out_longest_side       = ($width_out == $height_out) ? 'equal' : (($width_out > $height_out) ? 'width' : 'height');
		
		$this->params['longest_side']['source']    = $source_longest_side;
		$this->params['longest_side']['source_px'] = $source_longest_side_px;
		$this->params['longest_side']['output']    = $out_longest_side;
		$this->params['longest_side']['output_px'] = $out_longest_side_px;
	}
	
	
	
	private function calcFit(){
		$width_source  = $this->ch->source['width_px'];
		$height_source = $this->ch->source['height_px'];
		$width_out     = $this->params['width_px'];
		$height_out    = $this->params['height_px'];
		
		# Get the longest sides
		$source_longest_side_px = $this->helper->getLargestSide($width_source, $height_source);
		$source_longest_side    = ($width_source == $height_source) ? 'equal' : (($width_source > $height_source) ? 'width' : 'height');
		$out_longest_side_px    = $this->helper->getLargestSide($width_out, $height_out);
		$out_longest_side       = ($width_out == $height_out) ? 'equal' : (($width_out > $height_out) ? 'width' : 'height');
		$ratio                  = $source_longest_side_px / $out_longest_side_px;
		
		# Determine the dimensions needed for the image to fit perfectly
		$needed_width  = $width_source / $ratio;              # The exact width needed (may have decimals)
		$needed_height = $height_source / $ratio;             # The exact height needed (may have deciamls)
		
		$crop_width  = NULL;
		$crop_height = NULL;
		if(ceil($needed_width) != $needed_width) $crop_width = ceil($needed_width);             # If the new width is a decimal, round up
		if(ceil($needed_height) != $needed_height) $crop_height = ceil($needed_height);         # If the height is a decimal, round up
		
		$is_crop_needed = (!is_null($crop_width) || !is_null($crop_height));
		
		$this->params['fit']['calc_dimensions']['ratio']  = $ratio;
		$this->params['fit']['calc_dimensions']['width']  = $needed_width;
		$this->params['fit']['calc_dimensions']['height'] = $needed_height;
		
		
		# If a crop is needed to fit the image
		if($is_crop_needed){
			
			# If the image needs to be resized to fit the height
			if(ceil($needed_height) != $needed_height){
				$set_resize_width  = ceil(ceil($needed_height) * $width_source / $height_source);      # See what the image's width needs to be to fit
				$set_resize_height = ceil($needed_height);
				
				$this->params['fit']['is_crop_needed']   = 1;
				$this->params['fit']['resize']['width']  = $set_resize_width;
				$this->params['fit']['resize']['height'] = $set_resize_height;
				
				$this->params['fit']['crop']['x']      = 0;
				$this->params['fit']['crop']['y']      = 0;
				$this->params['fit']['crop']['width']  = floor($needed_width);                      # Round down to keep a whole number & to prevent stretching the image
				$this->params['fit']['crop']['height'] = floor($needed_height);                     # Round down to keep a whole number & to prevent stretching the image
			}
			
			
			# If the image needs to be resized to fit the width
			else if(ceil($needed_width) != $needed_width){
				$set_resize_height = ceil(ceil($needed_width) * $height_source / $width_source);    # See what the image's width needs to be to fit
				$set_resize_width  = ceil($needed_width);
				
				$this->params['fit']['is_crop_needed']   = 1;
				$this->params['fit']['resize']['width']  = $set_resize_width;
				$this->params['fit']['resize']['height'] = $set_resize_height;
				
				$this->params['fit']['crop']['x']      = 0;
				$this->params['fit']['crop']['y']      = 0;
				$this->params['fit']['crop']['width']  = floor($needed_width);                      # Round down to keep a whole number & to prevent stretching the image
				$this->params['fit']['crop']['height'] = floor($needed_height);                     # Round down to keep a whole number & to prevent stretching the image
			}
		}
	}
	
	
	
	private function calcCover(){
		$width_source        = $this->ch->source['width_px'];
		$height_source       = $this->ch->source['height_px'];
		$width_out           = $this->params['width_px'];
		$height_out          = $this->params['height_px'];
		$source_longest_side = $this->params['longest_side']['source'];
		$out_longest_side    = $this->params['longest_side']['output'];
		
		if($source_longest_side == 'height'){                                   # If the source image is taller than it is wide
			$this->params['resize_cover']['needed']['width'] = $width_out;      # Use the output width
		}
		
		elseif($source_longest_side == 'width'){                                # If the source image is wider than it is tall
			$this->params['resize_cover']['needed']['height'] = $height_out;    # Use the output height
			
			# Get the width if the image was scaled down to fit the height
			$ratio        = $height_source / $height_out;
			$needed_width = $width_source / $ratio;
			
			$this->params['resize_cover']['needed']['width'] = $needed_width;
			
			# The final width needs to be even and cannot be a decimal
			$set_width = floor($needed_width);
			if($set_width % 2 != 0){                     # If the set width is odd
				$set_width -= 1;                        # Subtract 1
			}
			# @todo add a check to verify the image can fit the desired dimension
			
			# Since the image does not need to keep aspect ratio, round down to the nearest pixel
			$this->params['resize_cover']['set']['width']  = $set_width;
			$this->params['resize_cover']['set']['height'] = $height_out;
			
			$middle_x = ($set_width - $width_out) / 2;
			
			$this->params['resize_cover']['crop']['x']      = $middle_x;
			$this->params['resize_cover']['crop']['y']      = 0;
			$this->params['resize_cover']['crop']['width']  = $width_out;
			$this->params['resize_cover']['crop']['height'] = $height_out;
		}
	}
	
	
	
	private function createJpeg($params){
		$source_path   = $params['source_path'];
		$output_path   = $params['output_path'];
		$output_width  = $params['output_width'];
		$output_height = $params['output_height'];
		$source_width  = $params['source_width'];
		$source_height = $params['source_height'];
		$jpeg_quality  = $params['jpeg_quality'];
		
		$output = imagecreatetruecolor($output_width, $output_height);              # Prep the output file (the image being created)
		$input  = imagecreatefromjpeg($source_path);                                # Prep the input file (the image being used as the source)
		
		imagecopyresampled($output, $input, 0, 0, 0, 0, $output_width, $output_height, $source_width, $source_height);
		imagejpeg($output, $output_path, $jpeg_quality);
		
		// Clear the image objects from PHP memory
		imagedestroy($output);
		imagedestroy($input);
	}
}

?>