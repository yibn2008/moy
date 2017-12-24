<?php
/**
 * 主配置
 */
return array(
    'log' => array(
        'enable' => true,
    ),
    'router' => array(
        'extension' => 'html',
        'show_index' => true,
        'complete' => false,
        'rewrite' => true
    ),
    'auth' => array(
        'auto_auth' => 'cookie',
        'user_roles' => array('guest', 'reader', 'writer', 'admin'),
    ),
    //数据库
    'databases' => array(
        'default' => array(
            'dsn' => 'sqlite::memory:',
            'username' => '',
            'password' => '',
            'options' => array(),
        ),
        'test' => array(
            'dsn' => 'sqlite::memory:',
            'username' => '',
            'password' => '',
            'options' => array(),
        ),
    ),
    'sitemap' => array(
        '_deny' => '*',
        '_show' => true,
        '_title' => 'Moy Test',
        'default' => array(
            '_allow' => '*',
            '_title' => 'Default',
            '_show' => true,
            'index' => array(
                '_title' => 'Index',
                '_protocol' => 'p=0.8;c=always;l=2012-01-01',
            ),
            'error404' => array(
                '_title' => 'Error 404',
            ),
            'error403' => array(
                '_title' => 'Error 403',
            ),
            'flash' => array(
                '_title' => 'Flash Message',
            ),
        ),
        'blog' => array(
            '_title' => 'Blog',
            '_show' => true,
            'view' => array(
                '_allow' => '*',
                '_route' => 'title<\w+>:about-us',
                '_protocol' => 'p=0.8;c=always',
            ),
            'comment' => array(
                '_allow' => array('reader', 'writer', 'admin'),
                '_protocol' => 'p=0.6;c=always;lastmod=0',
            ),
            'archive' => array(
                '_show' => false,
                '_route' => 'year<\d+>/month<\d+>:1/day:1',
            ),
        ),
        'not' => array(
            '_title' => 'Not',
            'exists' => array(
                '_title' => 'Exists',
            ),
        ),
        'admin' => array(
            '_allow' => array('admin'),
            '_title' => 'Administration',
            '_show' => false,
            'user' => array(
                'index' => array(
                    '_title' => 'Index',
                ),
                'edit' => array(
                    '_title' => 'Edit',
                ),
            ),
        ),
        'test' => array(
            '_allow' => '*',
            'index' => array(
                '_deny' => array('guest'),
            ),
        ),
    )
);