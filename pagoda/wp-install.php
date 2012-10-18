<?php
/*
	WordPress Latest Version
	cURL Installer for Pagoda Box v1.06
	Copyright 2012 by Martin Zeitler
	http://codefx.biz/contact
*/

/* the environment */
$fn='latest.zip';
$src='http://wordpress.org/'.$fn;
$base_dir = str_replace('/pagoda','', dirname(__FILE__));
$hostname=$_SERVER['APP_NAME'].'.pagodabox.com';
$version_info=dirname(__FILE__).'/wordpress/wp-includes/version.php';
$plugins=dirname(__FILE__).'/wordpress/wp-content/plugins';
$dst=$base_dir.'/pagoda/wp-'.$fn;

/* fetch the package */
wget($src, $dst);

/* extract the package */
$zip = new ZipArchive;
if($zip->open($dst) === TRUE) {
	$zip->extractTo(dirname(__FILE__));
	$zip->close();
}

/* removing some useless files */
unlink(dirname(__FILE__).'/wordpress/wp-config-sample.php');
unlink($plugins.'/hello.php');

/* [TODO] unique salts would need to be added wp-config.php */
copy(dirname(__FILE__).'/wp-config.php', dirname(__FILE__).'/wordpress/wp-config.php');

/* retrieve version number */
if(file_exists(dirname(__FILE__).'/wordpress/wp-includes/version.php')){
	require_once(dirname(__FILE__).'/wordpress/wp-includes/version.php');
	echo 'WordPress v'.$wp_version.' will now be deployed & configured.';
}

function wget($src, $dst){
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
	echo "Fetched '$src' @ ".abs(round(($info['size_download']*8/$time/1024/1024),2))."MBit/s.\n";
}

function format_size($size=0) {
	if($size < 1024){
		return $size.'b';
	}
	elseif($size < 1048576){
		return round($size/1024,2).'kb';
	}
	else {
		return round($size/1048576,2).'mb';
	}
}
?>