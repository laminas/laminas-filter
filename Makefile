# Minimum version of PHP supported by this project
PHP_VERSION ?= 8.3

#
# Filter 2.x needs a lot of extensions for the various compression formats.
# ext-rar is pretty much off the table
#
PHP_EXTENSIONS ?= lzf zip bz2 intl mbstring json xdebug snappy

DOCKER_IMAGE_NAME = laminas/filter

# Includes _everything_ supplied by the internal tooling lib
include vendor/laminas/internal-tooling/Makefile
