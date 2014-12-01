<?php
return array(
    'VIEW_PATH' => ROOT_PATH . 'themes/',
    'DEFAULT_THEME' => 'default',
    'TMPL_ENGINE_TYPE' => 'Smarty',
    'HTML_CACHE_ON' => false,
    'HTML_CACHE_RULES'=> array(
        'index:index'=>array('{$_SERVER.REQUEST_URI|md5}'),
    ),
    /*
    'TMPL_ACTION_ERROR'     => DATA_PATH.'assets/system/message.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   => DATA_PATH.'assets/system/message.tpl', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   => DATA_PATH.'assets/system/exception.tpl'
    */
);
/*
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'includes/lib_insert.php');
require(ROOT_PATH . 'includes/lib_goods.php');
require(ROOT_PATH . 'includes/lib_article.php');
?>