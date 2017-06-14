#!/bin/bash

if [ ! -n "$1" ] ;then
    echo " 没有输入网址，输入为空"
    exit
fi

url="$1"
str="://"
if [[ $url =~ $str ]] ;then
    echo "输入的网址为 $url"
else
    echo "输入网址格式错误，设定为 http:// 或者 https:// 开头"
    exit
fi
host=$(echo $url | awk -F'[|/]' '{print $3}')
echo 更新 /opt/www/index.html 文件
mv /opt/www/index.html /opt/www/index.html.bak
wget -q -P "/opt/www" "$url"
echo "抓取站点: $url"
wget -r -p -np -k -q "$url"
echo "下载成功 $url"

echo "更换 *.js 和 *.css 文件名"
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

echo "删除 $host 所有的 index.html?* "
find "/root/$host" -name "index.html?*" -exec rm -rf {} \;

echo replace link in all index.html
sed -i "s/index.php?p=/index.php?p=/g" `grep -rl "index.html" /root/"$host"`

echo 
echo "打包 $host 为 zip 文件, 移动 $host 到 /root/"
zip -r -q "$host.zip" "$host"

if [ ! -d "/root/www/$host" ] ;then
        mkdir -p "/root/www/$host"
        mkdir -p /root/pub
fi
rm -rf "/root/www/$host/*"
mv "$host" /root/www/
mv "/root/pub/$host.zip" "/root/pub/$host.zip.bak"
mv "$host.zip" /root/pub/
