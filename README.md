# fast-image-size library

### About

fast-image-size is a PHP library that does almost everything PHP's getimagesize() does but without the large overhead of downloading the complete file.

It currently supports the following image types:

* BMP
* GIF
* ICO
* IFF
* JPEG 2000
* JPEG
* PNG
* PSD
* TIF/TIFF
* WBMP

### Requirements

PHP 5.3.0 or newer is required for this library to work.

### Installation

It is recommend to install the library using composer.
Just add the following snippet to your composer.json:
```
  "require": {
    "marc1706/fast-image-size": "1.*"
  },
```

### Usage

Using the fast-image-size library is rather straightforward. Just create a new instance of the main class:
```
$FastImageSize = new \FastImageSize\FastImageSize();
```

Afterwards, you can check images using the getImageSize() method:
```
$imageSize = $FastImageSize->getImageSize('https://example.com/some_random_image.jpg');
```

You can pass any local or remote image to this library as long as it's readable.

If the library is able to determine the image size, it will return an array with the following structure (values and type might of course differ depending on your image):
```
$imageSize = array(
	'width' => 16,
	'height' => 16,
	'type' => IMAGETYPE_PNG,
);
```

### Automated Tests

The library is being tested using unit tests to prevent possible issues.

[![Build Status](https://travis-ci.org/marc1706/fast-image-size.svg?branch=master)](https://travis-ci.org/marc1706/fast-image-size)
[![Code Coverage](https://scrutinizer-ci.com/g/marc1706/fast-image-size/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/marc1706/fast-image-size/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/marc1706/fast-image-size/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/marc1706/fast-image-size/?branch=master)

### License

[The MIT License (MIT)](http://opensource.org/licenses/MIT)
