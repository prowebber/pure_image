# Pure Image

A PHP script that makes it easier to automatically compress, resize, and crop images.

### General

#### Installation
Install using composer: `composer require prowebber/pure_image`

#### Requirements
The following requirements must be met before using:

* PHP >=7.3
* PHP GD (https://www.php.net/manual/en/book.image.php)


## Params

### Image Compression/Quality

| Level | Description                                             |
|:-----:|:--------------------------------------------------------|
|   0   | No compression, same as input                           |
|   1   | Least compression (higher filesize, but better quality) |
|  10   | Most compression (smallest filesize, poorest quality)   |

## Usage

### Init Pure Image
```
// You will need to place Pure Image's composer autoload statement here

$pimage = new pure_image\Main();
```


### Add an Image
1. Specify the image you want to format

```
$pimage->add->image($img_path);
```


## Size Options

| Method  | Width Px | Height Px | Description                                                                                                  |
|:-------:|:--------:|:---------:|:-------------------------------------------------------------------------------------------------------------|
|  `fit`  | required | required  | Shrink the image so both the width and height fit within the dimensions specified                            |
| `cover` | required | required  | Shrink the image so the width and height are the exact dimensions specified. The final image may be cropped. |

---
### Fit
The image is resized so both the width and height will fit inside the dimensions specified.  The output image will maintain
the original aspect ratio.

**Fit Params**

| Param       | Type     | Description                                                                                                                                                         |
|:------------|:---------|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 'width'     | _int_    | The maximum width (px) of the image after resized                                                                                                                   |
| 'height'    | _int_    | The maximum height (px) of the image after resized                                                                                                                  |
| 'quality'   | _int_    | Scale of 0-100. `0` is the most compressed (poorest quality & smallest filesize).  `100` is least compressed (best quality & largest filesize).  65 is recommended. |
| 'quality'   | _int_    | The desired compression/quality level.                                                                                                                              |
| 'save_path' | _string_ | The absolute path of the output file.                                                                                                                               |

**Example Request**
```
$pimage = new pure_image\Main();
$pimage->add->image('/home/user/original.jpg');
$pimage->out->image([
	'method'    => 'fit',
	'width'     => 250,
	'height'    => 250,
	'quality'   => 65,
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