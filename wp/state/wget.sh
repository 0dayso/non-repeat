#!/bin/bash

# 这里需要设定 wwwroot
#
wwwroot="/home/wwwroot/default"


if [ ! -n "$1" ] ;then
    clear
    echo ""
    echo ""
    echo "    没有输入网址，输入为空"
    exit
fi

url="$1"
str="://"
if [[ $url =~ $str ]] ;then
    echo ""
    echo ""
    echo "    输入的网址为 $url"
else
    echo ""
    echo ""
    echo "    输入网址格式错误，设定为 http:// 或者 https:// 开头"
    exit
fi

host=$(echo $url | awk -F'[|/]' '{print $3}')

echo ""
echo ""
echo "==============  服务器端务必更新 index.html 文件  ================"
echo ""
echo ""

# rm -rf /opt/www/index.html
# wget -q -P "/opt/www" "$url"
# sed -i "s/http:\/\/ys138.win\///g" `grep -rl "index.html" /opt/www`
# sed -i "s/https:\/\/ys138.win\///g" `grep -rl "index.html" /opt/www`

echo ""
echo ""
echo "====================== 抓取站点: $url   ======================="
echo ""
echo ""

wget -r -p -np -k  --no-check-certificate "$url"

php -f /home/wwwroot/default/wpdb/all.php
# wget -c -r -np -k --no-check-certificate --mirror --exclude-directories /wp-includes/,/wp-content/themes/,/wp-json/ / -i /home/wwwroot/default/wpdb/new.txt
# wget -c -r -np -k --no-check-certificate --mirror --exclude-directories /wp-includes/,/wp-content/themes/,/wp-json/ / -i page.txt

clear
echo "====================== $url 下载成功  ========================="
echo ""
echo ""
echo "================= 更换 *.js 和 *.css 文件名   ================="
echo ""
echo ""

find /root/"$host" -type f -name "*.css?*" |
while read name; do
echo $name
newName=$(echo $name | awk -F'[|?]' '{print $1}')
echo  $newName
mv $name $newName
done

find /root/"$host" -type f -name "*.js?*" |
while read name; do
echo $name
newName=$(echo $name | awk -F'[|?]' '{print $1}')
echo  $newName
mv $name $newName
done

echo ""
echo ""
echo "============  删除 $host 所有的 index.html?* 文件  ============="
echo ""
echo ""

find "/root/$host" -name "index.html?*" -exec rm -rf {} \;

echo ""
echo ""
echo "========   替换所有  index.html 文件中的 index.php?p   ========="
echo ""
echo ""

sed -i 's/index.html?p=/index.php?p=/g' `grep -rl "index.html" /root/$host`
sed -i 's/href=\"http:\/\/ys138.win\/201/href="..\/..\/201/g' `grep -rl "index.html" /root/ys138.win/page`
sed -i 's/href=\"http:\/\/ys138.win\//href=\"..\/..\//g' `grep -rl "index.html" /root/ys138.win/page`
sed -i "s/href='http:\/\/ys138.win\//href='..\/..\//g" `grep -rl "index.html" /root/ys138.win/page`
sed -i "s/src='http:\/\/ys138.win\//src='..\/..\//g" `grep -rl "index.html" /root/ys138.win/page`

sed -i "s/href=\"..\/..\/app\/\"/href=\"http:\/\/ys138.win\/app\/\"/g" `grep -rl "index.html" /root/ys138.win/page`
sed -i "s/href=\"..\/..\/ss\/\"/href=\"http:\/\/ys138.win\/ss\/\"/g" `grep -rl "index.html" /root/ys138.win/page`

echo ""
echo ""
echo "======  打包 $host 为 zip 文件, 移动 $host 到 /root/www/ ======"
echo ""
echo ""

zip -r -q $host.zip $host

if [ ! -d /root/www/$host ] ;then
        mkdir -p /root/www/$host
        mkdir -p /root/pub
fi
rm -rf /root/www/$host/*
mv $host /root/www/
mv /root/pub/$host.zip /root/pub/$host.zip.bak
mv $host.zip /root/pub/

echo ""
echo ""
echo "============ 复制文件到 web 目录  $wwwroot/html/ ============="
echo ""
echo ""

mkdir $wwwroot/html
rm -rf $wwwroot/html/*
cp -r /root/www/ys138.win/* $wwwroot/html/

echo ""
echo ""
echo "=================== 修改 html 目录属性... ====================="
echo ""
echo ""

chmod -R 0777 $wwwroot/html

echo ""
echo ""
echo "=============== 精简网页，更换 URL 为相对链接 ================"
echo ""
echo ""

sed -i "s/http:\/\/ys138.win\//..\/..\/..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/2011`
sed -i "s/https:\/\/ys138.win\//..\/..\/..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/2011`
sed -i "s/http:\/\/ys138.win\//..\/..\/..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/2016`
sed -i "s/https:\/\/ys138.win\//..\/..\/..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/2016`
sed -i "s/http:\/\/ys138.win\//..\/..\/..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/2017`
sed -i "s/https:\/\/ys138.win\//..\/..\/..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/2017`
sed -i "s/http:\/\/ys138.win\//..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/page`
sed -i "s/https:\/\/ys138.win\//..\/..\//g" `grep -rl "index.html" /home/wwwroot/default/html/page`
sed -i "s/http:\/\/ys138.win\///g" "/home/wwwroot/default/html/index.html"
sed -i "s/https:\/\/ys138.win\///g" "/home/wwwroot/default/html/index.html"

#sed -i "s/id=\"searchform\" action=\"..\/..\/\">/id=\"searchform\" action=\"https:\/\/ys138.win\/\">/g" `grep -rl "index.html" /home/wwwroot/default/html`
#sed -i "s/ys138.win/ysuo.org/g" `grep -rl "index.html" /home/wwwroot/default/html`
#sed -i "s/<a href=\"http:\/\/ysuo.org\">/<a href=\"http:\/\/ys138.win\">/g" `grep -rl "index.html" /home/wwwroot/default/html`

php -f $wwwroot/cc.php

echo ""
echo ""
echo "======================  复制索引文件... ======================="
echo ""
echo ""

cp $wwwroot/wpdb/getid.php $wwwroot/html/index.php
cp $wwwroot/wpdb/url.txt $wwwroot/html/

echo ""
echo ""
echo "===============  修改 $wwwroot/html 文件属性 ================="
echo ""
echo ""

cd $wwwroot/html
find -type d|xargs chmod 755
find -type f|xargs chmod 644


