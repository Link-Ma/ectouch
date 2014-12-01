<?php

class Controller extends Action
{

    public function __construct()
    {
        parent::__construct();
    }
    
    protected function _initialize(){
        // 安装检测
        if (! file_exists(ROOT_PATH . 'data/install.lock') && ! defined('NO_CHECK_INSTALL')) {
            redirect(U('install/index'));
        }
        header("Content-type: text/html; charset=".EC_CHARSET);
        /* 载入系统参数 */
        C('setting', load_config());
        
    }
}