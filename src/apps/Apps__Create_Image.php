<?php
namespace pure_image\apps;


use pure_image\Channel;
use pure_image\generate\Generate__Image;

class Apps__Create_Image{
	//---------------	Class-Wide Variables	-------------
	private $ch;
	private $gen_image;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Apps__Create_Image Constructor
	 */
	public function __construct(Channel $ch){
		$this->ch        = $ch;
		$this->gen_image = new Generate__Image($this->ch);
	}
	
	
	public function image(){
		if(!$this->ch->errorFree()) return FALSE;                       # Don't continue if an error exists
		
		// Create the image(s)
		foreach(array_keys($this->ch->output) as $img_id){              # Loop through each image
			$this->gen_image->create($img_id);                          # Create the image
		}
	}
}

?>