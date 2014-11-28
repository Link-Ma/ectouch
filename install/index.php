<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：index.php
 * ----------------------------------------------------------------------------
 * 功能描述：安装入口文件
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
define('IN_ECTOUCH', true);
/* 绑定模块 */
define('BIND_MODULE', 'Install');
/* 安装检测 */
define('NO_CHECK_INSTALL', true);
/* 载入配置文件 */
require ('../include/define.php');
/* 载入核心文件 */
require (TOUCH_PATH . "system/init.php");
