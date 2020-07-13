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
	private $ch;
	private $helper;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Apps__Set_Output Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch     = $ch;
		$this->helper = new Helper__Common($this->ch);
	}
	
	
	
	public function scale($params, $image_id = null){
		$this->ch->setImageId($image_id);                   # Set the ID for this image
		$this->image($params, 'scale');
	}
	
	
	
	public function cover($params, $image_id = null){
		$this->ch->setImageId($image_id);                   # Set the ID for this image
		$this->image($params, 'cover');
	}
	
	
	
	public function compress($params, $image_id = null){
		$this->ch->setImageId($image_id);                   # Set the ID for this image
		$this->image($params, 'compress');
	}
	
	
	
	public function fit($params, $image_id = null){
		$this->ch->setImageId($image_id);                   # Set the ID for this image
		$this->image($params, 'fit');
	}
	
	
	
	public function hash($params, $image_id = null){
		$this->ch->setImageId($image_id);                   # Set the ID for this image
		$params['width']  = 16;     # DO NOT CHANGE THIS
		$params['height'] = 16;     # DO NOT CHANGE THIS
		
		$this->image($params, 'hash');
	}
	
	
	
	/**
	 * Apps__Set_Output Controller Function
	 */
	private function image($params, $method){
		if(!$this->ch->errorFree()) return FALSE;               # Don't continue if an error exists
		$image_id = $this->ch->image_id;                        # Get the current image ID
		
		// Get source image info
		$source_img_type = $this->ch->source['file_type'];      # The type of image file e.g. jpg, png, etc.
		
		// Get the output params
		$width     = $params['width'] ?? NULL;
		$height    = $params['height'] ?? NULL;
		$save_path = $params['save_path'] ?? NULL;
		$save_img  = $params['save_img'] ?? TRUE;
		
		# See if the user specified any optional params
		$output_img_type = $params['output_type'] ?? $source_img_type;                      # Use the type specified by the user; default to the input filetype if not specified
		$output_img_type = $this->helper->formatFileType($output_img_type);                 # Format the filetype
		$user_quality    = $params['quality'] ?? NULL;                                      # Get the quality specified by the user
		$quality         = $this->helper->getQuality($output_img_type, $user_quality);      # Get the quality level for the image format
		
		# Get the save location info
		$path_info   = pathinfo($save_path);                                               # Get file info
		$save_to_dir = $path_info['dirname'];
		$image_name  = $path_info['filename'];
		$file_name   = $image_name . '.' . $output_img_type;                              # Set the correct name (with the correct file extension type)
		$file_path   = $save_to_dir . '/' . $file_name;
		
		
		// Validate
		$this->ch->checkAllowedFileTypes($output_img_type);     # Check for filetype issues
		$this->ch->checkImageDimensions($width, $height);       # Check for dimension issues
		if(!$this->ch->errorFree()) return FALSE;               # Don't continue if an error exists
		
		$this->params = [
			'method'          => $method,
			'width_px'        => $width,                      # Desired width
			'height_px'       => $height,                     # Desired height
			'quality'         => $quality,                    # 0-100 if jpg, 0-9 if png
			'save_as'         => [
				'file_type' => $output_img_type,        # jpg|png|gif
				'img_name'  => $image_name,             # mount-everest
				'file_name' => $file_name,              # mount-everest.jpg
				'file_path' => $file_path,              # /home/user/mount-everest.jpg
				'save_img'  => $save_img,               # Only used if the image is a hash
			],
			'final_width_px'  => NULL,                  # The width of the output image
			'final_height_px' => NULL,                  # The height of the output image
			
			# Only used when hashing
			'hash'            => [],
			
			'rules' => [
				'is_hash'         => FALSE,
				'is_crop_needed'  => FALSE,             # True if the image must be cropped to fit
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
					'x'             => NULL,                # The start coord for x position
					'y'             => NULL,                # The start coord for y position
					'width'         => NULL,                # The width to crop
					'height'        => NULL,                # The height to crop
					'crop_position' => NULL,                # Where the crop was positioned
				],
			],
		];
		
		// If fitting the image to the specified dimensions
		if($method == 'compress'){
			$this->calcCompress();
		}
		elseif($method == 'scale'){
			$this->calcScale();
		}
		elseif($method == 'fit'){
			$this->calcFit();
		}
		elseif($method == 'cover'){
			$this->calcCover();
		}
		elseif($method == 'hash'){
			$this->params['rules']['is_hash'] = TRUE;   # Specify this is a hash
			$this->calcCover();                         # Use the 'cover' method
		}
		
		// Record the final image size
		if($this->params['rules']['is_crop_needed']){   # If the image is being cropped
			$this->params['final_width_px']  = $this->params['rules']['crop']['width'];
			$this->params['final_height_px'] = $this->params['rules']['crop']['height'];
		}
		else{                                           # If the image is NOT being cropped
			$this->params['final_width_px']  = $this->params['rules']['resize']['width'];
			$this->params['final_height_px'] = $this->params['rules']['resize']['height'];
		}
		
		$this->ch->output[$image_id] = $this->params;            # Add to the output
	}
	
	
	
	private function calcScale(){
		$width_source  = $this->ch->source['width_px'];
		$height_source = $this->ch->source['height_px'];
		$width_out     = $this->params['width_px'];
		$height_out    = $this->params['height_px'];
		
		// Verify only one side is specified
		if(!empty($width_out) && !empty($height_out)){
			$this->ch->addErr("Only the width or height can be specified with 'scale', both are currently set.", 3);
			return FALSE;
		}
		
		// If scaling by width
		if(!empty($width_out)){
			# Get the height if the image was scaled down to fit the width
			$ratio         = $width_source / $width_out;
			$needed_height = $height_source / $ratio;
			
			$this->params['rules']['calc_dimensions']['ratio']  = $ratio;
			$this->params['rules']['calc_dimensions']['width']  = $width_out;           # Use the width out the user specified
			$this->params['rules']['calc_dimensions']['height'] = $needed_height;       # Use the height out the user specified
			
			# The final height needs to be even and cannot be a decimal
			$set_height = floor($needed_height);
			
			# Since the image does not need to keep aspect ratio, round down to the nearest pixel @todo test this theory
			$this->params['rules']['resize']['width']  = $width_out;
			$this->params['rules']['resize']['height'] = $set_height;
		}
		
		// If scaling by height
		else{
			# Get the width if the image was scaled down to fit the height
			$ratio        = $height_source / $height_out;
			$needed_width = $width_source / $ratio;
			
			$this->params['rules']['calc_dimensions']['ratio']  = $ratio;
			$this->params['rules']['calc_dimensions']['width']  = $needed_width;
			$this->params['rules']['calc_dimensions']['height'] = $height_out;      # Use the height out the user specified
			
			# The final width needs to be even and cannot be a decimal
			$set_width = floor($needed_width);
			
			# Since the image does not need to keep aspect ratio, round down to the nearest pixel
			$this->params['rules']['resize']['width']  = $set_width;
			$this->params['rules']['resize']['height'] = $height_out;
		}
	}
	
	
	
	private function calcCompress(){
		$width_source  = $this->ch->source['width_px'];
		$height_source = $this->ch->source['height_px'];
		
		$this->params['rules']['calc_dimensions']['ratio']  = 1;
		$this->params['rules']['calc_dimensions']['width']  = $width_source;
		$this->params['rules']['calc_dimensions']['height'] = $height_source;
		
		$this->params['rules']['is_crop_needed']   = 0;
		$this->params['rules']['resize']['width']  = $width_source;
		$this->params['rules']['resize']['height'] = $height_source;
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
		
		# If the image does not need to be adjusted; set the default size
		else{
			$this->params['rules']['is_crop_needed']   = 0;
			$this->params['rules']['resize']['width']  = $needed_width;
			$this->params['rules']['resize']['height'] = $needed_height;
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
		
		$this->params['rules']['is_crop_needed']            = 1;                    # @todo will crop always be needed?
		$this->params['rules']['longest_side']['source']    = $source_longest_side;
		$this->params['rules']['longest_side']['source_px'] = $source_longest_side_px;
		$this->params['rules']['longest_side']['output']    = $out_longest_side;
		$this->params['rules']['longest_side']['output_px'] = $out_longest_side_px;
		
		
		/*
		 * Determine dimensions
		 */
		
		// If the image is taller than it is wide
		if($source_longest_side == 'height'){                                           # If the source image is taller than it is wide
			
			# Get the height if the image was scaled down to fit the width
			$ratio         = $width_source / $width_out;
			$needed_height = $height_source / $ratio;
			
			$this->params['rules']['calc_dimensions']['ratio']  = $ratio;
			$this->params['rules']['calc_dimensions']['width']  = $width_out;           # Use the width out the user specified
			$this->params['rules']['calc_dimensions']['height'] = $needed_height;       # Use the height out the user specified
			
			# The final height needs to be even and cannot be a decimal
			$set_height = floor($needed_height);
			if($set_height % 2 != 0){                        # If the set height is odd
				$set_height -= 1;                            # Subtract 1
			}
			# @todo add a check to verify the image can fit the desired dimension
			
			# Since the image does not need to keep aspect ratio, round down to the nearest pixel @todo test this theory
			$this->params['rules']['resize']['width']  = $width_out;
			$this->params['rules']['resize']['height'] = $set_height;
			
			$middle_y = ($set_height - $height_out) / 2;
			
			$this->params['rules']['crop']['x']             = 0;
			$this->params['rules']['crop']['y']             = $middle_y;
			$this->params['rules']['crop']['width']         = $width_out;
			$this->params['rules']['crop']['height']        = $height_out;
			$this->params['rules']['crop']['crop_position'] = 'middle center';
		}
		
		// If the image is wider than it is tall
		elseif($source_longest_side == 'width'){                                    # If the source image is wider than it is tall
			
			# Get the width if the image was scaled down to fit the height
			$ratio        = $height_source / $height_out;
			$needed_width = $width_source / $ratio;
			
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
		
		// If the image is equal width and height
		else{
			# Get the width if the image was scaled down to fit the height
			$ratio        = $height_source / $height_out;
			$needed_width = $width_source / $ratio;
			
			$this->params['rules']['calc_dimensions']['ratio']  = $ratio;
			$this->params['rules']['calc_dimensions']['width']  = $needed_width;
			$this->params['rules']['calc_dimensions']['height'] = $height_out;      # Use the height out the user specified
			
			# The final width needs to be even and cannot be a decimal
			$set_width = floor($needed_width);
			if($set_width % 2 != 0){                        # If the set width is odd
				$set_width -= 1;                            # Subtract 1
			}
			
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