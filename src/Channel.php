<?php
namespace pure_image;

class Channel{
	//---------------	Class-Wide Variables	-------------
	public  $source;
	public  $allowed_mimes;
	public  $output;
	private $rules;
	private $errors;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Channel Constructor
	 */
	public function __construct(){
		$this->errors = [];
		
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
		
		// Contains output params
		$this->output = [];
		
		
		// Specify the allowed mime and their file type
		$this->allowed_mimes = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
		];
		
		$this->rules = [
			'max_source_image_size_bytes' => 10485760,            # 10 Mb
		];
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
	
	
	
	public function checkImageDimensions($output_width, $output_height){
		$source_width  = $this->source['width_px'];
		$source_height = $this->source['height_px'];
		
		if($output_width > $source_width) $this->addErr("The output image cannot be wider than the source image.");
		if($output_height > $source_height) $this->addErr("The output image height cannot be taller than the source image");
	}
	
	
	
	public function checkInputFileSize(){
		if($this->source['size_bytes'] > $this->rules['max_source_image_size_bytes']){
			$max_allowed_size = round($this->rules['max_source_image_size_bytes'] / 1048576, 2);
			$cur_input_size   = $this->source['size_mb'];
			
			$this->addErr("The current image is: ".$cur_input_size." MB.  The maximum allowed size is: ".$max_allowed_size." MB.");
		}
	}
	
	
	
	public function checkAllowedFileTypes($file_type){
		$allowed_file_types = array_flip($this->allowed_mimes);
		
		if(!isset($allowed_file_types[$file_type])){
			$this->addErr("The filetype: '$file_type' is not supported");
		}
	}
	
	
	
	public function setMaxImageSize($size_bytes){
		if(!ctype_digit($size_bytes)){
			$this->addErr("The max image size must be numeric");
			return FALSE;
		}
		
		$this->ch->rules['max_source_image_size_bytes'] = $size_bytes;
	}
	
	
	
	public function addErr($err_msg){
		$err_hash = md5($err_msg);
		
		if(isset($this->errors[$err_hash])) return;         # Don't continue if the error is already added
		$this->errors[$err_hash] = $err_msg;
	}
}

?>