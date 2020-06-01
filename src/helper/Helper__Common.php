<?php
namespace pure_image\helper;




use pure_image\Channel;

class Helper__Common {
	
	//---------------	Class-Wide Variables	-------------
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Helper__Common Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch = $ch;
	}
	
	public function bytesToMb($bytes){
		return round($bytes / 1048576, 2);
	}
	
	
	public function getImageTypeByMime($mime){
		return $this->ch->allowed_mimes[$mime] ?? false;
	}
	
	
	public function getLargestSide($width_px, $height_px){
		return ($width_px >= $height_px) ? $width_px : $height_px;
	}
}
?>