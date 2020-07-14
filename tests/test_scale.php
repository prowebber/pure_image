<?php

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

$img_dir  = __DIR__ . '/img_source';
$img_name = '\hard-to-scale.jpg';
//$img_name = '\hard-to-crop-vert.jpg';

# Scale Test
$pimage = new \pure_image\PureImage();          # Init Pure Image
$pimage->setMaxImageSize(10000000);             # Set the max allowed image size (for the source image)
$pimage->add->image($img_dir . $img_name);      # Specify the source image

$pimage->out->cover([
	'width'     => 640,
	'height'    => 360,
	'save_path' => $img_dir . '\covered-img.jpg',
]);

//$pimage->out->cover([
//	'width'     => 360,
//	'height'    => 640,
//	'save_path' => $img_dir . '\covered-img-wcrop.jpg',
//]);


$pimage->showDebug();               # Show the debug
$pimage->save->image();             # Save all the images

if(!$pimage->isErrorFree()){        # If errors exist
	echo '<h1>Errors Exist</h1>';
}


$results = $pimage->getResult();


$pimage->showErrors();              # Print any error message