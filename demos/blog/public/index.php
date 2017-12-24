<?php
define('MOY_LIB_PATH', dirname(dirname(__DIR__)) . '/lib/');

require MOY_LIB_PATH . 'moy/core/bootstrap.php';

$bootstrap = new Moy_Bootstrap('develop');
$bootstrap->boot();
