#!/bin/bash

www="/root/www"
ftppub="/root/pub"
ip=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`

# 脚本所在目录
basepath=$(cd `dirname $0`; pwd)
cd $basepath

if egrep "CentOS release 6" /etc/redhat-release > /dev/null
then
    cat /etc/redhat-release
else
    echo "This shell applies only to CentOS 6"
    exit
fi

echo "" && echo "======== system update ========" && echo ""
yum update
yum install epel-release

clear
echo "" && echo "======== install chinese-support ========" && echo ""
yum -y groupinstall chinese-support
touch /etc/sysconfig/i18n
echo LANG="zh_CN.UTF-8" > /etc/sysconfig/i18n
echo LANGUAGE="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN" >> /etc/sysconfig/i18n
echo SUPPORTED="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN:zh:en_US.UTF-8:en_US:en" >> /etc/sysconfig/i18n
echo SYSFONT="lat0-sun16" >> /etc/sysconfig/i18n
echo export LC_ALL="zh_CN.UTF-8" >> /etc/sysconfig/i18n

clear
echo "" && echo "======== install Development Tools ========" && echo ""
yum install -y wget curl git vim zip unzip
yum install -y libjpeg-devel libpng-devel libtiff-devel freetype-devel pam-devel gettext-devel pcre-devel
yum install -y libxml2 libxml2-devel libxslt libxslt-devel
yum install -y zlib-devel bzip2-devel xz-devel
yum install -y libpcap-devel openssl-devel ncurses-devel
yum install -y flex bison autoconf automake 
yum groupinstall 'Development Tools'

clear
echo "" && echo "======== install python ========" && echo ""
mkdir python && cd python
git clone https://github.com/pypa/pip.git
git clone https://github.com/pypa/setuptools.git
wget http://pypi.python.org/packages/11/b6/abcb525026a4be042b486df43905d6893fb04f05aac21c32c638e939e447/pip-9.0.1.tar.gz
wget http://pypi.python.org/packages/a9/23/720c7558ba6ad3e0f5ad01e0d6ea2288b486da32f053c73e259f7c392042/setuptools-36.0.1.zip
wget http://pypi.python.org/packages/source/d/distribute/distribute-0.7.3.zip
wget http://pypi.python.org/packages/source/d/distribute/distribute-0.6.10.tar.gz
wget https://www.python.org/ftp/python/3.6.1/Python-3.6.1.tar.xz

wget https://www.python.org/ftp/python/2.7.13/Python-2.7.13.tgz
tar zxf Python-2.7.13.tgz
cd Python-2.7.13
./configure
make && make install

mv /usr/bin/python /usr/bin/python.old
rm -f /usr/bin/python-config
ln -s /usr/local/bin/python /usr/bin/python
ln -s /usr/local/bin/python-config /usr/bin/python-config
ln -s /usr/local/include/python2.7/ /usr/include/python2.7

cd ..
wget https://bootstrap.pypa.io/get-pip.py
python get-pip.py
cd ..
sed -i 's/#!\/usr\/bin\/python/#!\/usr\/\bin\/python.old/g' "/usr/bin/yum"

clear
echo "" && echo "======== install shadowsocks and IKEv2 ========" && echo ""
mkdir ss && cd ss
pip install git+https://github.com/shadowsocks/shadowsocks.git@master
touch ss.json
echo "{"                                     >> ss.json
echo "\"    server\":\"::\","                >> ss.json
echo "\"    server_port\":11268,"            >> ss.json
echo "\"    local_address\": \"127.0.0.1\"," >> ss.json
echo "\"    local_port\":1080,"              >> ss.json
echo "\"    password\":\"12345678\","        >> ss.json
echo "\"    timeout\":300,"                  >> ss.json
echo "\"    method\":\"aes-256-cfb\","       >> ss.json
echo "\"    fast_open\": false"              >> ss.json
echo "}"                                     >> ss.json

touch start.sh && chmod +x start.sh
echo  "ssserver -c ss.json --user nobody -d start" > start.sh
./start.sh
cd ..

mkdir ikev2 && cd ikev2
yum -y install strongswan strongswan-libipsec

cd ..

clear
echo "" && echo "======== install web tools ========" && echo ""
mkdir web && cd web
wget https://www.apachefriends.org/xampp-files/7.1.6/xampp-linux-x64-7.1.6-0-installer.run
wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
wget http://www.ispconfig.org/downloads/ISPConfig-3.1.5.tar.gz
git clone https://github.com/yourshell/ispconfig_setup.git
git clone https://github.com/dclardy64/ISPConfig-3-Debian-Installer.git
chmod +x xampp-linux-x64-7.1.6-0-installer.run
#tar xvfz ISPConfig-3.1.5.tar.gz
#cd ispconfig3_install/install
#php -q update.php

cd ..

clear
echo "" && echo "======== sshd white list ========" && echo ""
echo "sshd:$ip" >> /etc/hosts.allow
echo "sshd:104.131.150.174" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny
service xinetd restart

yum remove httpd
userdel apache
groupdel apache
groupadd www
groupadd ftp
useradd www -g www -m -d $www -s /sbin/nologin
useradd admin -g ftp -G www -m -d $www -s /sbin/nologin
useradd ftp -g ftp -m -d $ftppub -s /sbin/nologin

clear
echo "" && echo "======== reboot VPS ========" && echo ""
read -s -n1 -p "This command will reboot the system.  Continue?"
echo "Please enter 'yes' or 'no': $REPLY"
if [[ ! $REPLY =~ "yes" ]] ;then
    exit
fi
yum -y update
reboot

