#!/bin/bash

input_fn=$1
base_fn=`basename $input_fn`

rm -rf $base_fn $base_fn.md5
mkdir $base_fn
( cd $base_fn && unzip ../$input_fn > /dev/null )

md5sum $input_fn | cut -b 1-32 > $base_fn.md5

echo IFN $input_fn
echo BFN $base_fn

