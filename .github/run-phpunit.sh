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
	composer require phpunit/phpunit:^9.3 --dev --update-with-all-dependencies --ignore-platform-reqs
fi

if [ "$PHP_VERSION" == "7.2" ]
then
	vendor/bin/phpunit --coverage-clover=coverage.clover --verbose
else
	vendor/bin/phpunit --verbose
fi
