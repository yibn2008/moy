<?php
/**
 * 站点配置
 */
return array(
    'app_name' => 'test-app',
    'time_zone' => 'Asia/Chongqing',
    'language' => 'zh-cn',
    'charset' => 'UTF-8',
    'page_404' => null,
    'page_403' => null,
    'page_flash' => 'default:flash',
    'def_mode' => 'testing',
    'modes' => array(
        'develop' => array(
            'debug.enable' => true,
            'debug.error_level' => E_ALL | E_STRICT,
            'log.enable' => true,
        ),
        'deploy' => array(
            'debug.enable' => false,
            'debug.firephp' => false,
            'debug.error_level' => 0,
            'log.enable' => false,
        ),
        'testing' => array(
            'debug.enable' => true,
            'debug.err2ex' => false,
            'log.enable' => true,
            'debug.error_level' => E_ALL | E_STRICT,
        ),
    ),
);