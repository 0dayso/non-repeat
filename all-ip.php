<?php
/**
 *
 * $num = $ta[4];
 * $num = 16777216 ,(32-24=)/8 ,A段
 * $num = 65536 ,(32-16=)/16 ,B段
 * $num = 256 ,(32-8=)/24 ,C段
 *
 * Apanic提供了每日更新的亚太地区IPv4，IPv6，AS号分配的信息表，访问url是
 * http://ftp.apnic.net/apnic/stats/apnic/delegated-apnic-latest
 * 
 * 该文件的格式说明
 * 等级机构|获得该IP段的国家/组织|资源类型|起始IP |IP段长度|分配日期|分配状态
 * apnic   |CN                   |ipv4    |1.2.2.0|256     |20110331|assigned
 * 
 * https://blog.huijiewei.com/note/php-check-china-ip
 * http://www.cnblogs.com/zemliu/archive/2012/09/12/2681089.html
 *
*/

set_time_limit(0);
ini_set('memory_limit', '512M');

$ipjson = 'ip.json';
#$ip = '103.255.251.0';
# echo check_ip($ip, $ipjson);
# $cn = 'CN';
# check_cn($cn, $ipjson)

echo '<br> Old data files are deleted. Start converting data ...<br><br>';
get_ip_table($ipjson);
echo '<br> done';

# 用国家代码查询IP 
function check_cn($cn, $ipjson){
	$cn = strtoupper($cn);
	if (!file_exists($ipjson)) die(' ip.json file does not exist');
    $s = file_get_contents($ipjson);
    $tb = json_decode($s,true);
	unset($s);
	foreach($tb as $c){
         $cn = array_search_re($cn, $c);
         if(isset($cn)){
             foreach($cn as $d){
                 return $d[1] . '.' . $d[2] . '.' . $d[4] . ".0\r\n<br>";
                 }
             }
         }
    }
	
# 多维数组查询
function array_search_re($needle, $haystack, $a = 0, $nodes_temp = array()){
    global$nodes_found;
    $a++;
    foreach($haystack as $key1 => $value1){
        $nodes_temp[$a] = $key1;
        if(is_array($value1)){
            array_search_re($needle, $value1, $a, $nodes_temp);
            }
        elseif($value1 === $needle){
            $nodes_found[] = $nodes_temp;
            }
        }
    return $nodes_found;
    }

# 用IP查找国家
function check_ip($ip, $ipjson){
	if (!file_exists($ipjson)) die(' ip.json file does not exist');
    $ip_addr = explode('.', $ip);
    if(count($ip_addr) < 4) return false;
    $a1 = (int)$ip_addr[0];
    $a2 = (int)$ip_addr[1];
    $a3 = (int)$ip_addr[2];
    $a4 = (int)$ip_addr[3];
    $s = file_get_contents($ipjson);
    $tb = json_decode($s,true);
	unset($s);
	# print_r($tb);
	foreach($tb as $c){
		if(isset( $c[$a1][$a2][0][$a3])) {
			$cn = $c[$a1][$a2][0][$a3];
			return $ip .' '. $cn;
		}else{
			return false;
		}
	}
}

# 生成 json 格式数据
function get_ip_table($ipjson){
	if (file_exists($ipjson)) unlink($ipjson);
	if (file_exists('much-' . $ipjson)) unlink('much-' . $ipjson);
	if (!file_exists('delegated-apnic-latest.txt')) die('delegated-apnic-latest  file does not exist');
    $tb = file_get_contents('delegated-apnic-latest.txt');
    $tb_array = explode("\n", $tb);
    unset($tb);
	
    $muchip = '';
    foreach($tb_array as $t){
        $ta = explode("|", $t);
        if(count($ta) >= 7 and $ta[2] == 'ipv4'){ // $ta[1]=='CN'&&
            if($ta[4] > 16777216){
                echo $muchip .= $ta[3] . "<b> $ta[4] </b>too much IP in line. <br>\r\n";
                continue;
            }
            $sip_addr = explode(".", $ta[3]);
            $sip1 = (int)$sip_addr[0];
            $sip2 = (int)$sip_addr[1];
            $sip3 = (int)$sip_addr[2];
            $sip4 = (int)$sip_addr[3];
            $ip = cal_ip($ta[3], $ta[4]);
            $endip = $ip['eip'];
            $eip_addr = explode(".", $endip);
            $eip1 = (int)$eip_addr[0];
            $eip2 = (int)$eip_addr[1];
            $eip3 = (int)$eip_addr[2];
            $eip4 = (int)$eip_addr[3];
			
			# 生成 JSON 数据
            $ip1 = $eip1 - $sip1;
            $ip2 = $eip2 - $sip2;
            $ip3 = $eip3 - $sip3;
            $tb = array();
            $mip3 = array();
            if($ip1 > 0) {
				echo $muchip .= $ta[3] . "<b>  $ta[4] </b> IP[1] > 1. <br>\r\n";
                continue;
			}
            if($ip1 == 0 and $ip2 == 0){
                for($i = 0;$i <= $ip3;$i++){
                    $x = $sip3 + $i;
                    $mip3 = $mip3 + array($x => $ta[1]);
                }
                $tb[$sip1][$sip2] = array($mip3);
            }
            if($ip1 == 0 and $ip2 > 0){
                for($i = 0;$i <= $ip2;$i++){
                    if($i == 0){
                        $mip2 = $sip2 + $i;
                        $nip3 = 256 - $sip3;
                        for($n = 0;$n < $nip3;$n++){
                            $x = $sip3 + $n;
                            $mip3 = $mip3 + array($x => $ta[1]);
                        }
                        $tb[$sip1][$mip2] = array($mip3);
                        continue;
                    }
                    $mip2 = $sip2 + $i;
                    for($m = 0;$m < 256;$m++){
                        $mip3 = $mip3 + array($m => $ta[1]);
                    }
                    $tb[$sip1][$mip2] = array($mip3);
                }
            }
		# print_r($tb);
		$jsip = json_encode($tb) . ',';
		file_put_contents($ipjson, $jsip, FILE_APPEND);	
        }
    }
	# 修改组合后的json数据，方便解析
	unset($jsip);
	$data = file_get_contents($ipjson);
	$data = '[' . trim($data) . ']';
	$data = str_replace(',]', ']', $data);
	file_put_contents($ipjson, $data);
	if(!empty($muchip)) file_put_contents('much-' . $ipjson, $muchip, FILE_APPEND);
}

# 计算最终IP
function cal_ip($sip, $num){
    $sip_addr = explode(".", $sip);
    $a1 = str_pad(decbin($sip_addr[0]), 8, 0, STR_PAD_LEFT);
    $a2 = str_pad(decbin($sip_addr[1]), 8, 0, STR_PAD_LEFT);
    $a3 = str_pad(decbin($sip_addr[2]), 8, 0, STR_PAD_LEFT);
    $a4 = str_pad(decbin($sip_addr[3]), 8, 0, STR_PAD_LEFT);
    $sipbit = $a1 . $a2 . $a3 . $a4;

    $log = log($num, 2);
    $len = 32 - $log;
    $bit = '';
    for($i = 0;$i < $len;$i++)$bit .= 1;
    $maskbit = str_pad($bit, 32, 0, STR_PAD_RIGHT);
    $mask_addr = str_split($maskbit, 8);
    $mask = bindec($mask_addr[0]) . '.' . bindec($mask_addr[1]) . '.' . bindec($mask_addr[2]) . '.' . bindec($mask_addr[3]);
    $maskbit_left = str_pad(decbin($num - 1), 32, 0, STR_PAD_LEFT);

	$eipbit = binary_plus($sipbit, $maskbit_left);
    $eip_addr = str_split($eipbit, 8);
    $eip = bindec($eip_addr[0]) . '.' . bindec($eip_addr[1]) . '.' . bindec($eip_addr[2]) . '.' . bindec($eip_addr[3]);
    $ip_array = array('sip' => $sip, 'eip' => $eip, 'mask' => $mask, 'len' => $len, );
    return $ip_array;
}

# 二进制相加 
function binary_plus($binstr1, $binstr2){
    $bin_arr1 = str_split($binstr1);
    $bin_arr2 = str_split($binstr2);
    $arr_len1 = count($bin_arr1);
    $arr_len2 = count($bin_arr2);
    $sum_arr = array();
    if($arr_len1 < $arr_len2){
        $short_arr = & $bin_arr1;
    }else{
        $short_arr = & $bin_arr2;
    }
	
    # 将两个数组的长度补到一样长，短数组在前面补0
    for($i = 0;$i < abs($arr_len1 - $arr_len2);$i++){
        array_unshift($short_arr, 0);
    }
    $carry = 0;
	
    # 进位标记
    for($i = count($bin_arr1)-1;$i >= 0;$i--){
        $result = $bin_arr1[$i] + $bin_arr2[$i] + $carry;
        switch($result){
        case 0:array_unshift($sum_arr, 0);
        $carry = 0;
        break;
        case 1:array_unshift($sum_arr, 1);
        $carry = 0;
        break;
        case 2:array_unshift($sum_arr, 0);
        $carry = 1;
        break;
        case 3:array_unshift($sum_arr, 1);
        $carry = 1;
        break;
        default:die();
        }
    }
    if($carry == 1){
        array_unshift($sum_arr, 1);
    }
    return implode("", $sum_arr);
}
?>
