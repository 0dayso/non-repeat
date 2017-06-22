<?php
/**
 * ��;:cURLģ���¼Wordpress��������
 * �� 98~120 ��������ҳ�������� ID ==> URL ������, ���Ϊ index.php
 */

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d"); 
customize_flush();

# ����wordpress��ַ���˻�������
$wp_host = 'https://ys138.win/';                 # wordpress��ַ
$user = 'admin';        	                     # �˺�
$passwd = 'password ';                           # ����
$xmlfile = 'wordpress.' . $date . '.xml';        # xml �ļ���

$static_host = 'ysuo.org';                       # ��̬��������������http����https�����߲��� /
$static_host = $_SERVER['HTTP_HOST'];
$scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))?'https://':'http://';

$cookie_file = tempnam('./temp', 'cookie');      # cookie��ʱ�洢�ļ�
$id_url_log = 'id-url-' . $date . '.log';        # ��¼ ID��Ӧ URL���ļ�
$old_log = 'old.log';                            # ��ǰ���µĵ�һƪ�ļ���ַ
$new_log = 'new.txt';                            # ��δ��̬���� URL

/**------- ������������ -------*/
# ����Ҫ���ʵ�Ŀ��
$redirect_url = $wp_host . 'wp-admin/export.php?download=true&content=all&cat=0&post_author=0&post_start_date=0&post_end_date=0&post_status=0&page_author=0&page_start_date=0&page_end_date=0&page_status=0&attachment_start_date=0&attachment_end_date=0&submit=%E4%B8%8B%E8%BD%BD%E5%AF%BC%E5%87%BA%E7%9A%84%E6%96%87%E4%BB%B6';

# ��¼��ȡcookie
$login_url = $wp_host . 'wp-login.php';
$post_fields = 'log=' . $user . '&pwd=' . $passwd . '&rememberme=forever&redirect_to=' . $wp_host . '&testcookie=1';
$result = login($login_url, $cookie_file, $post_fields);
$header = file($cookie_file);
$n = count($header);
if($n < 6) die(' <br><b>Logon failure: unknown user name or bad password</b>');

# ��������
$xml = getdb($redirect_url, $cookie_file);
if(file_exists($xmlfile) == true){
	@chmod($xmlfile, 0777);
	@unlink($xmlfile);
}
file_put_contents($xmlfile, $xml);
//@unlink($cookie_file);
echo '<br><div>&nbsp; <b>Successful Data Backup</b><a href=./' . $xmlfile . '>  ' . $xmlfile . '</a>';

/* *
   * �� wordpress �ı�����������ȡ id �� url
   * ������ wget ��ȡ�� wordpress ��̬����
   *
*/

if(file_exists($xmlfile) == false) die('backup file is not exists');
$line = explode("\n", $xml);
$ny = count($line);
$xml_item = '';
$id_url_path = '';
for($y = 0;$y < $ny;$y++){
    if(strpos($line[$y], '<item>')) $xml_item .= "<itemline=$y>\r\n";
    if(strpos($line[$y], '<wp:post_id>')){
        $xml_item .= $line[$y] . "\r\n";
        $postid = str_replace(array('<wp:post_id>', '</wp:post_id>', '<![CDATA[', ']]>'), '', $line[$y]);
        $id_url_path .= trim($postid) . ' ';
    }
    if(strpos($line[$y], '<wp:post_date>')){
        $xml_item .= $line[$y] . "\r\n";
        $date = str_replace(array('<wp:post_date>', '</wp:post_date>', '<![CDATA[', ']]>'), '', $line[$y]);
        $postdate = explode(' ', $date);
        $date = str_replace('-', '/', $postdate['0']) . '/';
        $id_url_path .= trim($date);
    }
    if(strpos($line[$y], '<wp:post_name>')){
        $xml_item .= $line[$y] . "\r\n";
        $postname = str_replace(array('<wp:post_name>', '</wp:post_name>', '<![CDATA[', ']]>'), '', $line[$y]);
        $id_url_path .= trim($postname) . "/\r\n";
    }
    if(strpos($line[$y], '</item>')) $xml_item .= "</item>\r\n";
}
// file_put_contents('item.txt',$xml_item); # ����� item
if(file_exists($id_url_log) == true) @unlink($id_url_log);
file_put_contents($id_url_log, $id_url_path);     # ���� id �� url 
echo '<br><br><div>&nbsp; <a href="' . $id_url_log . '">' . $id_url_log . "</a>  create success <br><br>\r\n";

$xmlpath = getcwd() . $xmlfile;
$user = trim(shell_exec('whoami'));
@chgrp($xmlfile, $user);
@chown($xmlfile, $user);
@chmod($xmlfile, 0000);
@unlink($cookie_file);

/** 
 * ���´������Ϊ index.php
 * ������ҳ�������� ID ==> URL ������
*/

/**
<?php
$static_host = 'ysuo.org';                              # ��̬��������������http����https�����߲��� /
$static_host = $_SERVER['HTTP_HOST'];
$id_url_log = 'id-url-' . '2017-06-21' . '.log';        # ��¼ ID��Ӧ URL���ļ�

# $id_url_path �������ʽ $arr_url
if(file_exists($id_url_log) == false) die('file is not exists');
$id_url_path = file_get_contents($id_url_log);
$array = explode("\r\n", $id_url_path);
$nz = count($array)-1;
$arr_url = array();
for($z = 0;$z < $nz;$z++){
    $array_url = explode(' ', $array[$z]);
    $arr_url = $arr_url + array($array_url['0'] => $array_url['1']);
}
// print_r($arr_url);
# $arr_url[$_GET['p']] ���� ID ��Ӧ��URL ,����$static_host/ ����Ĭ�ϵ��� index.php �ļ�
if(isset($_GET['p'])){
    $scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))?'https://':'http://';
    $static_url = $scheme . $static_host . '/' . $arr_url[$_GET['p']];
	echo $static_url;
    header('Location:' . $static_url);
}
*/





/* *
   * ���� wget ��δ��ȡ�� wordpress ��־
   *
*/

# ��ȡ��̬�������ҳ����ȡ��ǰ�����ļ������ڡ�Ҳ����ʹ��Ŀ¼ɨ��İ취
$index = file_get_contents($scheme . $static_host);
$arr_index = explode('<article id', $index);
$arr_title = explode('<h3 class="entry-title h4">', $arr_index[1]);
$arr_content = explode('<div class="entry-content', $arr_title[1]);
$arr_link = explode('"', $arr_content[0]);
$link = del13($wp_host . $arr_link[1]);

$arr_old = explode("\r\n", $link);
$n = count($arr_old) - 1;
$oldurl = $arr_old[$n];
$arr_date = explode("/", $oldurl);
$host_url = $arr_date[0].'//'.$arr_date[2].'/';
$date_old = $arr_date[3].$arr_date[4].$arr_date[5];

if(file_exists($old_log) == true) @unlink($old_log);
file_put_contents($old_log, $link);
echo " $link <div>&nbsp; old date: <b>" . $date_old . "</b><br><br>\r\n";

unset($n);
unset($oldurl);
unset($arr_date);
unset($xml);

# ��ȡ$wp_host��û�о�̬���������б�
$arr_new = explode("\r\n", $id_url_path);
$n = count($arr_new);
$url = '';
for($i = 0; $i < $n ; $i++){
	$arr = explode(" ", $arr_new[$i]);
	$arr_date = explode("/", $arr[1]);	
	$date_new = $arr_date[0].$arr_date[1].$arr_date[2];
	if($date_old <=  $date_new) $url .= trim($host_url) . trim($arr[1]) . "\r\n";
}
unset($id_url_path);
unset($arr_new);
unset($i);
unset($n);
unset($arr);

$url = del13($url);

# ���$wp_host�е�URL�Ƿ���Ч
$arr = explode("\r\n", $url);
$n = count($arr);
$url = '';
for($i = 0; $i < $n ; $i++){
	if(chkurl($arr[$i]) == true) {
		echo '<div>&nbsp; ' . $arr[$i] . "<br>\r\n";
		$url .= $arr[$i] . "\r\n";
	}
}

if(file_exists($new_log) == true) @unlink($new_log);
file_put_contents($new_log, $url);
echo '<br><br><div>&nbsp; <b>done  </b> <a href="' .$new_log. '">' .$new_log. '</a>';



# ��غ���

# ɾ����λ�ո�Ͷ���Ļس�
function del13($str){
	$str = trim($str);
	$str = str_replace("\n\n", "\r\n", $str);
	$str = str_replace("\r\n\r\n", "\r\n", $str);
	return $str;
}

# ������ҳ�� HEADER
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

# ��IP��Ԫ�������IP
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

# ����IP��Ԫ
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

# ˢ�»���
function customize_flush(){
    if(php_sapi_name() === 'cli'){
	return true;
	}else{
        echo(str_repeat(' ',256));
        // check that buffer is actually set before flushing
        if (ob_get_length()){           
            @ob_flush();
            @flush();
            @ob_end_flush();
        }   
        @ob_start();
	}
}

# ���curl��SSL����֤��֤ȱ�ݣ�����ʹ��file_get_contents��ȡ��ҳ�ı�ͷ������
# ��֤�Ƿ��� SSL �������� https:// ģʽ
function is_SSL($https){
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );  
    $response = file_get_contents($https, false, stream_context_create($arrContextOptions));
	$headers = $http_response_header;
	$num = count($headers);
	$str = '';
	for($i = 0; $i < $num ;$i++){
		$str .= $headers[$i] . "\r\n";
	}
    $response = $str ."\r\n" .$response;
	if(strstr($headers[0] , '200')) return 'https://';
	else return 'http://';
}

# ͨ��CURL����HEADER,����SSL��ȱ��
function get_web_page( $url ){
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 1200,      // timeout on connect
        CURLOPT_TIMEOUT        => 1200,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false,    // Disabled SSL Cert checks
		CURLOPT_SSL_VERIFYHOST => 0,
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
	echo $content;
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

# ��¼�ʺţ����� $cookie
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
	
# �� $cookie �����˺������ļ�
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

# �� 404 �ж���ҳ�Ƿ����
function chkurl($url){
	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 1200);//���ó�ʱʱ��
	curl_setopt($handle, CURLOPT_TIMEOUT, 1200);       //���ó�ʱʱ��
	curl_setopt($handle, CURLOPT_HEADER, 0);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

	curl_exec($handle);
	//����Ƿ�404����ҳ�Ҳ�����
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	if($httpCode == 404) {
		return false;
	}else{
		return true;
	}
	curl_close($handle);
}
