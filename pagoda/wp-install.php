<?php
/*
	WordPress Installer for Pagoda Box v1.05
	Copyright 2012 by Martin Zeitler
	http://codefx.biz/contact
*/
$base_dir = str_replace('/pagoda','', dirname(__FILE__));

/* error reporting */
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_NOTICE);

$logfile=$base_dir.'/logs/deployment.log';
if(!file_exists($logfile)){touch($logfile);}
function pagoda_error_handler($errno, $errstr, $filename, $line) {
	global $logfile;
	$entry = date('[Y-m-d H:i:s]').'[#'.$errno.' > '.$filename.' @ '.$line.']'.$errstr."\n";
	file_put_contents($logfile, $entry, FILE_APPEND);
}
set_error_handler('pagoda_error_handler');

/* the environment */
$fn='latest.zip';
$src='http://wordpress.org/'.$fn;
$dst=$base_dir.'/pagoda/wp-'.$fn;

/* download the latest WordPress package */
if(file_exists($dst)){unlink($dst);}
$fp = fopen($dst, 'w');
$curl = curl_init();
$opt = array(
	CURLOPT_USERAGENT => 'WordPress Installer for Pagoda Box',
	CURLOPT_URL => $src,
	CURLOPT_HEADER => false,
	CURLOPT_FILE => $fp
);
curl_setopt_array($curl, $opt);
$rsp = curl_exec($curl);
if($rsp===false){
	die("[cURL] errno:".curl_errno($curl)."\n[cURL] error:".curl_error($curl)."\n");
}
$info = curl_getinfo($curl);
curl_close($curl);
fclose($fp);

if(!file_exists($dst)){
	die('file not found: '.$dst);
}

/* cURL stats */
$time = $info['total_time']-$info['namelookup_time']-$info['connect_time']-$info['pretransfer_time']-$info['starttransfer_time']-$info['redirect_time'];
echo "[cURL] fetched '$src' @ ".abs(round(($info['size_download']*8/$time/1024/1024/1024),2))."GBps.\n";
echo "[cURL] saved file to ".$dst.".\n";

$zip = new ZipArchive;
if($zip->open($dst) === TRUE) {
	echo '[Zip] archive opened: '.$dst;
	$zip->extractTo($base_dir, 'wordpress');
	$zip->close();
}
else {
	echo '[Zip] archive could not be opened: '.$dst;
}

/* remove downloaded package */
// unlink($dst);


function format_size($size=0) {
	if($size < 1024){
		$f=$size."b";
	}
	elseif($size < 1048576){
		$f=round($size/1024,2)."kb";
	}
	else {
		$f=round($size/1048576,2)."mb";
	}
	return $f;
}
?>