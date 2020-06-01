# Pure Image

A PHP script that makes it easier to automatically compress, resize, and crop images.

### General

#### Installation
Install using composer: `composer require prowebber/pure_image`

#### Requirements
The following requirements must be met before using:

* PHP >=7.3
* PHP GD (https://www.php.net/manual/en/book.image.php)



## Usage

### Init Pure Image
```
// You will need to place Pure Image's composer autoload statement here

$pure_image = new pure_image\Main();
```


### Add an Image
1. Specify the image you want to format

```
$pure_image->add->image($img_path);
```


## Size Options

| Method  | Width Px | Height Px | Description                                                                                                  |
|:-------:|:--------:|:---------:|:-------------------------------------------------------------------------------------------------------------|
|  `fit`  | required | required  | Shrink the image so both the width and height fit within the dimensions specified                            |
| `cover` | required | required  | Shrink the image so the width and height are the exact dimensions specified. The final image may be cropped. |


### Fit
The image is resized so both the width and height will fit inside the dimensions specified.  The output image will maintain
the original aspect ratio.

**Fit Params**

| Param       | Type     | Description                                                                                                                                                          |
|:------------|:---------|:---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 'width'     | _int_    | The maximum width (px) of the image after resized                                                                                                                    |
| 'height'    | _int_    | The maximum height (px) of the image after resized                                                                                                                   |
| 'quality'   | _int_    | Scale of 0-100. `0` is the most compressed (poorest quality & smallest filesize).  `100` is least compression (best quality & largest filesize).  65 is recommended. |
| 'save_path' | _string_ | The absolute path of the output file.                                                                                                                                |

**Example Request**
```
$pure_image = new pure_image\Main();
$pure_image->add->image('/home/user/original.jpg');
$pure_image->out->image([
	'method'    => 'fit',
	'width'     => '250',
	'height'    => '250',
	'quality'   => 65,
	'save_path' => '/home/user/resized.jpg,
]);
```


### Cover
The image is resized to the exact width and height specified.  The image will be cropped if the
original aspect ratio cannot be kept at the specified size.

**Cover Params**

| Param       | Type     | Description                                                                                                                                                          |
|:------------|:---------|:---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 'width'     | _int_    | The maximum width (px) of the image after resized                                                                                                                    |
| 'height'    | _int_    | The maximum height (px) of the image after resized                                                                                                                   |
| 'quality'   | _int_    | Scale of 0-100. `0` is the most compressed (poorest quality & smallest filesize).  `100` is least compression (best quality & largest filesize).  65 is recommended. |
| 'save_path' | _string_ | The absolute path of the output file.                                                                                                                                |

**Example Request**
```php
$pure_image = new pure_image\Main();
$pure_image->add->image('/home/user/original.jpg');
$pure_image->out->image([
	'method'    => 'cover',
	'width'     => '250',
	'height'    => '250',
	'quality'   => 65,
	'save_path' => '/home/user/resized.jpg,
]);
```