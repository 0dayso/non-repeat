<?php
/* *
   * 从 wordpress 的备份数据中提取 id 和 url
   * 适用于 wget 提取的 wordpress 静态镜像
   *
*/
  
$xmlfile = 'wordpress.2017-05-10.xml';  # wordpress备份文件
$host = 'ys138.win';                    # 不带http或者https，最后边不加 /
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
