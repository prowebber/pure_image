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
			'img_name'   => '',     # mount-everest.jpg
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