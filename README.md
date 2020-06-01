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
