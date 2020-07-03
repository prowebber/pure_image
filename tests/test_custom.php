<?php

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

$img_dir = __DIR__ . '/img_source';
$img_name = '\water-original.jpg';
//$img_name = '\water-original-flipped.jpg';
//$img_name = '\water-original-unflipped.jpg';


# Person Test
$pimage = new \pure_image\PureImage();          # Init Pure Image
$pimage->setMaxImageSize(10000000);             # Set the max allowed image size (for the source image)
$pimage->add->image($img_dir . $img_name);      # Specify the source image

# Save the standard image
//$pimage->out->compress([
//	'save_path' => $img_dir . '\compress_7674.jpg',
//]);

# Resize and save the smaller image
//$pimage->out->fit([
//	'width'     => 220,
//	'height'    => 75,
//	'save_path' => $img_dir . '/fit_7674.jpg',
//]);

/**
 * Hash Notes
 *
 * References
 * - https://stackoverflow.com/questions/11333591/find-similar-images-in-pure-php-mysql
 * - https://github.com/jenssegers/imagehash
 * - https://www.hackerfactor.com/blog/?/archives/432-Looks-Like-It.html
 *
 * Difference Hash
 * - https://phpnews.io/feeditem/perceptual-image-hashes
 */

/*
 * Generate a Perceptual Image Hash
 */
$pimage->out->hash([
	'save_path' => $img_dir . '/hash_test.jpg',
]);

$pimage->save->image();                               # Save the images
$pimage->showDebug();

