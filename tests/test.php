<?php

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';


$img_path = 'C:\Users\steve\Dropbox\machine\desktop\prowebber\pure_image\dev\animal.jpg';
$img_path = 'C:\Users\steve\Dropbox\machine\desktop\prowebber\pure_image\dev\tall-image-test.jpg';


$img_dir = 'C:\Users\steve\Dropbox\machine\desktop\prowebber\pure_image\dev';

$img_name = '\original-tall.jpg';
//$img_name = '\original-wide.jpg';

$pure_image = new pure_image\Main();
$pure_image->add->image($img_dir . $img_name);
$pure_image->out->image([
	'method'    => 'fit',
	'width'     => '250',
	'height'    => '250',
	'quality'   => 65,
	'save_path' => $img_dir . '\test-fit-a.jpg',
]);

$pure_image->out->image([
	'method'    => 'cover',
	'width'     => '250',
	'height'    => '250',
	'quality'   => 65,
	'save_path' => $img_dir . '\test-cover-tall.jpg',
]);


$pure_image->showDebug();
$pure_image->save->image();


