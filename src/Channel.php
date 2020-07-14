<?php
namespace pure_image;

class Channel{
	//---------------	Class-Wide Variables	-------------
	public  $source;
	public  $allowed_mimes;
	public  $output;
	private $rules;
	private $errors;
	private $errors_detailed;
	private $apps_err_ids;
	private $error_ids;
	public  $is_custom_img_id;              # Specifies if this is a custom image or not
	public  $image_id;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Channel Constructor
	 */
	public function __construct(){
		$this->errors = [];
		
		$this->image_id         = NULL;     # The current image ID
		$this->is_custom_img_id = NULL;     # True if the user has set one or more images to a custom image ID; False if the user has NOT set a custom image ID
		
		$this->source = [
			'file_name'  => '',     # mount-everest.jpg
			'img_name'   => '',     # mount-everest
			'img_mime'   => '',     # image/jpeg
			'abs_path'   => '',     # C:\wamp64\tmp\php6492.tmp
			'file_type'  => '',     # jpg
			'size_bytes' => '',     # 679849
			'size_mb'    => '',     # 0.65
			'width_px'   => '',     # 1200
			'height_px'  => '',     # 1600
		];
		
		// Contains specific IDs for errors
		$this->apps_err_ids = [
			1  => 'The source image was not found or does not exist',
			2  => 'The mime is not supported',
			3  => 'Only the width or height can be specified with the scale method, but both are set',
			4  => 'You cannot set the maximum image size to a non-integer value',
			5  => 'The image filetype is not supported',
			6  => 'The image file size exceeds the specified limit',
			7  => 'The output height cannot be taller than the source image',
			8  => 'The output width cannot be wider than the source image',
			9  => 'You cannot start using a custom image ID after pImage assigned incremental IDs',
			10 => 'You must specify an ID for each image when using custom IDs',
			11 => 'Another image has already been assigned this ID.  Each image must have a unique ID.',
		];
		$this->error_ids    = [];
		
		// Contains output params
		$this->output = [];
		
		
		// Specify the allowed mime and their file type
		$this->allowed_mimes = [
			'image/gif'  => 'gif',
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
		];
		
		$this->rules = [
			'max_source_image_size_bytes' => 10485760,            # 10 Mb
		];
	}
	
	
	
	/**
	 * Set the ID for each image
	 *
	 * - User can can assign any type of ID (but all IDs have to be set by the user)
	 * - If user does not assign any IDs, pImage will auto-increment IDs starting at zero
	 * - You cannot mix auto-generated IDs and custom IDs
	 *
	 * @param $image_id {String|Int|Null} The desired image ID
	 */
	public function setImageId($image_id){
		
		// Not yet determined
		if(is_null($this->is_custom_img_id)){                       # If this is the 1st image added
			if(is_null($image_id)){                                 # If no ID was specified
				$this->is_custom_img_id = FALSE;                    # Let pImage handle the image IDs
				$this->image_id         = 0;                        # Start at zero for the first ID
			}
			else{
				$this->is_custom_img_id = TRUE;                     # Specify the user is assigning IDs
				$this->image_id         = $image_id;
			}
		}
		
		// Use auto-incrementing IDs
		elseif($this->is_custom_img_id == FALSE){                   # If auto-incrementing the image IDs
			if(!is_null($image_id)){                                # If the user is attempting to set the ID of this image
				$this->addErr("pImage started automatically setting image IDs, but you specified a custom ID.", 9);
			}
			$this->image_id++;                                      # Increment the current ID anyway
		}
		
		// Use custom IDs
		elseif($this->is_custom_img_id == TRUE){
			if(is_null($image_id)){                                 # If the user did not specify an ID for this image
				$this->addErr("All images must have an ID specified when using a custom ID", 10);
			}
			
			if(isset($this->output[$image_id])){
				$this->addErr("You have already assigned another image this ID", 11);
			}
			
			$this->image_id = $image_id;
		}
	}
	
	
	
	/**
	 * Check if everything is error free
	 *
	 * @return bool True if there are no errors; False if errors exist
	 */
	public function errorFree(){
		if(empty($this->errors)) return TRUE;
		return FALSE;
	}
	
	
	
	public function getErrors(){
		return $this->errors;
	}
	
	
	
	public function getDetailedErrors(){
		return $this->errors_detailed;
	}
	
	
	
	public function checkImageDimensions($output_width, $output_height){
		$source_width  = $this->source['width_px'];
		$source_height = $this->source['height_px'];
		
		if($output_width > $source_width) $this->addErr("The output image cannot be wider than the source image.", 8);
		if($output_height > $source_height) $this->addErr("The output image height cannot be taller than the source image", 7);
	}
	
	
	
	public function checkInputFileSize(){
		if($this->source['size_bytes'] > $this->rules['max_source_image_size_bytes']){
			$max_allowed_size = round($this->rules['max_source_image_size_bytes'] / 1048576, 2);
			$cur_input_size   = $this->source['size_mb'];
			
			$this->addErr("The current image is: " . $cur_input_size . " MB.  The maximum allowed size is: " . $max_allowed_size . " MB.", 6);
		}
	}
	
	
	
	public function checkAllowedFileTypes($file_type){
		$allowed_file_types = array_flip($this->allowed_mimes);
		
		if(!isset($allowed_file_types[$file_type])){
			$this->addErr("The filetype: '$file_type' is not supported", 5);
		}
	}
	
	
	
	public function setMaxImageSize($size_bytes){
		if(!ctype_digit($size_bytes)){
			$this->addErr("The max image size must be numeric", 4);
			return FALSE;
		}
		
		$this->ch->rules['max_source_image_size_bytes'] = $size_bytes;
	}
	
	
	
	public function addErr($err_msg, $err_id = 0){
		$err_hash = md5($err_msg);
		$image_id = $this->image_id;
		
		if(!empty($err_id)) $this->error_ids[$err_id] = 1;              # Record the error ID
		
		if(isset($this->errors[$err_hash])) return;                     # Don't continue if the error is already added
		$this->errors[$err_hash] = $err_msg;                            # General errors
		
		$this->errors_detailed[$image_id][$err_id] = $err_msg;
	}
}

?>