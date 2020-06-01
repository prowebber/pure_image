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

|   Method   | Description                                                                                                  |
|:----------:|:-------------------------------------------------------------------------------------------------------------|
| `compress` | Don't resize the image; compress only                                                                        |
|  `cover`   | Shrink the image so the width and height are the exact dimensions specified. The final image may be cropped. |
|   `fit`    | Shrink the image so both the width and height fit within the dimensions specified                            |
|  `scale`   | Scale the image to a specified width or height.  Keep aspect ratio.                                          |


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

| Param         | Type     | Required | Description                                                                                                                        |
|:--------------|:---------|:--------:|:-----------------------------------------------------------------------------------------------------------------------------------|
| 'save_path'   | _string_ |   Yes    | The absolute path of the output file.                                                                                              |
| 'quality'     | _int_    | Optional | The desired compression/quality level. [See Compression Settings](#image-compressionquality)                                       |
| 'output_type' | _string_ | Optional | (jpg\|png\|gif) The type of image you want this converted to or saved as. (Uses the filetype of the source image if not specified) |

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

### Errors
Pure Image id designed to catch all errors before generating any output images.  If an error is detected it  
will store the error message and return false.  Below are different ways to check errors: \
&nbsp;

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

### Debug
You can see debug info by running the following command which will dump all the debug
info to the screen.

```php
$pimage->showDebug();
```

**Debug Description**
```
[ID]                        ** The Array index of the output image
|-- method                  ** {String} The compression/resize method to use
|-- width_px                ** {Int} The desired width of the output image
|-- height_px               ** {Int} The desired height of the output image
|-- save_path
|-- quality
|-- output_type
|-- rules
|   |-- is_crop_needed
|   |-- longest_side
|   |   |-- source
|   |   |-- source_px
|   |   |-- output
|   |   |-- output_px
|   |-- calc_dimensions
|   |   |-- ratio
|   |   |-- width
|   |   |-- height
|   |-- resize
|   |   |-- width
|   |   |-- height
|   |-- crop
|   |   |-- x
|   |   |-- y
|   |   |-- width
|   |   |-- height
|   |   |-- crop_position
```