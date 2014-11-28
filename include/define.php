<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：define.php
 * ----------------------------------------------------------------------------
 * 功能描述：常量配置文件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');
/* 默认模块 */
defined('BIND_MODULE') or define('BIND_MODULE', 'Index');
/* 项目名称 */
define('APP_NAME', 'ECTouch');
/* 调试模式 */
define('APP_DEBUG', true);
/* 项目根目录 */
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../') . '/');
/* 核心目录 */
define('TOUCH_PATH', ROOT_PATH . 'include/');
/* 公共类库目录 */
define('BASE_PATH', TOUCH_PATH . 'common/');
/* 数据目录 */
define('DATA_PATH', ROOT_PATH . 'data/');
/* 插件目录 */
define('ADDONS_PATH', ROOT_PATH . 'plugins/');
/* 项目目录 */
define('APP_PATH', TOUCH_PATH . 'modules/' . BIND_MODULE . '/');
/* 项目类库目录 */
define('LIB_PATH', APP_PATH);
/* 模板目录 */
define('TMPL_PATH', APP_PATH . 'View/');
/* 运行时目录 */
define('RUNTIME_PATH', DATA_PATH . 'caching/' . BIND_MODULE . '/');
/* 日志目录 */
define('LOG_PATH', DATA_PATH . 'logs/' . BIND_MODULE . '/');
/* 静态缓存目录 */
define('HTML_PATH', RUNTIME_PATH . 'Html/');
