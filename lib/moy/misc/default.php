<?php
/**
 * Moy默认配置
 */
return array(
    //站点配置
    'site' => array(
        //应用名称(为空表示与当前MOY_APP_PATH所在目录名相同)
        'app_name' => null,

        //应用版本
        'version' => null,

        //应用主题
        'theme' => null,

        //时区
        'timezone' => 'Asia/Shanghai',

        //语言
        'language' => 'zh-cn',

        //网站字符集
        'charset' => 'utf-8',

        //404页面(controller:action形式,为空表示使用moy/misc/404.php)
        'page_404' => null,

        //403页面(controller:action形式,为空表示使用moy/misc/403.php)
        'page_403' => null,

        //闪信页面(controller:action形式,为空表示使用moy/misc/flash.php)
        'page_flash' => null,

        //默认加载的助手
        'def_helpers' => array('global'),

        //默认模式
        'def_mode' => 'develop',

        //命令行默认模式
        'cli_mode' => 'cli',

        //所有网站模式
        'modes' => array(
            //开发模式
            'develop' => array(
                'debug.enable' => true,
                'debug.error_level' => E_ALL | E_STRICT,
                'log.enable' => true,
            ),

            //部署模式
            'deploy' => array(
                'debug.enable' => false,
                'debug.error_level' => 0,
                'log.enable' => false,
            ),

            //CLI模式
            'cli_mode' => array(
                'debug.enable' => false,
                'debug.error_level' => 0,
                'log.enable' => true,
            ),
        ),
    ),

    //Moy组件重写
    'overrides' => array(
        //前端控制器
        'front' => 'Moy_Front',

        //调试器
        'debug' => 'Moy_Debug',

        //日志记录
        'logger' => 'Moy_Logger',

        //路由器
        'router' => 'Moy_Router',

        //HTTP请求
        'request' => 'Moy_Request',

        //HTTP响应
        'response' => 'Moy_Response',

        //会话
        'session' => 'Moy_Session',

        //视图
        'view' => 'Moy_View',

        //认证
        'auth' => 'Moy_Auth',

        //站点地图
        'sitemap' => 'Moy_Sitemap',

        //数据库，此对象的实例按需创建
        'db' => 'Moy_Db'
    ),

    //站点地图
    'sitemap' => array(
        '_allow' => '*'
    ),

    //调试
    'debug' => array(
        //开启调试
        'enable' => true,

        //是否以日志记录调试信息
        'log_me' => true,

        //是否将错误信息转为异常
        'err2ex' => false,

        //PHP错误显示级别
        'error_level' => E_ALL | E_STRICT,

        //跟踪回溯深度
        'trace_depth' => 10,
    ),

    //日志
    'log' => array(
        //开启日志
        'enable' => true,

        //日志过滤器,指定需要记录的日志类型,如array('WARNING', 'ERROR'),如果为空表示记录所有日志
        'filter' => null,

        //日志格式,支持变量：datetime/type/label/message/detail
        'format' => '[{datetime}] [{type}] [{label}] {message}{detail}',

        //日志文件名,支持变量：date/year/month/day/hour
        'file' => '{date}/{hour}.log',
    ),

    //路由
    'router' => array(
        //默认控制器
        'controller' => 'default',

        //默认动作
        'action' => 'index',

        //默认URL扩展名(为空表示URL后不自动添加扩展名)
        'extension' => null,

        //路由是否为重写模式
        'rewrite' => false,

        //重写模式时，URL中是否显示索引文件名(形如/index.php/controller/action)。对于Apache服务器，
        //如果当前环境不支持URL重写,必须设置此选项为true才能保存路由正常解析
        'show_index' => true,

        //生成URL时是否为完整形式(scheme://domain:port/path)
        'complete' => false,
    ),

    //会话
    'session' => array(
        //会话名称
        'name' => 'moy_session_id',

        //命名空间(如果不指定,就以应用名作为当前命令空间)
        'namespace' => null,

        //会话生存周期
        'lifetime' => 1440,

        //会话存储句柄类型(default|sqlite|custom)
        'handle_type' => 'default',

        //会话存储句柄类名(默认句柄类型不需要设置句柄类)
        'handle' => null,

        //会话保存路径(使用PHP默认的会话保存路径,若不存在就使用系统默认临时目录)
        //如果使用SQLite，则表示SQLite数据库文件存储路径
        'save_path' => null,
    ),

    //视图
    'view' => array(
        //视图目录(为空表示使用默认，即"view"目录)
        'view_dir' => null,

        //视图布局
        'layout' => 'default',

        //视图解析器
        'render' => 'Moy_View_Render',
    ),

    //认证
    'auth' => array(
        //启用自动认证(值为cookie/http)
        'auto_auth' => null,

        //COOKIE认证相关信息
        'cookie_info' => array(
            //COOKIE名称
            'name' => '_auth',

            //COOKIE应用的路径(默认是当前请求的URL路径)
            'path' => null,

            //COOKIE应用的域名(默认是当前域名)
            'domain' => null,

            //仅允许通过HTTPS安全协议访问(PHP默认值)
            'secure' => false,

            //仅允许通过HTTP方式访问(PHP默认值)
            'httponly' => false,
        ),

        //用户句柄类名
        'user_handle' => 'Moy_Auth_User',

        //默认角色(必须是auth.user_roles中的一个)
        'def_role' => 'guest',

        //所有的用户角色
        'user_roles' => array('guest'),
    ),

    //数据库
    'databases' => array(
        //默认数据源
        'default' => array(
            //数据源名称(Database Source Name)
            'dsn' => 'mysql:host=localhost;dbname=test',

            //用户名
            'username' => 'root',

            //密码
            'password' => '',

            //数据库选项(由用户根据数据库指定, PDO::__construct()的第四个参数)
            'options' => array(),
        ),
    ),
);