<?php
/**
 * 用途:cURL模拟登录Wordpress导出数据
 * 第 104 行需要设置 $localurl = 'http://ysuo.org';
 *
 *
 */

set_time_limit(0);

# 设置网站地址和密码
$host = 'https://ys138.win/';
$user = 'admin';        	# 账号
$passwd = 'passwd';      # 密码

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
if($n < 6) die(' <br><b>Logon failure: unknown user name or bad password</b>');

# 导出数据
$xml = getdb($redirect_url, $cookie_file);
date_default_timezone_set('Asia/Shanghai');
$dbfn = 'wordpress.' . date("Y-m-d") . '.xml';
file_put_contents($dbfn, $xml);
//@unlink($cookie_file);
echo '<br><div>&nbsp; <b>Successful Data Backup</b><a href=./' . $dbfn . '>  ' . $dbfn . '</a>';

/* *
   * 从 wordpress 的备份数据中提取 id 和 url
   * 适用于 wget 提取的 wordpress 静态镜像
   *
*/

$yshost = $host;
@unlink('url.txt');
$date = date("Y-m-d");  
$xmlfile = 'wordpress.' .$date. '.xml';  # wordpress备份文件

$host = 'arubacloud.ys138.win';                    # 不带http或者https，最后边不加 /
if(file_exists('url.txt') == false){
    if(file_exists($xmlfile) == false)die('backupfileisnotexists');
    $c = file_get_contents($xmlfile);
    $line = explode("\n", $c);
    $ny = count($line);
    $item = '';
    $url = '';
    for($y = 0;$y < $ny;$y++){
        if(strpos($line[$y], '<item>'))$item .= "<itemline=$y>\r\n";
        if(strpos($line[$y], '<wp:post_id>')){
            $item .= $line[$y] . "\r\n";
            $postid = str_replace(array('<wp:post_id>', '</wp:post_id>', '<![CDATA[', ']]>'), '', $line[$y]);
            $url .= trim($postid) . ' ';
        }
        if(strpos($line[$y], '<wp:post_date>')){
            $item .= $line[$y] . "\r\n";
            $date = str_replace(array('<wp:post_date>', '</wp:post_date>', '<![CDATA[', ']]>'), '', $line[$y]);
            $postdate = explode(' ', $date);
            $date = str_replace('-', '/', $postdate['0']) . '/';
            $url .= trim($date);
        }
        if(strpos($line[$y], '<wp:post_name>')){
            $item .= $line[$y] . "\r\n";
            $postname = str_replace(array('<wp:post_name>', '</wp:post_name>', '<![CDATA[', ']]>'), '', $line[$y]);
            $url .= trim($postname) . "/\r\n";
        }
        if(strpos($line[$y], '</item>'))$item .= "</item>\r\n";
    }
    // file_put_contents('item.txt',$item); # 精简的 item
    file_put_contents('url.txt', $url);     # 包含 id 和 url 
    }
$url = file_get_contents('url.txt');
$arr_url = array();
$array = explode("\r\n", $url);
$nz = count($array)-1;
for($z = 0;$z < $nz;$z++){
    $array_url = explode(' ', $array[$z]);
    $arr_url = $arr_url + array($array_url['0'] => $array_url['1']);
}
// print_r($arr_url);  # $arr_url 是 $url 的数组格式
if(isset($_GET['p'])){
    $scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))?'https://':'http://';
    $url = $scheme . $host . '/' . $arr_url[$_GET['p']];
	echo $url;
    header('Location:' . $url);
}

echo "<br><br>\r\n  <div>&nbsp; <a href='url.txt'>url.txt</a>  create success <br><br>\r\n";

/* *
   * 生成 wget 尚未提取的 wordpress 日志
   *
*/

$localurl = 'http://ysuo.org';

$index = getdb($localurl, $cookie_file);
$arr_index = explode('<article id', $index);
$arr_title = explode('<h3 class="entry-title h4">', $arr_index[1]);
$arr_content = explode('<div class="entry-content', $arr_title[1]);
$arr_link = explode('"', $arr_content[0]);
$link = $yshost . $arr_link[1];
$link = del13($link);

@unlink('old');
file_put_contents('old', $link);

$old = file_get_contents('old');
$old = del13($old);
$arr_old = explode("\r\n", $old);
$n = count($arr_old) - 1;
$oldurl = $arr_old[$n];

$arr_date = explode("/", $oldurl);
$host_url = $arr_date[0].'//'.$arr_date[2].'/';
$date_old = $arr_date[3].$arr_date[4].$arr_date[5];

echo "<div>&nbsp; old date: <b>" . $date_old . "</b><br><br>\r\n";
unset($old);
unset($arr_old);
unset($n);
unset($oldurl);
unset($arr_date);
# ------------------------- 
$new = file_get_contents('url.txt');
$new = del13($new);
$arr_new = explode("\r\n", $new);
$n = count($arr_new);
$url = '';
for($i = 0; $i < $n ; $i++){
	$arr = explode(" ", $arr_new[$i]);
	$arr_date = explode("/", $arr[1]);	
	$date_new = $arr_date[0].$arr_date[1].$arr_date[2];
	if($date_old <=  $date_new) $url .= trim($host_url) . trim($arr[1]) . "\r\n";
}
unset($new);
unset($arr_new);
unset($i);
unset($n);
unset($arr);

@unlink('new.txt');

$url = del13($url);

# 检测URL是否有效
$arr = explode("\r\n", $url);
$n = count($arr);
$url = '';
for($i = 0; $i < $n ; $i++){
	if(chkurl($arr[$i]) == true) {
		echo '<div>&nbsp; ' . $arr[$i] . "<br>\r\n";
		$url .= $arr[$i] . "\r\n";
	}
}
file_put_contents('new.txt', $url);


$xmlpath = getcwd() . $xmlfile;
echo $xmlfile;
$user = "nobody";
@chgrp($xmlfile, $user);
@chown($xmlfile, $user);
@chmod($xmlfile, 0000);
@unlink($cookie_file);

echo "<br><br><div>&nbsp; <b>done  </b> <a href='new.txt'>new.txt</a>";


# 相关函数

# 删除首位空格和多余的回车
function del13($str){
	$str = trim($str);
	$str = str_replace("\n\n", "\r\n", $str);
	$str = str_replace("\r\n\r\n", "\r\n", $str);
	return $str;
}

# php使用curl判断404
function chkurl($url){
	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);//设置超时时间
	curl_setopt($handle, CURLOPT_HEADER, 0);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

	curl_exec($handle);
	//检查是否404（网页找不到）
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	if($httpCode == 404) {
		return false;
	}else{
		return true;
	}
	curl_close($handle);
}

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

