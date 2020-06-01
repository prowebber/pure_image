<?php
namespace pure_image;

use pure_image\apps\Apps__Add_Image;
use pure_image\apps\Apps__Create_Image;
use pure_image\apps\Apps__Set_Output;

class Main{
	//---------------	Class-Wide Variables	-------------
	private $ch;
	public  $add;
	public  $out;
	public  $save;
	//---------------	Injected Classes	-----------------
	//---------------	Added Classes		-----------------
	
	
	/**
	 * Main Constructor
	 */
	public function __construct(){
		$this->ch   = new Channel();
		$this->add  = new Apps__Add_Image($this->ch);
		$this->out  = new Apps__Set_Output($this->ch);
		$this->save = new Apps__Create_Image($this->ch);
	}
	
	
	
	/**
	 * Set the max allowed import/source image size
	 * - Default is 10Mb
	 *
	 * @param $size_bytes
	 */
	public function setMaxImageSize($size_bytes){
		$this->ch->setMaxImageSize($size_bytes);
	}
	
	
	
	public function showDebug(){
		echo "<h3>Source</h3><pre>" . print_r($this->ch->source, TRUE) . "</pre>";
		echo "<h3>Output</h3><pre>" . print_r($this->ch->output, TRUE) . "</pre>";
	}
	
	public function showErrors(){
		$errors = $this->ch->getErrors();
		
		if($errors){
			echo "<b>Errors</b><pre>".print_r($errors, true)."</pre>";
		}
	}
}

?>