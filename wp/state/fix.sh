# 
cd /home/wwwroot/default/html/wp-content/
zip -r uploads.zip uploads
cp /root/wp/uploads.zip /home/wwwroot/default/html/wp-content/

cd /root/wp/

unzip wp-js-css.zip
unzip uploads.zip

rm -rf /home/wwwroot/default/html/*
cp -r /root/www/ys138.win/* /home/wwwroot/default/html/
cp -r wp-js-css/* /home/wwwroot/default/html/
cp -r uploads /home/wwwroot/default/html/wp-content/

chmod -R 0777 /home/wwwroot/default/html

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

php -f /home/wwwroot/default/cc.php

cd /home/wwwroot/default/html

find -type d|xargs chmod 755
find -type f|xargs chmod 644

cp -r 201* /home/wwwroot/default/
cp -r page /home/wwwroot/default/
cp -r category /home/wwwroot/default/
cp -r index.* /home/wwwroot/default/
cp -r favicon.ico /home/wwwroot/default/

# 到这里完毕

cp -r /home/wwwroot/default/html/wp-content /home/wwwroot/
cp -r /home/wwwroot/default/html/wp-includes /home/wwwroot/

rm -rf /home/wwwroot/default/html/*

cp -r /home/wwwroot/default/201* /home/wwwroot/default/html/
cp -r /home/wwwroot/default/page /home/wwwroot/default/html/
cp -r /home/wwwroot/default/category /home/wwwroot/default/html/
cp -r /home/wwwroot/default/index.* /home/wwwroot/default/html/
cp -r /home/wwwroot/default/favicon.ico /home/wwwroot/default/html/
mv /home/wwwroot/wp-content /home/wwwroot/default/html/
mv /home/wwwroot/wp-includes /home/wwwroot/default/html/



