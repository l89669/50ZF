<?php

$APPConfig = array(
    'WebSiteName' => "武林支付",
    'url_bbs' => 'http://bbs.ddmcloud.com/forum.php?gid=36',
    'url_bbs_discuzz' => 'http://bbs.ddmcloud.com/forum.php?mod=forumdisplay&fid=38',
    'url_register' => 'http://bbs.ddmcloud.com/member.php?mod=register',
    'url_changepassword' => 'http://bbs.ddmcloud.com/home.php?mod=spacecp&ac=profile&op=password',
    'url' => 'http://mc.50zf.com/',
    'pay_ddm_partner' => '1',
    'pay_ddm_key' => 'A9D9F491VBR11B44',
    'api_status' => true,
);
$SystemConfig = array(
    //数据库
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_PORT' => 3306, // 服务器端口
    'DB_NAME' => 'mcpay', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'root', // 密码
    'DB_PREFIX' => 'ddm_', // 数据库表前缀
    'DB_CHARSET' => 'utf8', // 数据库字符集
    //多语言
    'LANG_SWITCH_ON' => true, // 开启语言包功能
    'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST' => 'zh-cn', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE' => 'l', // 默认语言切换变量
    //模板主题
    'DEFAULT_THEME' => 'default', //默认主题
    //URL
    'URL_CASE_INSENSITIVE' => false, //是否大小写严格匹配
    'URL_MODEL' => 1,
    'TOKEN_ON' => true, // 是否开启令牌验证 默认关闭
    'TOKEN_NAME' => '__hash__', // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE' => 'md5', //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET' => false, //令牌验证出错后是否重置令牌 默认为true
    'URL_HTML_SUFFIX' => 'html|shtml|xml|php|asp|jsp|aspx|action', //限制伪静态的后缀
    // 开启路由
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(//url优化
        'spay/:id\d' => 'Home/serverpay/index',
    ),
    'HTML_CACHE_ON' => true, // 开启静态缓存
    'HTML_CACHE_TIME' => 10, // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX' => '.shtml', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES' => array(// 定义静态缓存规则
        //'user:main' => array('user/{uc_username|session}_{:action}', 15),
        //'user:record' => array('user/{$_SESSION.uc_username}/{:action}_{p}', 3),
        'user:recordinfo' => array('user/{:action}_{order}', 30),
        'server:info' => array('server/{:action}_{id}', 3),
        'server:setrate' => array('server/{:action}_{id}', 3),
        'serverpay:index' => array('serverpay/{:action}_{id}', 10),
        'doserverpay:query' => array('serverpay/{:action}_{order}', 180),
    ),
    //Cookies
    'COOKIE_EXPIRE' => 2678400, // Cookie有效期
    'COOKIE_DOMAIN' => '.50zf.com', // Cookie有效域名
    'COOKIE_PATH' => '/', // Cookie路径
    'COOKIE_PREFIX' => '', // Cookie前缀 避免冲突
    'COOKIE_HTTPONLY' => 'true', // Cookie的httponly属性 3.2.2新增
    //Session
    'SESSION_AUTO_START' => true, // 是否自动开启Session
);
$AllConfig = array_merge($APPConfig, $SystemConfig);
///////
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', $AllConfig['COOKIE_DOMAIN']);

return $AllConfig;
