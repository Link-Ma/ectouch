<?php

class Controller extends Action
{

    public function __construct()
    {
        parent::__construct();
        
        // 安装检测
        if (! file_exists(ROOT_PATH . 'data/install.lock') && ! defined('NO_CHECK_INSTALL')) {
            redirect(U('install/index'));
        }
    }
}