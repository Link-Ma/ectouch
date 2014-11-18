<?php

/**
 * 密码编译方法;
 *
 * @access  public
 * @param   string      $pass       需要编译的原始密码
 *
 * @return  string
 */
function compile_password($pass)
{
    return md5($pass);
}

/**
 * 取得当前的域名
 *
 * @access public
 *        
 * @return string 当前的域名
 */
function get_domain()
{
    /* 协议 */
    $protocol = http();
    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    } else {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = ':' . $_SERVER['SERVER_PORT'];
            
            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                $port = '';
            }
        } else {
            $port = '';
        }
        
        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'] . $port;
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }
    
    return $protocol . $host;
}

/**
 * 获得当前环境的 URL 地址
 *
 * @access public
 *        
 * @return void
 */
function url()
{
    $curr = strpos(PHP_SELF, ADMIN_PATH . '/') !== false ? preg_replace('/(.*)(' . ADMIN_PATH . ')(\/?)(.)*/i', '\1', dirname(PHP_SELF)) : dirname(PHP_SELF);
    $root = str_replace('\\', '/', $curr);
    if (substr($root, - 1) != '/') {
        $root .= '/';
    }
    return get_domain() . $root;
}

/**
 * 获得当前环境的 HTTP 协议方式
 *
 * @access public
 *        
 * @return void
 */
function http()
{
    return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}