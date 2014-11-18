<?php

/**
 * ECSCMS (c) 2012-2014 http://ecscms.com All rights reserved.
 * ============================================================================
 * This is not a freeware, use is subject to license terms
 * ----------------------------------------------------------------------------
 * 文件名称：bootstrap.php
 * ----------------------------------------------------------------------------
 * 功能描述：项目公共入口文件
 * ----------------------------------------------------------------------------
 * Author: Ted <docxcn@gmail.com>
 * ----------------------------------------------------------------------------
 */

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) die('require PHP > 5.3.0 !');
// 开启调试模式
define('APP_DEBUG', true);
// 网站根目录
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../') . '/');
// 网站核心目录
define('BASE_PATH', ROOT_PATH . 'source/');
// 应用目录
define('APP_PATH', BASE_PATH . 'application/');
// 应用公共目录
define('COMMON_PATH', BASE_PATH . 'common/');
// 运行时目录
define('RUNTIME_PATH', ROOT_PATH . 'data/temp/');
// 根目录请求
$root_directory = (BIND_MODULE == 'Index') ? true : false;
// 网站URL路径
if ($root_directory) {
    define('__ROOT__', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
} else {
    define('__ROOT__', rtrim(dirname($_SERVER['SCRIPT_NAME']), strtolower(BIND_MODULE) . '/'));
}
// 安装检测
if (!file_exists(ROOT_PATH . 'data/install.lock') && BIND_MODULE !== 'Install') {
    $redirectUrl = $root_directory ? './install' : '../install';
    header("Location: " . $redirectUrl);
    exit();
}
// 引入框架入口文件
require BASE_PATH . 'framework/core.php';
