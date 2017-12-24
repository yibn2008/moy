<?php
define('MOY_APP_PATH', dirname(dirname(__FILE__)) . '/');
define('MOY_LIB_PATH', dirname(dirname(MOY_APP_PATH)) . '/lib/');
define('MOY_PUB_PATH', MOY_APP_PATH . 'public/');

require MOY_LIB_PATH . 'Moy/Core/Bootstrap.php';

$bootstrap = new Moy_Bootstrap('testing');
$bootstrap->boot();