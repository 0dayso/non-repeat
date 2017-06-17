
#!/bin/bash

wwwroot="$wwwroot"
clear
echo ""
echo ""
echo "==== 检测处理后的网页没有纰漏，则复制到根目录 ==="
echo ""
echo "====     wp-includes/js"
echo "====     wp-content/plugins"
echo "====     wp-content/themes"
echo "==== 以上目录中的非 php 文件务必提取原文件 ======"
echo ""
echo ""

cp -r $wwwroot/html/wp-content/uploads $wwwrootwp-content/
cp -r $wwwroot/html/201* $wwwroot
cp -r $wwwroot/html/page $wwwroot
cp -r $wwwroot/html/index.* $wwwroot
cp -r $wwwroot/html/url.txt $wwwroot
cp -r $wwwroot/html/ss $wwwroot
cp -r $wwwroot/html/ikev2-vpn $wwwroot
cp -r $wwwroot/html/non-repeat $wwwroot
cp -r $wwwroot/html/soft $wwwroot

echo ""
echo "========== 对 app/index.html 做编码转换 ========"
echo ""

rm -rf $wwwroot/html/app/*
wget -k -P $wwwroot/html/app --no-check-certificate https://ys138.win/app/
iconv -f UTF-8 -t GB2312 index.html >index2.html
mv index2.html index.html


echo ""
echo "========== 删除无用文件 ========"
echo ""

rm -rf $wwwroot/html/author
rm -rf $wwwroot/html/comments
rm -rf $wwwroot/html/feed
rm -rf $wwwroot/html/robots.txt
rm -rf $wwwroot/html/wp-json
rm -rf $wwwroot/html/wp-login.php?action=lostpassword
rm -rf $wwwroot/html/xmlrpc.php?rsd
rm -rf $wwwroot/html/category
rm -rf $wwwroot/html/soft
rm -rf $wwwroot/html/wp-content
rm -rf $wwwroot/html/wp-includes
rm -rf $wwwroot/html/wp-login.php
rm -rf $wwwroot/html/wp-login.php?action=register

