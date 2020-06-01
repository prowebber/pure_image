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
			'method'    => $method,
			'width_px'  => $width,
			'height_px' => $height,
			'save_path' => $save_path,
			'quality'   => $quality,
			
			'rules' => [
				'is_crop_needed'  => FALSE,        # True if the image must be cropped to fit
				'longest_side'    => [
					'source'    => NULL,
					'source_px' => NULL,
					'output'    => NULL,
					'output_px' => NULL,
				],
				
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
					'x'             => NULL,               # The start coord for x position
					'y'             => NULL,               # The start coord for y position
					'width'         => NULL,               # The width to crop
					'height'        => NULL,               # The height to crop
					'crop_position' => NULL,        # Where the crop was positioned
				],
			],
		];
		
		// If fitting the image to the specified dimensions
		if($method == 'fit'){
			$this->calcFit();
		}
		elseif($method == 'cover'){
			$this->calcCover();
		}
		
		$this->ch->output[] = $this->params;            # Add to the output
	}
	
	
	
	private function calcFit(){
		$width_source  = $this->ch->source['width_px'];
		$height_source = $this->ch->source['height_px'];
		$width_out     = $this->params['width_px'];
		$height_out    = $this->params['height_px'];
		
		// Get the longest sides
		$source_longest_side_px = $this->helper->getLargestSide($width_source, $height_source);
		$source_longest_side    = ($width_source == $height_source) ? 'equal' : (($width_source > $height_source) ? 'width' : 'height');
		$out_longest_side_px    = $this->helper->getLargestSide($width_out, $height_out);
		$out_longest_side       = ($width_out == $height_out) ? 'equal' : (($width_out > $height_out) ? 'width' : 'height');
		$ratio                  = $source_longest_side_px / $out_longest_side_px;
		
		$this->params['rules']['longest_side']['source']    = $source_longest_side;
		$this->params['rules']['longest_side']['source_px'] = $source_longest_side_px;
		$this->params['rules']['longest_side']['output']    = $out_longest_side;
		$this->params['rules']['longest_side']['output_px'] = $out_longest_side_px;
		
		# Determine the dimensions needed for the image to fit perfectly
		$needed_width  = $width_source / $ratio;              # The exact width needed (may have decimals)
		$needed_height = $height_source / $ratio;             # The exact height needed (may have deciamls)
		
		$crop_width  = NULL;
		$crop_height = NULL;
		if(ceil($needed_width) != $needed_width) $crop_width = ceil($needed_width);             # If the new width is a decimal, round up
		if(ceil($needed_height) != $needed_height) $crop_height = ceil($needed_height);         # If the height is a decimal, round up
		
		$is_crop_needed = (!is_null($crop_width) || !is_null($crop_height));
		
		$this->params['rules']['calc_dimensions']['ratio']  = $ratio;
		$this->params['rules']['calc_dimensions']['width']  = $needed_width;
		$this->params['rules']['calc_dimensions']['height'] = $needed_height;
		
		
		# If a crop is needed to fit the image
		if($is_crop_needed){
			
			# If the image needs to be resized to fit the height
			if(ceil($needed_height) != $needed_height){
				$set_resize_width  = ceil(ceil($needed_height) * $width_source / $height_source);      # See what the image's width needs to be to fit
				$set_resize_height = ceil($needed_height);
				
				$this->params['rules']['is_crop_needed']   = 1;
				$this->params['rules']['resize']['width']  = $set_resize_width;
				$this->params['rules']['resize']['height'] = $set_resize_height;
				
				$this->params['rules']['crop']['x']             = 0;
				$this->params['rules']['crop']['y']             = 0;
				$this->params['rules']['crop']['width']         = floor($needed_width);                 # Round down to keep a whole number & to prevent stretching the image
				$this->params['rules']['crop']['height']        = floor($needed_height);                # Round down to keep a whole number & to prevent stretching the image
				$this->params['rules']['crop']['crop_position'] = 'top left';
			}
			
			
			# If the image needs to be resized to fit the width
			else if(ceil($needed_width) != $needed_width){
				$set_resize_height = ceil(ceil($needed_width) * $height_source / $width_source);        # See what the image's width needs to be to fit
				$set_resize_width  = ceil($needed_width);
				
				$this->params['rules']['is_crop_needed']   = 1;
				$this->params['rules']['resize']['width']  = $set_resize_width;
				$this->params['rules']['resize']['height'] = $set_resize_height;
				
				$this->params['rules']['crop']['x']             = 0;
				$this->params['rules']['crop']['y']             = 0;
				$this->params['rules']['crop']['width']         = floor($needed_width);                 # Round down to keep a whole number & to prevent stretching the image
				$this->params['rules']['crop']['height']        = floor($needed_height);                # Round down to keep a whole number & to prevent stretching the image
				$this->params['rules']['crop']['crop_position'] = 'top left';
			}
		}
	}
	
	
	
	private function calcCover(){
		$width_source  = $this->ch->source['width_px'];
		$height_source = $this->ch->source['height_px'];
		$width_out     = $this->params['width_px'];
		$height_out    = $this->params['height_px'];
		
		// Get the longest sides
		$source_longest_side_px = $this->helper->getLargestSide($width_source, $height_source);
		$source_longest_side    = ($width_source == $height_source) ? 'equal' : (($width_source > $height_source) ? 'width' : 'height');
		$out_longest_side_px    = $this->helper->getLargestSide($width_out, $height_out);
		$out_longest_side       = ($width_out == $height_out) ? 'equal' : (($width_out > $height_out) ? 'width' : 'height');
		
		$this->params['rules']['longest_side']['source']    = $source_longest_side;
		$this->params['rules']['longest_side']['source_px'] = $source_longest_side_px;
		$this->params['rules']['longest_side']['output']    = $out_longest_side;
		$this->params['rules']['longest_side']['output_px'] = $out_longest_side_px;
		
		
		/*
		 * Determine dimensions
		 */
		
		// If the image is taller than it is wide
		if($source_longest_side == 'height'){                                       # If the source image is taller than it is wide
			$this->params['rules']['calc_dimensions']['width'] = $width_out;        # Use the width out the user specified
		}
		
		// If the image is wider than it is tall
		elseif($source_longest_side == 'width'){                                    # If the source image is wider than it is tall
			
			# Get the width if the image was scaled down to fit the height
			$ratio        = $height_source / $height_out;
			$needed_width = $width_source / $ratio;
			
			$this->params['rules']['is_crop_needed']            = 1;
			$this->params['rules']['calc_dimensions']['ratio']  = $ratio;
			$this->params['rules']['calc_dimensions']['width']  = $needed_width;
			$this->params['rules']['calc_dimensions']['height'] = $height_out;      # Use the height out the user specified
			
			# The final width needs to be even and cannot be a decimal
			$set_width = floor($needed_width);
			if($set_width % 2 != 0){                        # If the set width is odd
				$set_width -= 1;                            # Subtract 1
			}
			# @todo add a check to verify the image can fit the desired dimension
			
			# Since the image does not need to keep aspect ratio, round down to the nearest pixel
			$this->params['rules']['resize']['width']  = $set_width;
			$this->params['rules']['resize']['height'] = $height_out;
			
			$middle_x = ($set_width - $width_out) / 2;
			
			$this->params['rules']['crop']['x']             = $middle_x;
			$this->params['rules']['crop']['y']             = 0;
			$this->params['rules']['crop']['width']         = $width_out;
			$this->params['rules']['crop']['height']        = $height_out;
			$this->params['rules']['crop']['crop_position'] = 'middle center';
		}
	}
}

?>