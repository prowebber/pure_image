<?php

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';


$img_dir = 'C:\Users\steve\Dropbox\machine\desktop\prowebber\pure_image\dev';

$img_name = '\original-tall.jpg';
$img_name = '\original-wide.jpg';
//$img_name = '\full-width-png.png';

$pimage = new pure_image\Main();
$pimage->add->image($img_dir . $img_name);
$pimage->out->image([
	'method'      => 'fit',
	'width'       => '250',
	'height'      => '250',
	'quality'     => 65,
	'output_type' => 'png',
	'save_path'   => $img_dir . '\new-test-wide.jpg',
]);

//$pimage->out->image([
//	'method'    => 'cover',
//	'width'     => '250',
//	'height'    => '250',
//	'quality'   => 65,
//	'save_path' => $img_dir . '\test-cover-tall.jpg',
//]);

$pimage->showDebug();
$pimage->save->image();
$pimage->showErrors();


