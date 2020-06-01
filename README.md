# Pure Image

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

### Basic Usage
Pure Image is easy to use and only requires 4 steps:
1. A call to init Pure Image
2. Specify the source image you want to compress and/or resize
3. Specify the output image and compression settings
4. Save the images


#### Step 1) Instantiating Pure Image
```php
require_once __DIR__ . 'path_to_composer/autoload.php';

$pimage = new pure_image\Main();
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
$pimage->out->image([
	'method'      => 'compress',
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
|   `fit`    | Shrink the image so both the width and height fit within the dimensions specified                            |
|  `cover`   | Shrink the image so the width and height are the exact dimensions specified. The final image may be cropped. |


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

### Fit
The image is resized so both the width and height will fit inside the dimensions specified.  The output image will maintain
the original aspect ratio.

**All Params**

| Param       | Type        | Required | Description                                                                                  |
|:------------|:------------|:--------:|:---------------------------------------------------------------------------------------------|
| 'width'     | _int_       |   Yes    | The maximum width (px) of the image after resized                                            |
| 'height'    | _int_       |   Yes    | The maximum height (px) of the image after resized                                           |
| 'quality'   | _int\|null_ |    No    | The desired compression/quality level. [See Compression Settings](#image-compressionquality) |
| 'save_path' | _string_    |   Yes    | The absolute path of the output file.                                                        |

**Example Code**
```
$pimage = new pure_image\Main();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->image([
	'method'    => 'fit',
	'width'     => 250,
	'height'    => 250,
	'quality'   => 0,
	'save_path' => '/home/user/resized.jpg,
]);
```
---

### Cover
The image is resized to the exact width and height specified.  The image will be cropped if the
original aspect ratio cannot be kept at the specified size.

**Cover Params**

| Param         | Type     | Required | Description                                                                                                                                                         |
|:--------------|:---------|:--------:|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 'method'      | _string_ | Required | How you want the image resized                                                                                                                                      |
| 'width'       | _int_    | Required | The maximum width (px) of the image after resized                                                                                                                   |
| 'height'      | _int_    | Required | The maximum height (px) of the image after resized                                                                                                                  |
| 'save_path'   | _string_ | Required | The absolute path of the output file.                                                                                                                               |
| 'quality'     | _int_    | Optional | Scale of 0-100. `0` is the most compressed (poorest quality & smallest filesize).  `100` is least compressed (best quality & largest filesize).  65 is recommended. |
| 'output_type' | _string_ | Optional | (jpg\|png) The type of image you want this converted to or saved as.                                                                                                |

**Example Request**
```php
$pimage = new pure_image\Main();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->image([
	'method'    => 'cover',
	'width'     => 250,
	'height'    => 250,
	'quality'   => 65,
	'save_path' => '/home/user/resized.jpg,
]);
```

## Advanced

### Debug

```
ID
|-- method
|-- width_px
|-- height_px
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