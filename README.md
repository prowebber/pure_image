# Pure Image

[![GitHub version](https://badge.fury.io/gh/prowebber%2Fpure_image.svg)](https://badge.fury.io/gh/prowebber%2Fpure_image)

A PHP-only script that makes it easier to automatically compress, resize, and crop images.  This uses the
native functions within PHP and PHP GD to compress and resize the images.  Below are the main benefits
of Pure Image:

* Handles image type conversions (e.g. convert .png to .jpg)
* Maintains the original image's aspect ratio while scaling down
* Detects & fixes images that have an aspect ratio which cannot be divided evenly to fit the specified width/height.

### General

#### Installation
Install using composer: `composer require prowebber/pure_image`

#### Requirements
The following requirements must be met before using:

* PHP >=7.3
* PHP GD (https://www.php.net/manual/en/book.image.php)

#### Supported Images
Pure Image can read, write, and convert between the following image formats:

* Gif (.gif)
* Jpeg (.jpg, .jpeg)
* Png (.png)


### Basic Usage
Pure Image is easy to use and only requires 4 steps:
1. A call to init Pure Image
2. Specify the source image you want to compress and/or resize
3. Specify the output image and compression settings
4. Save the images


#### Step 1) Instantiating Pure Image
```php
require_once __DIR__ . 'path_to_composer/autoload.php';

$pimage = new pure_image\PureImage();
```
&nbsp;

#### Step 2) Specify a Source Image
Pure Image requires a source image for all operations.  You can do this by specifying the full
path to the image you want to compress/resize.

```php
$pimage->add->image('/abs_path/image.jpg');
```
&nbsp;

#### Step 3) Specify an Output Image
This is where you specify the resizing method, the name and location of the output image, and additional
params if needed.
```php
$pimage->out->compress([
	'save_path'   => '/output_dir/file_name.jpg',
]);
```

#### Step 4) Run Pure Image
The final step is running the script.  This will perform all actions and save the compressed/resized
image to the location you specified in step 3.

```php
$pimage->save->image();
```


## Common Settings

### Resize Options/Methods

|   Method   | Description                                                                                                      |
|:----------:|:-----------------------------------------------------------------------------------------------------------------|
| `compress` | Don't resize the image; compress only                                                                            |
|  `cover`   | Shrink the image so the width and height are the exact dimensions specified. The final image may be cropped.     |
|   `fit`    | Shrink the image so both the width and height fit within the dimensions specified                                |
|  `scale`   | Scale the image to a specified width or height.  Keep aspect ratio.                                              |
|   `hash`   | Generate a perceptual image hash and return the hash and the bit value of the image for detecting similar images |


### Image Compression/Quality
The image compression is specified on a scale of 0 to 5, with 1 being the lest compression (largest filesize/best quality)
and 5 being the most compressed (smallest filesize/poorest quality). If you leave the compression
setting blank or set it to `0`, the script will fallback to its default value. \
&nbsp;


| Level | Description          | Quality | Jpeg Equivalent | PNG Equivalent |
|:-----:|:---------------------|:--------|:---------------:|:--------------:|
|   0   | Default / fallback   |         |       65        |       6        |
|   1   | Least compression    | Best    |       75        |       0        |
|   2   | Low  compression     | Good    |       65        |       2        |
|   3   | Moderate compression | Decent  |       55        |       4        |
|   4   | High compression     | Low     |       45        |       6        |
|   5   | Highest compression  | Poor    |       35        |       8        |


## Pure Image Methods
Below are the different ways Pure Image can format the output image:

* [Compress](#compress) - Only compress the image.  Do not scale or resize.
* [Cover](#cover) - Resize and crop the output image so it is the exact width and height specified.
* [Fit](#fit) - Resize the output image so it will fit inside the dimensions specified while maintaining aspect ratio.
* [Scale](#scale) - Create an image with the specified width or height.  Keep the aspect ratio.


### Compress
Compress the image.  Do not scale or resize the output image.

**Options**
You can use these options on all types of methods.

| Param         | Type     | Required | Description                                                                                                                        |
|:--------------|:---------|:--------:|:-----------------------------------------------------------------------------------------------------------------------------------|
| 'save_path'   | _string_ |   Yes    | The absolute path of the output file.                                                                                              |
| 'quality'     | _int_    | Optional | The desired compression/quality level. [See Compression Settings](#image-compressionquality)                                       |
| 'output_type' | _string_ | Optional | (jpg\|png\|gif) The type of image you want this converted to or saved as. (Uses the filetype of the source image if not specified) |
| 'image_id'    | _string_ | Optional | Allows you to specify a unique ID for each image (output array and error array will reference this ID when called)                 |

**Example Request**
```php
$pimage = new pure_image\PureImage();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->compress([
	'quality'   => 3,
	'save_path' => '/home/user/resized.jpg,
]);
$pimage->save->image();
```
---

### Cover
The image is resized to the exact width and height specified.  The image will be cropped if the
original aspect ratio cannot be kept at the specified size.

**Options**

| Param         | Type     | Required | Description                                                                                                                   |
|:--------------|:---------|:--------:|:------------------------------------------------------------------------------------------------------------------------------|
| 'width'       | _int_    |   Yes    | The maximum width (px) of the image after resized                                                                             |
| 'height'      | _int_    |   Yes    | The maximum height (px) of the image after resized                                                                            |
| 'save_path'   | _string_ |   Yes    | The absolute path of the output file.                                                                                         |
| 'quality'     | _int_    | Optional | The desired compression/quality level. [See Compression Settings](#image-compressionquality)                                  |
| 'output_type' | _string_ | Optional | (jpg\|png\|gif) The type of image you want this converted to or saved as. (Uses the filetype of the source image if not specified) |

**Example Request**
```php
$pimage = new pure_image\PureImage();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->cover([
	'width'     => 250,
	'height'    => 250,
	'quality'   => 3,
	'save_path' => '/home/user/resized.jpg,
]);
$pimage->save->image();
```
---

### Fit
The image is resized so both the width and height will fit inside the dimensions specified.  The output image will maintain
the original aspect ratio.

**Options**

| Param         | Type        | Required | Description                                                                                                                   |
|:--------------|:------------|:--------:|:------------------------------------------------------------------------------------------------------------------------------|
| 'width'       | _int_       |   Yes    | The maximum width (px) of the image after resized                                                                             |
| 'height'      | _int_       |   Yes    | The maximum height (px) of the image after resized                                                                            |
| 'save_path'   | _string_    |   Yes    | The absolute path of the output file.                                                                                         |
| 'quality'     | _int\|null_ | Optional | The desired compression/quality level. [See Compression Settings](#image-compressionquality)                                  |
| 'output_type' | _string_    | Optional | (jpg\|png\|gif) The type of image you want this converted to or saved as. (Uses the filetype of the source image if not specified) |

**Example Code**
```php
$pimage = new pure_image\PureImage();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->fit([
	'width'     => 250,
	'height'    => 250,
	'quality'   => 3,
	'save_path' => '/home/user/resized.jpg,
]);
$pimage->save->image();
```
---

### Scale
Scale the image by its width or height.  The final image will be the exact width _or_ height specified and will
keep its original aspect ratio.

**Options**

| Param         | Type     | Required | Description                                                                                                                   |
|:--------------|:---------|:--------:|:------------------------------------------------------------------------------------------------------------------------------|
| 'width'       | _int_    |  Maybe   | The maximum width (px) of the image after resized                                                                             |
| 'height'      | _int_    |  Maybe   | The maximum height (px) of the image after resized                                                                            |
| 'save_path'   | _string_ |   Yes    | The absolute path of the output file.                                                                                         |
| 'quality'     | _int_    | Optional | The desired compression/quality level. [See Compression Settings](#image-compressionquality)                                  |
| 'output_type' | _string_ | Optional | (jpg\|png\|gif) The type of image you want this converted to or saved as. (Uses the filetype of the source image if not specified) |

**Example Request**
```php
$pimage = new pure_image\PureImage();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->scale([
	'width'     => 250,
	'height'    => 250,
	'quality'   => 3,
	'save_path' => '/home/user/resized.jpg,
]);
$pimage->save->image();
```


### Hash
The hash feature is used to create a unique hash for each image that makes it easy to detect duplicate
images in a database.  All hashes have the following features:

* Generates a 64 character fingerprint you can compare other images against
* Two different hashing algorithms are used (Average and Difference)
* All images are rotated so mirrored images with have the same hash value

**Notes**
1. PHP does not support unsigned BigInts, so you will need to convert the fingerprint hashes to hex or decimals within your script
2. You can save the hash image (if desired), but it is not necessary as the fingerprint represents the image exactly
3. Each digit in the fingerprint represents a specific pixel in the image. 0 = x0,y0, and 63 = x7,y7

#### Average Fingerprint
Creates a hash based on the average brightness of the image.

1. Visit each pixel (in order)
2. Compare the brightness of the current pixel to the average brightness of the entire image
   * If the current pixel is darker than average, give the current pixel a value of `1`
   * If the current pixel is lighter than average, give the current pixel a value of `0`
3. When completed, you will have a 64 character binary string of 0's and 1's

#### Difference Fingerprint
Creates a hash based off the brightness of the previous pixel.

1. Visit each pixel (in order)
2. Compare the brightness of the current pixel to the brightness of the previous pixel
   * If the current pixel is brighter, give the current pixel a value of `1`
   * If the previous pixel is brighter, give the current pixel a value of `0`
3. When completed, you will have a 64 character binary string of 0's and 1's

This is used to generate a hash of the image to help detect similar images that are being compressed.  The hash
will do the following:

1. Create a black and white 16px x 16px variation of the image
2. Rotate the images identically so flipped/rotated variants will be discovered
3. Generate a 64 bit binary value of the image
4. Use the binary value to create a hex and big int value

**Database Storage**
You can store the image hashes in a database.  Below is an example of a MySQL table to store the hashes:

| Field        | Type            | Notes                                              |
|:-------------|:----------------|:---------------------------------------------------|
| `img_id`     | int             | Auto-incrementing image ID                         |
| `total_bits` | tinyint         | Contains the total `1` values in the binary string |
| `img_hash`   | bigint UNSIGNED | Stores the hash value as an integer                |

#### Issues
* PHP does not support unsigned BigInt values
* If the image bit value is larger than a BigInt, it will be an invalid hash
* The best workaround is to use the bit value and have MySQL convert it to the BitInt hash
* More SQL tips to come

```sql
- This will convert the binary string to an Unsigned BigInt in MySQL
SELECT
    CAST(CONV(BINARY('1000000110000000000000000000000000000001111011111111111111111111'), 2, 10) AS UNSIGNED INTEGER) img_hash
```


**Hash Logic Used**
```
1. Make all images uniform
|-- Shrink the image to 16px x 16px (use the 'cover' resize method)
|-- Convert the image to grayscale
2. Remove minor color differences
|-- Determine the overall average grayscale color value for the image
|-- Loop through each pixel and assign it a boolean grayscale value
|   |-- Value of 1 indicates the pixel is darker than the average grayscale value
|   |-- Value of 0 indicates the pixel is lighter than the average grayscale value
3. Rotate the image so the darkest size is on the bottom right and lightest side is top left
|   |-- This helps catch similar images that are flipped or rotated
|   |-- Convert the image from greyscale to only black or white values
|   |-- Rotate the image
4. Compute the bits
|-- Resize the hash image to 8px x 8px so the total pixels is exactly 64 
|-- Visit each pixel (since it has been rotated they may be different) and get the boolean grayscale value
|-- Assign black pixels to 1 and white pixels to 0
|-- Build the binary string of 0 and 1 values
5. Generate the hashes
|-- Record the binary string hash
|-- Record the Decimal/BigInt value of the binary string
|-- Record the hex value of the binary string
```



## Advanced

### Creating Multiple Output Images
You can create multiple output images by making additional calls to the `$pimage->out` method with the
desired params.  When you are ready to create the images, just call the `$pimage->save->images()` method
and it will create all the images.

```php
$pimage->add->image('/home/user/original.jpg');

// Compress the image 
$pimage->out->compress([
	'quality'   => 3,
	'save_path' => '/home/user/compressed.jpg,
]);

// Create an image to fit the specified params
$pimage->out->fit([
	'width'     => 300,
	'height'    => 200,
	'quality'   => 3,
	'save_path' => '/home/user/resized.jpg,
]);

// Create another image to fit the specified params
$pimage->out->fit([
	'width'     => 600,
	'height'    => 400,
	'quality'   => 3,
	'save_path' => '/home/user/resized.jpg,
]);

// Create all images
$pimage->save->image();
```

### Custom Settings
Below are settings you can set and/or change dynamically. \
&nbsp;

**Max Allowed Image Size**  
This sets the maximum allowed size (in bytes) that a source image can be.  If this image size is
exceeded an error will be thrown.

```php
$pimage->setMaxImageSize(10000000);
```
&nbsp;

### Getting Output Result
After the images have been created you can fetch the output result/response with the following
method call:

```php
$result = $pimage->getResult();
```

This will return a array with details for each image processed.  If you specified a unique ID for each
image, the response array will be keyed by that ID.  If you __did not__ specify an ID for each image the
response array start at `0` and will increment for each additional image output.  So requesting `$result[0]` will
return the [debug](#debug) info for that image.
&nbsp;


### Errors
Pure Image id designed to catch all errors before generating any output images.  If an error is detected it  
will store the error message and return false.  Below are different ways to check errors: \
&nbsp;

**Errors by ID** \
These are the error codes (and errors) Pure Image checks.

| err_id | Error Message                                                                      |
|:------:|:-----------------------------------------------------------------------------------|
|   1    | The source image was not found or does not exist                                   |
|   2    | The mime is not supported                                                          |
|   3    | Only the width or height can be specified with the scale method, but both are set  |
|   4    | You cannot set the maximum image size to a non-integer value                       |
|   5    | The image filetype is not supported                                                |
|   6    | The image file size exceeds the specified limit                                    |
|   7    | The output height cannot be taller than the source image                           |
|   8    | The output width cannot be wider than the source image                             |
|   9    | You cannot start using a custom image ID after pImage assigned incremental IDs     |
|   10   | You must specify an ID for each image when using custom IDs                        |
|   11   | Another image has already been assigned this ID.  Each image must have a unique ID |


**Check if any errors exist** \
The following will return `TRUE` if there are no errors; `FALSE` if errors exist.

```php
$pimage->isErrorFree();
```
&nbsp;

**Echo errors to the screen** \
If any errors exist, this will echo/dump them to the screen.

```php
$pimage->showErrors();
````
&nbsp;

**Return an array of errors** \
If any errors exist, this will return an associative array of the errors.  The key/index will be
the MD5 value of the error message.

```php
$pimage->getErrors();
````
&nbsp;

**Return detailed array of errors** \
You can return a detailed array of errors (keyed by the image ID) by using the following method call:

```php
$pimage->getDetailedErrors();
````

This will return an array with the following syntax:
```
array[$image_id][$err_id] = Error message;
```

&nbsp;

### Debug
You can see debug info by running the following command which will dump all the debug
info to the screen.

```php
$pimage->showDebug();
```

**Debug Description** \
Below is an overview of the information output when debugging.

```
[ID]                                ** {Int} The Array index of the output image starting at 0
|-- method                          ** {String} The compression/resize method to use
|-- width_px                        ** {Int} The desired width of the output image
|-- height_px                       ** {Int} The desired height of the output image
|-- quality                         ** {Int} Quality level (converted to jpeg/png compression value)
|-- save_as                         ** {Array} Contains details on saving the image
|   |-- file_type                   ** {String} jpg|png|gif
|   |-- img_name                    ** {String} saved image name (e.g. mount-everest)
|   |-- file_name                   ** {String} saved image filename (e.g. mount-everest.jpg)
|   |-- file_path                   ** {String} saved image output path (e.g. /home/user/mount-everest.jpg)
|-- hash                            ** {Array} Contains hash details about the image (if selected as method)
|   |-- avg_grey                    ** {Int} The average greyscale color for the image (0-255)
|   |-- darkest                     ** {Array} Contains details of the darkest coordinates on the image
|   |   |-- vertical                ** {String} The darkest vertical region in the image
|   |   |-- horizontal              ** {String} The darkest horizontal region in the image
|   |-- flip_direction              ** {String} The direction the image was rotated before computing the hash
|   |-- fingerprints                ** {Array} Contains hash fingerprints
|   |   |-- average_hash            ** {String} 64 character hash of pixel differences by average pixel color
|   |   |-- average_bit_count       ** {Int} Total true bits in the average hash
|   |   |-- difference_hash         ** {String} 64 character hash of pixel differences by previous pixel color
|   |   |-- difference_bit_count    ** {Int} Total true bits in the difference hash
|-- rules                           ** {Array} Contains rules used for resizing the image
|   |-- is_crop_needed              ** {Bool} True if the image needs to be cropped to meet desired dimensions
|   |-- longest_side                ** {Array} Contains information on the input/output longest side(s)
|   |   |-- source                  ** {String} Which side on the source is the longest (width|height)
|   |   |-- source_px               ** {Int} The length of the longest side in px
|   |   |-- output                  ** {String} Which side on the output is the longest (width|height)
|   |   |-- output_px               ** {Int} The length of the longest side in px
|   |-- calc_dimensions             ** {Array} Contains dimensions required to keep the aspect ratio
|   |   |-- ratio                   ** {Int|Decimal} The ratio of the source to output image
|   |   |-- width                   ** {Int|Decimal} The width in px required for perfect fit
|   |   |-- height                  ** {Int|Decimal} The height in px required for perfect fit
|   |-- resize                      ** {Array} Contains dimensions to re-size the image to
|   |   |-- width                   ** {Int} The width in px to resize the image to
|   |   |-- height                  ** {Int} The height in px to resize the image to
|   |-- crop                        ** {Array} Contains crop details (if the image needs to be cropped)
|   |   |-- x                       ** {Int} Starting x-coord for the crop 
|   |   |-- y                       ** {Int} Starting y-coord for the crop
|   |   |-- width                   ** {Int} Width of the crop
|   |   |-- height                  ** {Int} Height of the crop
|   |   |-- crop_position           ** {String} Describes crop position {x y}
```