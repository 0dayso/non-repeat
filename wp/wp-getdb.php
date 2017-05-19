<?php
/**
 * 用途:cURL模拟登录Wordpress导出数据
 */

set_time_limit(0);

# 设置网站地址和密码
$host = 'http://ys138.win/';
$user = '*******';        # 账号
$passwd = '*********';     # 密码

# 设置要访问的目标
$redirect_url = $host . 'wp-admin/export.php?download=true&content=all&cat=0&post_author=0&post_start_date=0&post_end_date=0&post_status=0&page_author=0&page_start_date=0&page_end_date=0&page_status=0&attachment_start_date=0&attachment_end_date=0&submit=%E4%B8%8B%E8%BD%BD%E5%AF%BC%E5%87%BA%E7%9A%84%E6%96%87%E4%BB%B6';

# 建立一个cookie临时存储文件
$cookie_file = tempnam('./temp', 'cookie');

# 登录获取cookie
$login_url = $host . 'wp-login.php';
$post_fields = 'log=' . $user . '&pwd=' . $passwd . '&rememberme=forever&redirect_to=' . $host . '&testcookie=1';
$result = login($login_url, $cookie_file, $post_fields);
$header = file($cookie_file);
$n = count($header);
if($n < 6) die(' <br><center><b>Logon failure: unknown user name or bad password</b></center>');

# 导出数据
$xml = getdb($redirect_url, $cookie_file);
date_default_timezone_set('Asia/Shanghai');
$dbfn = 'wordpress.' . date("Y-m-d") . '.xml';
file_put_contents($dbfn, $xml);
@unlink($cookie_file);
echo ' <br><center><b>Successful Data Backup</b><a href=./' . $dbfn . '>  ' . $dbfn . '</a></center>';

# 相关函数
function array_header($result){
	$array = explode("\r\n\r\n", $result, 2);
	$header = explode("\r\n", $array['0']);
	$array_header = array();
	$n = count($header);
	for ($i = 1; $i < $n; $i++){
		$elements = explode(":", $header[$i], 2);
		$array_header = $array_header + array($elements['0'] => $elements['1']);
	}
	return $array_header;
}
function login($url, $cookie, $post){
    global$host;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . ipv4()));
    curl_setopt($ch, CURLOPT_REFERER, $host);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
    }
function getdb($url, $cookie){
    global$host;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . ipv4()));
    curl_setopt($ch, CURLOPT_REFERER, $host);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
    }
function ipv4(){
    $a = (int)unit();
    $b = '.' . (int)unit();
    $c = '.' . (int)unit() . '.';
    $d = (int)unit();
    if($a === 0) $a = '120';
    if($d === 0) $d = '254';
    $ip = $a . $b . $c . $d;
    return $ip;
    }
function unit(){
    $one = mt_rand(0, 2);
    if($one == 2){
        $two = mt_rand(0, 5);
        $three = mt_rand(0, 5);
        }else{
        $two = mt_rand(0, 9);
        $three = mt_rand(0, 9);
        }
    return $one . $two . $three;
    }

