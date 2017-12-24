<?php
//define moy constants
defined('MOY_TIMESTAMP') or define('MOY_TIMESTAMP', time());
defined('MOY_BOOT_TIME') or define('MOY_BOOT_TIME', microtime(true));
defined('MOY_APP_PATH') or define('MOY_APP_PATH', dirname(dirname(__FILE__)) . '/test-app/');
defined('MOY_LIB_PATH') or define('MOY_LIB_PATH', dirname(dirname(MOY_APP_PATH)) . '/lib/');
defined('MOY_PUB_PATH') or define('MOY_PUB_PATH', MOY_APP_PATH . 'public/');
defined('MOY_WEB_ROOT') or define('MOY_WEB_ROOT', '/');

//reset super variables
$_GET = array('for' => 'testing');
$_POST = array();
$_COOKIE = array();
$_REQUEST = array('for' => 'testing');
$_FILES = array();
$_SESSION = array();
$_ENV = array();
$_SERVER = array (
    'HTTP_HOST' => 'localhost',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1',
    'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
    'HTTP_ACCEPT_LANGUAGE' => 'zh-cn,zh;q=0.5',
    'HTTP_ACCEPT_ENCODING' => 'gzip',
    'HTTP_ACCEPT_CHARSET' => 'utf-8,*;q=0.7',
    'HTTP_CONNECTION' => 'keep-alive',
    'HTTP_CACHE_CONTROL' => 'max-age=0',
    'PATH' => 'C:\\Windows\\system32;C:\\Windows;C:\\Windows\\System32\\Wbem;',
    'SystemRoot' => 'C:\\Windows',
    'COMSPEC' => 'C:\\Windows\\system32\\cmd.exe',
    'PATHEXT' => '.COM;.EXE;.BAT;.CMD;.VBS;.VBE;.JS;.JSE;.WSF;.WSH;.MSC',
    'WINDIR' => 'C:\\Windows',
    'SERVER_SIGNATURE' => '',
    'SERVER_SOFTWARE' => 'Apache/2.2.17',
    'SERVER_NAME' => 'localhost',
    'SERVER_ADDR' => '127.0.0.1',
    'SERVER_PORT' => '80',
    'REMOTE_ADDR' => '127.0.0.1',
    'DOCUMENT_ROOT' => MOY_APP_PATH . 'public/',
    'SERVER_ADMIN' => 'yibn2008@gmail.com',
    'SCRIPT_FILENAME' => MOY_APP_PATH . 'public/index.php',
    'REMOTE_PORT' => '49965',
    'GATEWAY_INTERFACE' => 'CGI/1.1',
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'REQUEST_METHOD' => 'GET',
    'QUERY_STRING' => 'for=testing',
    'REQUEST_URI' => '/index.php/test-path?for=testing',
    'SCRIPT_NAME' => '/index.php',
    'PATH_INFO' => '/test-path',
    'PATH_TRANSLATED' => 'redirect:\\index.php',
    'PHP_SELF' => '/index.php/test-path',
    'PHP_AUTH_DIGEST' => 'username="admin", realm="Restricted area", nonce="4f1938a67e809", uri="/index.php/test-path?for=testing", response="45fb6e147764c004d3d1432e8c2f2e56", opaque="cdce8a5c95a1427d74df7acbf41c9ce0", qop=auth, nc=00000007, cnonce="70a72e2da461ea2d"',
    'REQUEST_TIME' => MOY_TIMESTAMP,
);