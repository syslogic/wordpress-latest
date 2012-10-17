<?php
/*
	WordPress Installer for Pagoda Box v1.05
	Copyright 2012 by Martin Zeitler
	http://codefx.biz/contact
*/

/* the environment */
$fn='latest.zip';
$base_dir = str_replace('/pagoda','', dirname(__FILE__));
$src='http://wordpress.org/'.$fn;
$dst=$base_dir.'/pagoda/wp-'.$fn;

/* fetch the package */
if(file_exists($dst)){unlink($dst);}
$fp = fopen($dst, 'w');
$curl = curl_init();
$opt = array(CURLOPT_URL => $src, CURLOPT_HEADER => false, CURLOPT_FILE => $fp);
curl_setopt_array($curl, $opt);
$rsp = curl_exec($curl);
if($rsp===false){
	die("[cURL] errno:".curl_errno($curl)."\n[cURL] error:".curl_error($curl)."\n");
}
$info = curl_getinfo($curl);
curl_close($curl);
fclose($fp);

/* cURL stats */
$time = $info['total_time']-$info['namelookup_time']-$info['connect_time']-$info['pretransfer_time']-$info['starttransfer_time']-$info['redirect_time'];
echo "[cURL] fetched '$src' @ ".abs(round(($info['size_download']*8/$time/1024/1024/1024),2))."GBps.\n";

$zip = new ZipArchive;
if($zip->open($dst) === TRUE) {
	if($zip->extractTo(dirname(__FILE__))){
		echo '[ZiP] '.$fn.' extracted to: '.dirname(__FILE__).'/wordpress';
	}
	$zip->close();
}
if(file_exists(dirname(__FILE__).'/wordpress/wp-includes/version.php')){
	require_once(dirname(__FILE__).'/wordpress/wp-includes/version.php');
	echo '[WP] WordPress '.$wp_version.' will be deployed.';
}

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