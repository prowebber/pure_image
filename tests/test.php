<?php

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

$img_dir = __DIR__ . '/img_source';
$img_name = '\water-original.jpg';

$pimage = new PureImage();                      # Init Pure Image
$pimage->setMaxImageSize(10000000);             # Set the max allowed image size (for the source image)
$pimage->add->image($img_dir . $img_name);      # Specify the source image

$pimage->out->cover([
	'width'     => 300,
	'height'    => 200,
	'save_path' => $img_dir . '\fit.jpg',
]);

$pimage->showDebug();               # Show the debug

$result = $pimage->getResult();

exit;


/**
 * Compress
 *
 * This will compress the image without resizing or scaling the output.
 */
$pimage->out->compress([
	'quality'   => 3,
	'save_path' => $img_dir . '\compress.jpg',
]);

/**
 * Cover
 *
 * This will resize and crop the image so the output will exactly match the image dimensions
 */
$pimage->out->cover([
	'width'     => 300,
	'height'    => 200,
	'save_path' => $img_dir . '\cover.jpg',
]);

/**
 * Fit
 *
 * This will resize the image so it will fit within the dimensions specified.  The final image will
 * keep the original apect ratio.
 */
$pimage->out->fit([
	'width'     => 300,
	'height'    => 200,
	'save_path' => $img_dir . '\fit.jpg',
]);


/**
 * Scale
 *
 * This will scale the image to match either the width OR height specified
 */

// Scale by height
$pimage->out->scale([
	'height'    => 300,
	'save_path' => $img_dir . '\scale-by-height.jpg',
]);

// Scale by width
$pimage->out->scale([
	'width'     => 300,
	'save_path' => $img_dir . '\scale-by-width.jpg',
]);


/**
 * Convert from gif to jpg
 */
$pimage->out->compress([
	'quality'     => 3,
	'output_type' => 'jpeg',
	'save_path'   => $img_dir . '\compress-gif-to-jpg.jpg',
]);


$pimage->showDebug();               # Show the debug
$pimage->save->image();             # Save all the images

if(!$pimage->isErrorFree()){        # If errors exist
	echo '<h1>Errors Exist</h1>';
}


$pimage->showErrors();              # Print any error message



/**
 * Run another batch
 */

# Convert Gif to Jpg
$img_name = '\water-original-gif.gif';
$pimage->add->image($img_dir . $img_name);      # Specify the source image

$pimage->out->compress([
	'quality'   => 3,
	'save_path' => $img_dir . '\compress-from-gif-to-jpg.jpg',
]);

$pimage->save->image();             # Save all the images
