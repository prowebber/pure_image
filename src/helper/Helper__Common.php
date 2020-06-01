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
		if(!isset($this->ch->allowed_mimes[$mime])){
			$this->ch->addErr("The mime '$mime' is not yet supported.");
			return false;
		}
		
		return $this->ch->allowed_mimes[$mime];
	}
	
	
	public function getLargestSide($width_px, $height_px){
		return ($width_px >= $height_px) ? $width_px : $height_px;
	}
	
	
	public function getQuality($output_type, $wanted_quality){
		if($output_type == 'jpg'){
			if($wanted_quality == '5'){
				return 75;                      # Don't go higher than 75 since the filesize gets huge
			}
			elseif($wanted_quality == '4'){
				return 65;
			}
			elseif($wanted_quality == '3'){
				return 55;
			}
			elseif($wanted_quality == '2'){
				return 45;
			}
			elseif($wanted_quality == '1'){
				return 35;
			}
			else{
				return 65;
			}
		}
		
		elseif($output_type == 'png'){
			if($wanted_quality == '5'){
				return 6;                      # Don't go higher than 75 since the filesize gets huge
			}
			elseif($wanted_quality == '4'){
				return 5;
			}
			elseif($wanted_quality == '3'){
				return 4;
			}
			elseif($wanted_quality == '2'){
				return 3;
			}
			elseif($wanted_quality == '1'){
				return 2;
			}
			else{
				return 6;
			}
		}
	}
}
?>