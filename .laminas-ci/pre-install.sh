#!/bin/bash

#
# @todo The compression adapters that require these non-standard extensions have been deprecated for removal in 3.0
#       This file can be removed along with the adapters.
#

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

cd "$TMPDIR" || exit 1;

apt install -y make g++

pecl channel-update pecl.php.net

# Install lzf
pecl install lzf || exit 1
echo "extension=lzf.so" >> /etc/php/"${PHP_VERSION}"/cli/php.ini

# Install rar - May 2023 - Rar no longer compiles on 8.1 or 8.2
#pecl install rar
#echo "extension=rar.so" >> /etc/php/"${PHP_VERSION}"/cli/php.ini

# Install snappy - May 2023 - Extension no longer compiles
# git clone --recursive --depth=1 https://github.com/kjdev/php-ext-snappy.git
# cd php-ext-snappy || exit 1;
# phpize
# ./configure
# make
# make install
#
# echo "extension=snappy.so" >> /etc/php/"${PHP_VERSION}"/cli/php.ini

# Debug output
php --ri lzf
echo ""
php --ri rar
echo ""
php --ri snappy
echo ""

cd "$WORKING_DIRECTORY" || exit 1;
