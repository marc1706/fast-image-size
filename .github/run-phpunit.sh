#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

PHP_VERSION=$1

if [[ "$PHP_VERSION" =~ ^nightly$ || "$PHP_VERSION" =~ ^8 ]]
then
	php composer require phpunit/phpunit:^9.3 --dev --update-with-all-dependencies --ignore-platform-reqs
fi

if [ "$PHP_VERSION" == "5.6" ]
then
	vendor/bin/phpunit --coverage-clover=coverage.clover
	wget https://scrutinizer-ci.com/ocular.phar
	php ocular.phar code-coverage:upload --format=php-clover coverage.clover
else
	vendor/bin/phpunit
fi
