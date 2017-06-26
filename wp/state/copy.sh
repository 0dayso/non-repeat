#!/bin/bash

wwwroot="/home/wwwroot/default"

rm -rf $wwwroot/201*
rm -rf $wwwroot/page
rm -rf $wwwroot/category
rm -rf $wwwroot/index.html

cp -r $wwwroot/tmphtml/201* $wwwroot/
cp -r $wwwroot/tmphtml/page $wwwroot/
cp -r $wwwroot/tmphtml/category $wwwroot/
cp -r $wwwroot/tmphtml/index.html $wwwroot/
cp -r $wwwroot/index.html $wwwroot/index_cn.html

chown -R www:www $wwwroot/201*
chown -R www:www $wwwroot/page
chown -R www:www $wwwroot/category
chown www:www $wwwroot/index.html

rm -rf $wwwroot/tmphtml/*

