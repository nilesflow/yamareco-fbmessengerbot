#!/bin/bash

if [ $# == 0 ]; then
  echo "please input target directory."
  echo "this-command [tareget directory]"
  exit
fi

phpdoc run --directory web --filename setup.php --target $1
