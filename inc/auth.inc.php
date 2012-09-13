<?php

// Check IP
$valid_ips = array('192.168.333.777');
if (! in_array($_SERVER['REMOTE_ADDR'], $valid_ips)) {
	die("Access Denied ({$_SERVER['REMOTE_ADDR']})");
}

// Fucking fastcgi...
// @list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr(@$_SERVER['HTTP_AUTHORIZATION'], 6)));

// Basic auth
$u = @$_SERVER['PHP_AUTH_USER'];
$p = @$_SERVER['PHP_AUTH_PW'];

if    ((md5($u) != 'hash string') || (md5($p) != 'nother hash string')) {
	header('WWW-Authenticate: Basic realm="LiteAdmin"');
	header('HTTP/1.1 401 Unauthorized');
	die("Access Denied (u/p)");
}
