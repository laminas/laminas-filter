#!/bin/bash

WORKING_DIRECTORY=$2

cd $TMPDIR || exit 1;

pecl install lzf
pecl install rar

cd $WORKING_DIRECTORY
