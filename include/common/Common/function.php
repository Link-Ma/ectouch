<?php

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

/**
 * 将指定的表名加上前缀后返回
 *
 * @access public
 * @param string $str 表名
 *
 * @return string
 */
function table($str)
{
    return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
}

/**
 * ECSHOP 密码编译方法;
 *
 * @access public
 * @param string $pass 需要编译的原始密码
 *
 * @return string
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
    $protocol = $this->http();
    
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
 * 获得 ECSHOP 当前环境的 URL 地址
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
    
    return $this->get_domain() . $root;
}

/**
 * 获得 ECSHOP 当前环境的 HTTP 协议方式
 *
 * @access public
 *
 * @return void
 */
function http()
{
    return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

/**
 * 获得数据目录的路径
 *
 * @param int $sid
 *
 * @return string 路径
 */
function data_dir($sid = 0)
{
    if (empty($sid)) {
        $s = 'data';
    } else {
        $s = 'user_files/';
        $s .= ceil($sid / 3000) . '/';
        $s .= $sid % 3000;
    }
    return $s;
}

/**
 * 获得图片的目录路径
 *
 * @param int $sid
 *
 * @return string 路径
 */
function image_dir($sid = 0)
{
    if (empty($sid)) {
        $s = 'images';
    } else {
        $s = 'user_files/';
        $s .= ceil($sid / 3000) . '/';
        $s .= ($sid % 3000) . '/';
        $s .= 'images';
    }
    return $s;
}

/**
 * *****************************************
 * 时间函数
 * *****************************************
 */

/**
 * 获得当前格林威治时间的时间戳
 *
 * @return integer
 */
function gmtime()
{
    return (time() - date('Z'));
}

/**
 * 获得服务器的时区
 *
 * @return integer
 */
function server_timezone()
{
    if (function_exists('date_default_timezone_get')) {
        return date_default_timezone_get();
    } else {
        return date('Z') / 3600;
    }
}

/**
 * 生成一个用户自定义时区日期的GMT时间戳
 *
 * @access public
 * @param int $hour
 * @param int $minute
 * @param int $second
 * @param int $month
 * @param int $day
 * @param int $year
 *
 * @return void
 */
function local_mktime($hour = NULL, $minute = NULL, $second = NULL, $month = NULL, $day = NULL, $year = NULL)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
    
    /**
     * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
     * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
     */
    $time = mktime($hour, $minute, $second, $month, $day, $year) - $timezone * 3600;
    
    return $time;
}

/**
 * 将GMT时间戳格式化为用户自定义时区日期
 *
 * @param string $format
 * @param integer $time 该参数必须是一个GMT的时间戳
 *
 * @return string
 */
function local_date($format, $time = NULL)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
    
    if ($time === NULL) {
        $time = gmtime();
    } elseif ($time <= 0) {
        return '';
    }
    
    $time += ($timezone * 3600);
    
    return date($format, $time);
}

/**
 * 转换字符串形式的时间表达式为GMT时间戳
 *
 * @param string $str
 *
 * @return integer
 */
function gmstr2time($str)
{
    $time = strtotime($str);
    
    if ($time > 0) {
        $time -= date('Z');
    }
    
    return $time;
}

/**
 * 将一个用户自定义时区的日期转为GMT时间戳
 *
 * @access public
 * @param string $str
 *
 * @return integer
 */
function local_strtotime($str)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
    
    /**
     * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
     * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
     */
    $time = strtotime($str) - $timezone * 3600;
    
    return $time;
}

/**
 * 获得用户所在时区指定的时间戳
 *
 * @param $timestamp integer 该时间戳必须是一个服务器本地的时间戳
 *
 * @return array
 */
function local_gettime($timestamp = NULL)
{
    $tmp = local_getdate($timestamp);
    return $tmp[0];
}

/**
 * 获得用户所在时区指定的日期和时间信息
 *
 * @param $timestamp integer 该时间戳必须是一个服务器本地的时间戳
 *
 * @return array
 */
function local_getdate($timestamp = NULL)
{
    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
    
    /* 如果时间戳为空，则获得服务器的当前时间 */
    if ($timestamp === NULL) {
        $timestamp = time();
    }
    
    $gmt = $timestamp - date('Z'); // 得到该时间的格林威治时间
    $local_time = $gmt + ($timezone * 3600); // 转换为用户所在时区的时间戳
    
    return getdate($local_time);
}

/**
 * *****************************************
 * 基础函数
 * *****************************************
 */

/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param string $str 被截取的字符串
 * @param int $length 截取的长度
 * @param bool $append 是否附加省略号
 *
 * @return string
 */
function sub_str($str, $length = 0, $append = true)
{
    $str = trim($str);
    $strlength = strlen($str);
    
    if ($length == 0 || $length >= $strlength) {
        return $str;
    } elseif ($length < 0) {
        $length = $strlength + $length;
        if ($length < 0) {
            $length = $strlength;
        }
    }
    
    if (function_exists('mb_substr')) {
        $newstr = mb_substr($str, 0, $length, EC_CHARSET);
    } elseif (function_exists('iconv_substr')) {
        $newstr = iconv_substr($str, 0, $length, EC_CHARSET);
    } else {
        // $newstr = trim_right(substr($str, 0, $length));
        $newstr = substr($str, 0, $length);
    }
    
    if ($append && $str != $newstr) {
        $newstr .= '...';
    }
    
    return $newstr;
}

/**
 * 获得用户的真实IP地址
 *
 * @access public
 * @return string
 */
function real_ip()
{
    static $realip = NULL;
    
    if ($realip !== NULL) {
        return $realip;
    }
    
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip) {
                $ip = trim($ip);
                
                if ($ip != 'unknown') {
                    $realip = $ip;
                    
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    
    return $realip;
}

/**
 * 计算字符串的长度（汉字按照两个字符计算）
 *
 * @param string $str 字符串
 *
 * @return int
 */
function str_len($str)
{
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
    
    if ($length) {
        return strlen($str) - $length + intval($length / 3) * 2;
    } else {
        return strlen($str);
    }
}

/**
 * 获得用户操作系统的换行符
 *
 * @access public
 * @return string
 */
function get_crlf()
{
    /* LF (Line Feed, 0x0A, \N) 和 CR(Carriage Return, 0x0D, \R) */
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'Win')) {
        $the_crlf = '\r\n';
    } elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'Mac')) {
        $the_crlf = '\r'; // for old MAC OS
    } else {
        $the_crlf = '\n';
    }
    
    return $the_crlf;
}

/**
 * 邮件发送
 *
 * @param : $name[string] 接收人姓名
 * @param : $email[string] 接收人邮件地址
 * @param : $subject[string] 邮件标题
 * @param : $content[string] 邮件内容
 * @param : $type[int] 0 普通邮件， 1 HTML邮件
 * @param : $notification[bool] true 要求回执， false 不用回执
 *
 * @return boolean
 */
function send_mail($name, $email, $subject, $content, $type = 0, $notification = false)
{
    /* 如果邮件编码不是EC_CHARSET，创建字符集转换对象，转换编码 */
    if ($GLOBALS['_CFG']['mail_charset'] != EC_CHARSET) {
        $name = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $name);
        $subject = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $subject);
        $content = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $content);
        $shop_name = ecs_iconv(EC_CHARSET, $GLOBALS['_CFG']['mail_charset'], $GLOBALS['_CFG']['shop_name']);
    }
    $charset = $GLOBALS['_CFG']['mail_charset'];
    /**
     * 使用mail函数发送邮件
     */
    if ($GLOBALS['_CFG']['mail_service'] == 0 && function_exists('mail')) {
        /* 邮件的头部信息 */
        $content_type = ($type == 0) ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $headers = array();
        $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
        $headers[] = $content_type . '; format=flowed';
        if ($notification) {
            $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
        }
        
        $res = @mail($email, '=?' . $charset . '?B?' . base64_encode($subject) . '?=', $content, implode("\r\n", $headers));
        
        if (! $res) {
            $GLOBALS['err']->add($GLOBALS['_LANG']['sendemail_false']);
            
            return false;
        } else {
            return true;
        }
    } /**
     * 使用smtp服务发送邮件
     */
    else {
        /* 邮件的头部信息 */
        $content_type = ($type == 0) ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $content = base64_encode($content);
        
        $headers = array();
        $headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
        $headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($name) . '?=' . '" <' . $email . '>';
        $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
        $headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
        $headers[] = $content_type . '; format=flowed';
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'Content-Disposition: inline';
        if ($notification) {
            $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $GLOBALS['_CFG']['smtp_mail'] . '>';
        }
        
        /* 获得邮件服务器的参数设置 */
        $params['host'] = $GLOBALS['_CFG']['smtp_host'];
        $params['port'] = $GLOBALS['_CFG']['smtp_port'];
        $params['user'] = $GLOBALS['_CFG']['smtp_user'];
        $params['pass'] = $GLOBALS['_CFG']['smtp_pass'];
        
        if (empty($params['host']) || empty($params['port'])) {
            // 如果没有设置主机和端口直接返回 false
            $GLOBALS['err']->add($GLOBALS['_LANG']['smtp_setting_error']);
            
            return false;
        } else {
            // 发送邮件
            if (! function_exists('fsockopen')) {
                // 如果fsockopen被禁用，直接返回
                $GLOBALS['err']->add($GLOBALS['_LANG']['disabled_fsockopen']);
                
                return false;
            }
            
            include_once (ROOT_PATH . 'includes/cls_smtp.php');
            static $smtp;
            
            $send_params['recipients'] = $email;
            $send_params['headers'] = $headers;
            $send_params['from'] = $GLOBALS['_CFG']['smtp_mail'];
            $send_params['body'] = $content;
            
            if (! isset($smtp)) {
                $smtp = new smtp($params);
            }
            
            if ($smtp->connect() && $smtp->send($send_params)) {
                return true;
            } else {
                $err_msg = $smtp->error_msg();
                if (empty($err_msg)) {
                    $GLOBALS['err']->add('Unknown Error');
                } else {
                    if (strpos($err_msg, 'Failed to connect to server') !== false) {
                        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['smtp_connect_failure'], $params['host'] . ':' . $params['port']));
                    } else 
                        if (strpos($err_msg, 'AUTH command failed') !== false) {
                            $GLOBALS['err']->add($GLOBALS['_LANG']['smtp_login_failure']);
                        } elseif (strpos($err_msg, 'bad sequence of commands') !== false) {
                            $GLOBALS['err']->add($GLOBALS['_LANG']['smtp_refuse']);
                        } else {
                            $GLOBALS['err']->add($err_msg);
                        }
                }
                
                return false;
            }
        }
    }
}

/**
 * 获得服务器上的 GD 版本
 *
 * @access public
 * @return int 可能的值为0，1，2
 */
function gd_version()
{
    include_once (ROOT_PATH . 'includes/cls_image.php');
    
    return cls_image::gd_version();
}

/**
 * 文件或目录权限检查函数
 *
 * @access public
 * @param string $file_path 文件路径
 * @param bool $rename_prv 是否在检查修改权限时检查执行rename()函数的权限
 *
 * @return int 返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
 * 返回值在二进制计数法中，四位由高到低分别代表
 * 可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
 */
function file_mode_info($file_path)
{
    /* 如果不存在，则不可读、不可写、不可改 */
    if (! file_exists($file_path)) {
        return false;
    }
    
    $mark = 0;
    
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        /* 测试文件 */
        $test_file = $file_path . '/cf_test.txt';
        
        /* 如果是目录 */
        if (is_dir($file_path)) {
            /* 检查目录是否可读 */
            $dir = @opendir($file_path);
            if ($dir === false) {
                return $mark; // 如果目录打开失败，直接返回目录不可修改、不可写、不可读
            }
            if (@readdir($dir) !== false) {
                $mark ^= 1; // 目录可读 001，目录不可读 000
            }
            @closedir($dir);
            
            /* 检查目录是否可写 */
            $fp = @fopen($test_file, 'wb');
            if ($fp === false) {
                return $mark; // 如果目录中的文件创建失败，返回不可写。
            }
            if (@fwrite($fp, 'directory access testing.') !== false) {
                $mark ^= 2; // 目录可写可读011，目录可写不可读 010
            }
            @fclose($fp);
            
            @unlink($test_file);
            
            /* 检查目录是否可修改 */
            $fp = @fopen($test_file, 'ab+');
            if ($fp === false) {
                return $mark;
            }
            if (@fwrite($fp, "modify test.\r\n") !== false) {
                $mark ^= 4;
            }
            @fclose($fp);
            
            /* 检查目录下是否有执行rename()函数的权限 */
            if (@rename($test_file, $test_file) !== false) {
                $mark ^= 8;
            }
            @unlink($test_file);
        }        /* 如果是文件 */
        elseif (is_file($file_path)) {
            /* 以读方式打开 */
            $fp = @fopen($file_path, 'rb');
            if ($fp) {
                $mark ^= 1; // 可读 001
            }
            @fclose($fp);
            
            /* 试着修改文件 */
            $fp = @fopen($file_path, 'ab+');
            if ($fp && @fwrite($fp, '') !== false) {
                $mark ^= 6; // 可修改可写可读 111，不可修改可写可读011...
            }
            @fclose($fp);
            
            /* 检查目录下是否有执行rename()函数的权限 */
            if (@rename($test_file, $test_file) !== false) {
                $mark ^= 8;
            }
        }
    } else {
        if (@is_readable($file_path)) {
            $mark ^= 1;
        }
        
        if (@is_writable($file_path)) {
            $mark ^= 14;
        }
    }
    
    return $mark;
}

function log_write($arg, $file = '', $line = '')
{
    if ((DEBUG_MODE & 4) != 4) {
        return;
    }
    
    $str = "\r\n-- " . date('Y-m-d H:i:s') . " --------------------------------------------------------------\r\n";
    $str .= "FILE: $file\r\nLINE: $line\r\n";
    
    if (is_array($arg)) {
        $str .= '$arg = array(';
        foreach ($arg as $val) {
            foreach ($val as $key => $list) {
                $str .= "'$key' => '$list'\r\n";
            }
        }
        $str .= ")\r\n";
    } else {
        $str .= $arg;
    }
    
    file_put_contents(ROOT_PATH . DATA_DIR . '/log.txt', $str);
}

/**
 * 检查目标文件夹是否存在，如果不存在则自动创建该目录
 *
 * @access public
 * @param string folder 目录路径。不能使用相对于网站根目录的URL
 *
 * @return bool
 */
function make_dir($folder)
{
    $reval = false;
    
    if (! file_exists($folder)) {
        /* 如果目录不存在则尝试创建该目录 */
        @umask(0);
        
        /* 将目录路径拆分成数组 */
        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
        
        /* 如果第一个字符为/则当作物理路径处理 */
        $base = ($atmp[0][0] == '/') ? '/' : '';
        
        /* 遍历包含路径信息的数组 */
        foreach ($atmp[1] as $val) {
            if ('' != $val) {
                $base .= $val;
                
                if ('..' == $val || '.' == $val) {
                    /* 如果目录为.或者..则直接补/继续下一个循环 */
                    $base .= '/';
                    
                    continue;
                }
            } else {
                continue;
            }
            
            $base .= '/';
            
            if (! file_exists($base)) {
                /* 尝试创建目录，如果创建失败则继续循环 */
                if (@mkdir(rtrim($base, '/'), 0777)) {
                    @chmod($base, 0777);
                    $reval = true;
                }
            }
        }
    } else {
        /* 路径已经存在。返回该路径是不是一个目录 */
        $reval = is_dir($folder);
    }
    
    clearstatcache();
    
    return $reval;
}

/**
 * 获得系统是否启用了 gzip
 *
 * @access public
 *
 * @return boolean
 */
function gzip_enabled()
{
    static $enabled_gzip = NULL;
    
    if ($enabled_gzip === NULL) {
        $enabled_gzip = ($GLOBALS['_CFG']['enable_gzip'] && function_exists('ob_gzhandler'));
    }
    
    return $enabled_gzip;
}

/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access public
 * @param mix $value
 *
 * @return mix
 */
function addslashes_deep($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

/**
 * 将对象成员变量或者数组的特殊字符进行转义
 *
 * @access public
 * @param mix $obj 对象或者数组
 * @author Xuan Yan
 *
 * @return mix 对象或者数组
 */
function addslashes_deep_obj($obj)
{
    if (is_object($obj) == true) {
        foreach ($obj as $key => $val) {
            $obj->$key = addslashes_deep($val);
        }
    } else {
        $obj = addslashes_deep($obj);
    }
    
    return $obj;
}

/**
 * 递归方式的对变量中的特殊字符去除转义
 *
 * @access public
 * @param mix $value
 *
 * @return mix
 */
function stripslashes_deep($value)
{
    if (empty($value)) {
        return $value;
    } else {
        return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    }
}

/**
 * 将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access public
 * @param string $str 待转换字串
 *
 * @return string $str 处理后字串
 */
function make_semiangle($str)
{
    $arr = array(
        '０' => '0',
        '１' => '1',
        '２' => '2',
        '３' => '3',
        '４' => '4',
        '５' => '5',
        '６' => '6',
        '７' => '7',
        '８' => '8',
        '９' => '9',
        'Ａ' => 'A',
        'Ｂ' => 'B',
        'Ｃ' => 'C',
        'Ｄ' => 'D',
        'Ｅ' => 'E',
        'Ｆ' => 'F',
        'Ｇ' => 'G',
        'Ｈ' => 'H',
        'Ｉ' => 'I',
        'Ｊ' => 'J',
        'Ｋ' => 'K',
        'Ｌ' => 'L',
        'Ｍ' => 'M',
        'Ｎ' => 'N',
        'Ｏ' => 'O',
        'Ｐ' => 'P',
        'Ｑ' => 'Q',
        'Ｒ' => 'R',
        'Ｓ' => 'S',
        'Ｔ' => 'T',
        'Ｕ' => 'U',
        'Ｖ' => 'V',
        'Ｗ' => 'W',
        'Ｘ' => 'X',
        'Ｙ' => 'Y',
        'Ｚ' => 'Z',
        'ａ' => 'a',
        'ｂ' => 'b',
        'ｃ' => 'c',
        'ｄ' => 'd',
        'ｅ' => 'e',
        'ｆ' => 'f',
        'ｇ' => 'g',
        'ｈ' => 'h',
        'ｉ' => 'i',
        'ｊ' => 'j',
        'ｋ' => 'k',
        'ｌ' => 'l',
        'ｍ' => 'm',
        'ｎ' => 'n',
        'ｏ' => 'o',
        'ｐ' => 'p',
        'ｑ' => 'q',
        'ｒ' => 'r',
        'ｓ' => 's',
        'ｔ' => 't',
        'ｕ' => 'u',
        'ｖ' => 'v',
        'ｗ' => 'w',
        'ｘ' => 'x',
        'ｙ' => 'y',
        'ｚ' => 'z',
        '（' => '(',
        '）' => ')',
        '〔' => '[',
        '〕' => ']',
        '【' => '[',
        '】' => ']',
        '〖' => '[',
        '〗' => ']',
        '“' => '[',
        '”' => ']',
        '‘' => '[',
        '’' => ']',
        '｛' => '{',
        '｝' => '}',
        '《' => '<',
        '》' => '>',
        '％' => '%',
        '＋' => '+',
        '—' => '-',
        '－' => '-',
        '～' => '-',
        '：' => ':',
        '。' => '.',
        '、' => ',',
        '，' => '.',
        '、' => '.',
        '；' => ',',
        '？' => '?',
        '！' => '!',
        '…' => '-',
        '‖' => '|',
        '”' => '"',
        '’' => '`',
        '‘' => '`',
        '｜' => '|',
        '〃' => '"',
        '　' => ' '
    );
    
    return strtr($str, $arr);
}

/**
 * 过滤用户输入的基本数据，防止script攻击
 *
 * @access public
 * @return string
 */
function compile_str($str)
{
    $arr = array(
        '<' => '＜',
        '>' => '＞',
        '"' => '”',
        "'" => '’'
    );
    
    return strtr($str, $arr);
}

/**
 * 检查文件类型
 *
 * @access public
 * @param string filename 文件名
 * @param string realname 真实文件名
 * @param string limit_ext_types 允许的文件类型
 * @return string
 */
function check_file_type($filename, $realname = '', $limit_ext_types = '')
{
    if ($realname) {
        $extname = strtolower(substr($realname, strrpos($realname, '.') + 1));
    } else {
        $extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
    }
    
    if ($limit_ext_types && stristr($limit_ext_types, '|' . $extname . '|') === false) {
        return '';
    }
    
    $str = $format = '';
    
    $file = @fopen($filename, 'rb');
    if ($file) {
        $str = @fread($file, 0x400); // 读取前 1024 个字节
        @fclose($file);
    } else {
        if (stristr($filename, ROOT_PATH) === false) {
            if ($extname == 'jpg' || $extname == 'jpeg' || $extname == 'gif' || $extname == 'png' || $extname == 'doc' || $extname == 'xls' || $extname == 'txt' || $extname == 'zip' || $extname == 'rar' || $extname == 'ppt' || $extname == 'pdf' || $extname == 'rm' || $extname == 'mid' || $extname == 'wav' || $extname == 'bmp' || $extname == 'swf' || $extname == 'chm' || $extname == 'sql' || $extname == 'cert' || $extname == 'pptx' || $extname == 'xlsx' || $extname == 'docx') {
                $format = $extname;
            }
        } else {
            return '';
        }
    }
    
    if ($format == '' && strlen($str) >= 2) {
        if (substr($str, 0, 4) == 'MThd' && $extname != 'txt') {
            $format = 'mid';
        } elseif (substr($str, 0, 4) == 'RIFF' && $extname == 'wav') {
            $format = 'wav';
        } elseif (substr($str, 0, 3) == "\xFF\xD8\xFF") {
            $format = 'jpg';
        } elseif (substr($str, 0, 4) == 'GIF8' && $extname != 'txt') {
            $format = 'gif';
        } elseif (substr($str, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
            $format = 'png';
        } elseif (substr($str, 0, 2) == 'BM' && $extname != 'txt') {
            $format = 'bmp';
        } elseif ((substr($str, 0, 3) == 'CWS' || substr($str, 0, 3) == 'FWS') && $extname != 'txt') {
            $format = 'swf';
        } elseif (substr($str, 0, 4) == "\xD0\xCF\x11\xE0") { // D0CF11E == DOCFILE == Microsoft Office Document
            if (substr($str, 0x200, 4) == "\xEC\xA5\xC1\x00" || $extname == 'doc') {
                $format = 'doc';
            } elseif (substr($str, 0x200, 2) == "\x09\x08" || $extname == 'xls') {
                $format = 'xls';
            } elseif (substr($str, 0x200, 4) == "\xFD\xFF\xFF\xFF" || $extname == 'ppt') {
                $format = 'ppt';
            }
        } elseif (substr($str, 0, 4) == "PK\x03\x04") {
            if (substr($str, 0x200, 4) == "\xEC\xA5\xC1\x00" || $extname == 'docx') {
                $format = 'docx';
            } elseif (substr($str, 0x200, 2) == "\x09\x08" || $extname == 'xlsx') {
                $format = 'xlsx';
            } elseif (substr($str, 0x200, 4) == "\xFD\xFF\xFF\xFF" || $extname == 'pptx') {
                $format = 'pptx';
            } else {
                $format = 'zip';
            }
        } elseif (substr($str, 0, 4) == 'Rar!' && $extname != 'txt') {
            $format = 'rar';
        } elseif (substr($str, 0, 4) == "\x25PDF") {
            $format = 'pdf';
        } elseif (substr($str, 0, 3) == "\x30\x82\x0A") {
            $format = 'cert';
        } elseif (substr($str, 0, 4) == 'ITSF' && $extname != 'txt') {
            $format = 'chm';
        } elseif (substr($str, 0, 4) == "\x2ERMF") {
            $format = 'rm';
        } elseif ($extname == 'sql') {
            $format = 'sql';
        } elseif ($extname == 'txt') {
            $format = 'txt';
        }
    }
    
    if ($limit_ext_types && stristr($limit_ext_types, '|' . $format . '|') === false) {
        $format = '';
    }
    
    return $format;
}

/**
 * 对 MYSQL LIKE 的内容进行转义
 *
 * @access public
 * @param string string 内容
 * @return string
 */
function mysql_like_quote($str)
{
    return strtr($str, array(
        "\\\\" => "\\\\\\\\",
        '_' => '\_',
        '%' => '\%',
        "\'" => "\\\\\'"
    ));
}

/**
 * 获取服务器的ip
 *
 * @access public
 *
 * @return string
 *
 */
function real_server_ip()
{
    static $serverip = NULL;
    
    if ($serverip !== NULL) {
        return $serverip;
    }
    
    if (isset($_SERVER)) {
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverip = $_SERVER['SERVER_ADDR'];
        } else {
            $serverip = '0.0.0.0';
        }
    } else {
        $serverip = getenv('SERVER_ADDR');
    }
    
    return $serverip;
}

/**
 * 自定义 header 函数，用于过滤可能出现的安全隐患
 *
 * @param string string 内容
 *
 * @return void
 *
 */
function ecs_header($string, $replace = true, $http_response_code = 0)
{
    if (strpos($string, '../upgrade/index.php') === 0) {
        echo '<script type="text/javascript">window.location.href="' . $string . '";</script>';
    }
    $string = str_replace(array(
        "\r",
        "\n"
    ), array(
        '',
        ''
    ), $string);
    
    if (preg_match('/^\s*location:/is', $string)) {
        @header($string . "\n", $replace);
        
        exit();
    }
    
    if (empty($http_response_code) || PHP_VERSION < '4.3') {
        @header($string, $replace);
    } else {
        @header($string, $replace, $http_response_code);
    }
}

function ecs_iconv($source_lang, $target_lang, $source_string = '')
{
    static $chs = NULL;
    
    /* 如果字符串为空或者字符串不需要转换，直接返回 */
    if ($source_lang == $target_lang || $source_string == '' || preg_match("/[\x80-\xFF]+/", $source_string) == 0) {
        return $source_string;
    }
    
    if ($chs === NULL) {
        require_once (ROOT_PATH . 'includes/cls_iconv.php');
        $chs = new Chinese(ROOT_PATH);
    }
    
    return $chs->Convert($source_lang, $target_lang, $source_string);
}

function ecs_geoip($ip)
{
    static $fp = NULL, $offset = array(), $index = NULL;
    
    $ip = gethostbyname($ip);
    $ipdot = explode('.', $ip);
    $ip = pack('N', ip2long($ip));
    
    $ipdot[0] = (int) $ipdot[0];
    $ipdot[1] = (int) $ipdot[1];
    if ($ipdot[0] == 10 || $ipdot[0] == 127 || ($ipdot[0] == 192 && $ipdot[1] == 168) || ($ipdot[0] == 172 && ($ipdot[1] >= 16 && $ipdot[1] <= 31))) {
        return 'LAN';
    }
    
    if ($fp === NULL) {
        $fp = fopen(ROOT_PATH . 'includes/codetable/ipdata.dat', 'rb');
        if ($fp === false) {
            return 'Invalid IP data file';
        }
        $offset = unpack('Nlen', fread($fp, 4));
        if ($offset['len'] < 4) {
            return 'Invalid IP data file';
        }
        $index = fread($fp, $offset['len'] - 4);
    }
    
    $length = $offset['len'] - 1028;
    $start = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
            $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = unpack('Clen', $index{$start + 7});
            break;
        }
    }
    
    fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    $area = fread($fp, $index_length['len']);
    
    fclose($fp);
    $fp = NULL;
    
    return $area;
}

/**
 * 去除字符串右侧可能出现的乱码
 *
 * @param string $str 字符串
 *
 * @return string
 */
function trim_right($str)
{
    $len = strlen($str);
    /* 为空或单个字符直接返回 */
    if ($len == 0 || ord($str{$len - 1}) < 127) {
        return $str;
    }
    /* 有前导字符的直接把前导字符去掉 */
    if (ord($str{$len - 1}) >= 192) {
        return substr($str, 0, $len - 1);
    }
    /* 有非独立的字符，先把非独立字符去掉，再验证非独立的字符是不是一个完整的字，不是连原来前导字符也截取掉 */
    $r_len = strlen(rtrim($str, "\x80..\xBF"));
    if ($r_len == 0 || ord($str{$r_len - 1}) < 127) {
        return sub_str($str, 0, $r_len);
    }
    
    $as_num = ord(~ $str{$r_len - 1});
    if ($as_num > (1 << (6 + $r_len - $len))) {
        return $str;
    } else {
        return substr($str, 0, $r_len - 1);
    }
}

/**
 * 将上传文件转移到指定位置
 *
 * @param string $file_name
 * @param string $target_name
 * @return blog
 */
function move_upload_file($file_name, $target_name = '')
{
    if (function_exists("move_uploaded_file")) {
        if (move_uploaded_file($file_name, $target_name)) {
            @chmod($target_name, 0755);
            return true;
        } else 
            if (copy($file_name, $target_name)) {
                @chmod($target_name, 0755);
                return true;
            }
    } elseif (copy($file_name, $target_name)) {
        @chmod($target_name, 0755);
        return true;
    }
    return false;
}

/**
 * 将JSON传递的参数转码
 *
 * @param string $str
 * @return string
 */
function json_str_iconv($str)
{
    if (EC_CHARSET != 'utf-8') {
        if (is_string($str)) {
            return addslashes(stripslashes(ecs_iconv('utf-8', EC_CHARSET, $str)));
        } elseif (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = json_str_iconv($value);
            }
            return $str;
        } elseif (is_object($str)) {
            foreach ($str as $key => $value) {
                $str->$key = json_str_iconv($value);
            }
            return $str;
        } else {
            return $str;
        }
    }
    return $str;
}

/**
 * 循环转码成utf8内容
 *
 * @param string $str
 * @return string
 */
function to_utf8_iconv($str)
{
    if (EC_CHARSET != 'utf-8') {
        if (is_string($str)) {
            return ecs_iconv(EC_CHARSET, 'utf-8', $str);
        } elseif (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = to_utf8_iconv($value);
            }
            return $str;
        } elseif (is_object($str)) {
            foreach ($str as $key => $value) {
                $str->$key = to_utf8_iconv($value);
            }
            return $str;
        } else {
            return $str;
        }
    }
    return $str;
}

/**
 * 获取文件后缀名,并判断是否合法
 *
 * @param string $file_name
 * @param array $allow_type
 * @return blob
 */
function get_file_suffix($file_name, $allow_type = array())
{
    $file_suffix = strtolower(array_pop(explode('.', $file_name)));
    if (empty($allow_type)) {
        return $file_suffix;
    } else {
        if (in_array($file_suffix, $allow_type)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 读结果缓存文件
 *
 * @param s string $cache_name
 *
 * @return array $data
 */
function read_static_cache($cache_name)
{
    if ((DEBUG_MODE & 2) == 2) {
        return false;
    }
    static $result = array();
    if (! empty($result[$cache_name])) {
        return $result[$cache_name];
    }
    $cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
    if (file_exists($cache_file_path)) {
        include_once ($cache_file_path);
        $result[$cache_name] = $data;
        return $result[$cache_name];
    } else {
        return false;
    }
}

/**
 * 写结果缓存文件
 *
 * @param s string $cache_name
 * @param s string $caches
 *
 * @return
 *
 */
function write_static_cache($cache_name, $caches)
{
    if ((DEBUG_MODE & 2) == 2) {
        return false;
    }
    $cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
    $content = "<?php\r\n";
    $content .= "\$data = " . var_export($caches, true) . ";\r\n";
    $content .= "?>";
    file_put_contents($cache_file_path, $content, LOCK_EX);
}

/**
 * *****************************************
 * 公用函数
 * *****************************************
 */

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access public
 * @param mix $item_list 列表数组或字符串
 * @param string $field_name 字段名称
 *
 * @return void
 */
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list)) {
        return $field_name . " IN ('') ";
    } else {
        if (! is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list as $item) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . " IN ('') ";
        } else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 验证输入的邮件地址是否合法
 *
 * @access public
 * @param string $email 需要验证的邮件地址
 *
 * @return bool
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @access public
 * @param string $time
 * @return void
 */
function is_time($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';
    
    return preg_match($pattern, $time);
}

/**
 * 获得查询时间和次数，并赋值给smarty
 *
 * @access public
 * @return void
 */
function assign_query_info()
{
    if ($GLOBALS['db']->queryTime == '') {
        $query_time = 0;
    } else {
        if (PHP_VERSION >= '5.0.0') {
            $query_time = number_format(microtime(true) - $GLOBALS['db']->queryTime, 6);
        } else {
            list ($now_usec, $now_sec) = explode(' ', microtime());
            list ($start_usec, $start_sec) = explode(' ', $GLOBALS['db']->queryTime);
            $query_time = number_format(($now_sec - $start_sec) + ($now_usec - $start_usec), 6);
        }
    }
    $GLOBALS['smarty']->assign('query_info', sprintf($GLOBALS['_LANG']['query_info'], $GLOBALS['db']->queryCount, $query_time));
    
    /* 内存占用情况 */
    if ($GLOBALS['_LANG']['memory_info'] && function_exists('memory_get_usage')) {
        $GLOBALS['smarty']->assign('memory_info', sprintf($GLOBALS['_LANG']['memory_info'], memory_get_usage() / 1048576));
    }
    
    /* 是否启用了 gzip */
    $gzip_enabled = gzip_enabled() ? $GLOBALS['_LANG']['gzip_enabled'] : $GLOBALS['_LANG']['gzip_disabled'];
    $GLOBALS['smarty']->assign('gzip_enabled', $gzip_enabled);
}

/**
 * 创建地区的返回信息
 *
 * @access public
 * @param array $arr 地区数组 *
 * @return void
 */
function region_result($parent, $sel_name, $type)
{
    global $cp;
    
    $arr = get_regions($type, $parent);
    foreach ($arr as $v) {
        $region = & $cp->add_node('region');
        $region_id = & $region->add_node('id');
        $region_name = & $region->add_node('name');
        
        $region_id->set_data($v['region_id']);
        $region_name->set_data($v['region_name']);
    }
    $select_obj = & $cp->add_node('select');
    $select_obj->set_data($sel_name);
}

/**
 * 获得指定国家的所有省份
 *
 * @access public
 * @param int country 国家的编号
 * @return array
 */
function get_regions($type = 0, $parent = 0)
{
    $sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') . " WHERE region_type = '$type' AND parent_id = '$parent'";
    
    return $GLOBALS['db']->GetAll($sql);
}

/**
 * 获得配送区域中指定的配送方式的配送费用的计算参数
 *
 * @access public
 * @param int $area_id 配送区域ID
 *
 * @return array;
 */
function get_shipping_config($area_id)
{
    /* 获得配置信息 */
    $sql = 'SELECT configure FROM ' . $GLOBALS['ecs']->table('shipping_area') . " WHERE shipping_area_id = '$area_id'";
    $cfg = $GLOBALS['db']->GetOne($sql);
    
    if ($cfg) {
        /* 拆分成配置信息的数组 */
        $arr = unserialize($cfg);
    } else {
        $arr = array();
    }
    
    return $arr;
}

/**
 * 初始化会员数据整合类
 *
 * @access public
 * @return object
 */
function &init_users()
{
    $set_modules = false;
    static $cls = null;
    if ($cls != null) {
        return $cls;
    }
    include_once (ROOT_PATH . 'includes/modules/integrates/' . $GLOBALS['_CFG']['integrate_code'] . '.php');
    $cfg = unserialize($GLOBALS['_CFG']['integrate_config']);
    $cls = new $GLOBALS['_CFG']['integrate_code']($cfg);
    
    return $cls;
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access public
 * @param int $cat_id 分类的ID
 * @param int $selected 当前选中分类的ID
 * @param boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param int $level 限定返回的级数。为0时返回所有级数
 * @param int $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return mix
 */
function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
    static $res = NULL;
    
    if ($res === NULL) {
        $data = read_static_cache('cat_pid_releate');
        if ($data === false) {
            $sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children " . 'FROM ' . $GLOBALS['ecs']->table('category') . " AS c " . "LEFT JOIN " . $GLOBALS['ecs']->table('category') . " AS s ON s.parent_id=c.cat_id " . "GROUP BY c.cat_id " . 'ORDER BY c.parent_id, c.sort_order ASC';
            $res = $GLOBALS['db']->getAll($sql);
            
            $sql = "SELECT cat_id, COUNT(*) AS goods_num " . " FROM " . $GLOBALS['ecs']->table('goods') . " WHERE is_delete = 0 AND is_on_sale = 1 " . " GROUP BY cat_id";
            $res2 = $GLOBALS['db']->getAll($sql);
            
            $sql = "SELECT gc.cat_id, COUNT(*) AS goods_num " . " FROM " . $GLOBALS['ecs']->table('goods_cat') . " AS gc , " . $GLOBALS['ecs']->table('goods') . " AS g " . " WHERE g.goods_id = gc.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 " . " GROUP BY gc.cat_id";
            $res3 = $GLOBALS['db']->getAll($sql);
            
            $newres = array();
            foreach ($res2 as $k => $v) {
                $newres[$v['cat_id']] = $v['goods_num'];
                foreach ($res3 as $ks => $vs) {
                    if ($v['cat_id'] == $vs['cat_id']) {
                        $newres[$v['cat_id']] = $v['goods_num'] + $vs['goods_num'];
                    }
                }
            }
            
            foreach ($res as $k => $v) {
                $res[$k]['goods_num'] = ! empty($newres[$v['cat_id']]) ? $newres[$v['cat_id']] : 0;
            }
            // 如果数组过大，不采用静态缓存方式
            if (count($res) <= 1000) {
                write_static_cache('cat_pid_releate', $res);
            }
        } else {
            $res = $data;
        }
    }
    
    if (empty($res) == true) {
        return $re_type ? '' : array();
    }
    
    $options = cat_options($cat_id, $res); // 获得指定分类下的子分类的数组
    
    $children_level = 99999; // 大于这个分类的将被删除
    if ($is_show_all == false) {
        foreach ($options as $key => $val) {
            if ($val['level'] > $children_level) {
                unset($options[$key]);
            } else {
                if ($val['is_show'] == 0) {
                    unset($options[$key]);
                    if ($children_level > $val['level']) {
                        $children_level = $val['level']; // 标记一下，这样子分类也能删除
                    }
                } else {
                    $children_level = 99999; // 恢复初始值
                }
            }
        }
    }
    
    /* 截取到指定的缩减级别 */
    if ($level > 0) {
        if ($cat_id == 0) {
            $end_level = $level;
        } else {
            $first_item = reset($options); // 获取第一个元素
            $end_level = $first_item['level'] + $level;
        }
        
        /* 保留level小于end_level的部分 */
        foreach ($options as $key => $val) {
            if ($val['level'] >= $end_level) {
                unset($options[$key]);
            }
        }
    }
    
    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
        }
        
        return $select;
    } else {
        foreach ($options as $key => $value) {
            $options[$key]['url'] = build_uri('category', array(
                'cid' => $value['cat_id']
            ), $value['cat_name']);
        }
        
        return $options;
    }
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access private
 * @param int $cat_id 上级分类ID
 * @param array $arr 含有所有分类的数组
 * @param int $level 级别
 * @return void
 */
function cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();
    
    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }
    
    if (! isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
        $data = read_static_cache('cat_option_static');
        if ($data === false) {
            while (! empty($arr)) {
                foreach ($arr as $key => $value) {
                    $cat_id = $value['cat_id'];
                    if ($level == 0 && $last_cat_id == 0) {
                        if ($value['parent_id'] > 0) {
                            break;
                        }
                        
                        $options[$cat_id] = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id'] = $cat_id;
                        $options[$cat_id]['name'] = $value['cat_name'];
                        unset($arr[$key]);
                        
                        if ($value['has_children'] == 0) {
                            continue;
                        }
                        $last_cat_id = $cat_id;
                        $cat_id_array = array(
                            $cat_id
                        );
                        $level_array[$last_cat_id] = ++ $level;
                        continue;
                    }
                    
                    if ($value['parent_id'] == $last_cat_id) {
                        $options[$cat_id] = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id'] = $cat_id;
                        $options[$cat_id]['name'] = $value['cat_name'];
                        unset($arr[$key]);
                        
                        if ($value['has_children'] > 0) {
                            if (end($cat_id_array) != $last_cat_id) {
                                $cat_id_array[] = $last_cat_id;
                            }
                            $last_cat_id = $cat_id;
                            $cat_id_array[] = $cat_id;
                            $level_array[$last_cat_id] = ++ $level;
                        }
                    } elseif ($value['parent_id'] > $last_cat_id) {
                        break;
                    }
                }
                
                $count = count($cat_id_array);
                if ($count > 1) {
                    $last_cat_id = array_pop($cat_id_array);
                } elseif ($count == 1) {
                    if ($last_cat_id != end($cat_id_array)) {
                        $last_cat_id = end($cat_id_array);
                    } else {
                        $level = 0;
                        $last_cat_id = 0;
                        $cat_id_array = array();
                        continue;
                    }
                }
                
                if ($last_cat_id && isset($level_array[$last_cat_id])) {
                    $level = $level_array[$last_cat_id];
                } else {
                    $level = 0;
                }
            }
            // 如果数组过大，不采用静态缓存方式
            if (count($options) <= 2000) {
                write_static_cache('cat_option_static', $options);
            }
        } else {
            $options = $data;
        }
        $cat_options[0] = $options;
    } else {
        $options = $cat_options[0];
    }
    
    if (! $spec_cat_id) {
        return $options;
    } else {
        if (empty($options[$spec_cat_id])) {
            return array();
        }
        
        $spec_cat_id_level = $options[$spec_cat_id]['level'];
        
        foreach ($options as $key => $value) {
            if ($key != $spec_cat_id) {
                unset($options[$key]);
            } else {
                break;
            }
        }
        
        $spec_cat_id_array = array();
        foreach ($options as $key => $value) {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) || ($spec_cat_id_level > $value['level'])) {
                break;
            } else {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;
        
        return $spec_cat_id_array;
    }
}

/**
 * 载入配置信息
 *
 * @access public
 * @return array
 */
function load_config()
{
    $res = M('shop_config')->field('code, value')
        ->where('parent_id > 0')
        ->cache(true)
        ->select();
    
    $arr = array();
    foreach ($res as $row) {
        $arr[$row['code']] = $row['value'];
    }
    
    /* 对数值型设置处理 */
    $arr['watermark_alpha'] = intval($arr['watermark_alpha']);
    $arr['market_price_rate'] = floatval($arr['market_price_rate']);
    $arr['integral_scale'] = floatval($arr['integral_scale']);
    // $arr['integral_percent'] = floatval($arr['integral_percent']);
    $arr['cache_time'] = intval($arr['cache_time']);
    $arr['thumb_width'] = intval($arr['thumb_width']);
    $arr['thumb_height'] = intval($arr['thumb_height']);
    $arr['image_width'] = intval($arr['image_width']);
    $arr['image_height'] = intval($arr['image_height']);
    $arr['best_number'] = ! empty($arr['best_number']) && intval($arr['best_number']) > 0 ? intval($arr['best_number']) : 3;
    $arr['new_number'] = ! empty($arr['new_number']) && intval($arr['new_number']) > 0 ? intval($arr['new_number']) : 3;
    $arr['hot_number'] = ! empty($arr['hot_number']) && intval($arr['hot_number']) > 0 ? intval($arr['hot_number']) : 3;
    $arr['promote_number'] = ! empty($arr['promote_number']) && intval($arr['promote_number']) > 0 ? intval($arr['promote_number']) : 3;
    $arr['top_number'] = intval($arr['top_number']) > 0 ? intval($arr['top_number']) : 10;
    $arr['history_number'] = intval($arr['history_number']) > 0 ? intval($arr['history_number']) : 5;
    $arr['comments_number'] = intval($arr['comments_number']) > 0 ? intval($arr['comments_number']) : 5;
    $arr['article_number'] = intval($arr['article_number']) > 0 ? intval($arr['article_number']) : 5;
    $arr['page_size'] = intval($arr['page_size']) > 0 ? intval($arr['page_size']) : 10;
    $arr['bought_goods'] = intval($arr['bought_goods']);
    $arr['goods_name_length'] = intval($arr['goods_name_length']);
    $arr['top10_time'] = intval($arr['top10_time']);
    $arr['goods_gallery_number'] = intval($arr['goods_gallery_number']) ? intval($arr['goods_gallery_number']) : 5;
    $arr['no_picture'] = ! empty($arr['no_picture']) ? str_replace('../', './', $arr['no_picture']) : 'images/no_picture.gif'; // 修改默认商品图片的路径
    $arr['qq'] = ! empty($arr['qq']) ? $arr['qq'] : '';
    $arr['ww'] = ! empty($arr['ww']) ? $arr['ww'] : '';
    $arr['default_storage'] = isset($arr['default_storage']) ? intval($arr['default_storage']) : 1;
    $arr['min_goods_amount'] = isset($arr['min_goods_amount']) ? floatval($arr['min_goods_amount']) : 0;
    $arr['one_step_buy'] = empty($arr['one_step_buy']) ? 0 : 1;
    $arr['invoice_type'] = empty($arr['invoice_type']) ? array(
        'type' => array(),
        'rate' => array()
    ) : unserialize($arr['invoice_type']);
    $arr['show_order_type'] = isset($arr['show_order_type']) ? $arr['show_order_type'] : 0; // 显示方式默认为列表方式
    $arr['help_open'] = isset($arr['help_open']) ? $arr['help_open'] : 1; // 显示方式默认为列表方式
    
    if (! isset($arr['ecs_version'])) {
        /* 如果没有版本号则默认为1.0 */
        $arr['ecs_version'] = 'v1.0';
    }
    
    // 限定语言项
    $lang_array = array(
        'zh_cn',
        'zh_tw',
        'en_us'
    );
    if (empty($arr['lang']) || ! in_array($arr['lang'], $lang_array)) {
        $arr['lang'] = 'zh_cn'; // 默认语言为简体中文
    }
    
    if (empty($arr['integrate_code'])) {
        $arr['integrate_code'] = 'ecshop'; // 默认的会员整合插件为 ecshop
    }
    
    return $arr;
}

/**
 * 取得品牌列表
 * 
 * @return array 品牌列表 id => name
 */
function get_brand_list()
{
    $sql = 'SELECT brand_id, brand_name FROM ' . $GLOBALS['ecs']->table('brand') . ' ORDER BY sort_order';
    $res = $GLOBALS['db']->getAll($sql);
    
    $brand_list = array();
    foreach ($res as $row) {
        $brand_list[$row['brand_id']] = addslashes($row['brand_name']);
    }
    
    return $brand_list;
}

/**
 * 获得某个分类下
 *
 * @access public
 * @param int $cat
 * @return array
 */
function get_brands($cat = 0, $app = 'brand')
{
    global $page_libs;
    $template = basename(PHP_SELF);
    $template = substr($template, 0, strrpos($template, '.'));
    include_once (ROOT_PATH . ADMIN_PATH . '/includes/lib_template.php');
    static $static_page_libs = null;
    if ($static_page_libs == null) {
        $static_page_libs = $page_libs;
    }
    
    $children = ($cat > 0) ? ' AND ' . get_children($cat) : '';
    
    $sql = "SELECT b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, COUNT(*) AS goods_num, IF(b.brand_logo > '', '1', '0') AS tag " . "FROM " . $GLOBALS['ecs']->table('brand') . "AS b, " . $GLOBALS['ecs']->table('goods') . " AS g " . "WHERE g.brand_id = b.brand_id $children AND is_show = 1 " . " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 " . "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY tag DESC, b.sort_order ASC";
    if (isset($static_page_libs[$template]['/library/brands.lbi'])) {
        $num = get_library_number("brands");
        $sql .= " LIMIT $num ";
    }
    $row = $GLOBALS['db']->getAll($sql);
    
    foreach ($row as $key => $val) {
        $row[$key]['url'] = build_uri($app, array(
            'cid' => $cat,
            'bid' => $val['brand_id']
        ), $val['brand_name']);
        $row[$key]['brand_desc'] = htmlspecialchars($val['brand_desc'], ENT_QUOTES);
    }
    
    return $row;
}

/**
 * 所有的促销活动信息
 *
 * @access public
 * @return array
 */
function get_promotion_info($goods_id = '')
{
    $snatch = array();
    $group = array();
    $auction = array();
    $package = array();
    $favourable = array();
    
    $gmtime = gmtime();
    $sql = 'SELECT act_id, act_name, act_type, start_time, end_time FROM ' . $GLOBALS['ecs']->table('goods_activity') . " WHERE is_finished=0 AND start_time <= '$gmtime' AND end_time >= '$gmtime'";
    if (! empty($goods_id)) {
        $sql .= " AND goods_id = '$goods_id'";
    }
    $res = $GLOBALS['db']->getAll($sql);
    foreach ($res as $data) {
        switch ($data['act_type']) {
            case GAT_SNATCH: // 夺宝奇兵
                $snatch[$data['act_id']]['act_name'] = $data['act_name'];
                $snatch[$data['act_id']]['url'] = build_uri('snatch', array(
                    'sid' => $data['act_id']
                ));
                $snatch[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                $snatch[$data['act_id']]['sort'] = $data['start_time'];
                $snatch[$data['act_id']]['type'] = 'snatch';
                break;
            
            case GAT_GROUP_BUY: // 团购
                $group[$data['act_id']]['act_name'] = $data['act_name'];
                $group[$data['act_id']]['url'] = build_uri('group_buy', array(
                    'gbid' => $data['act_id']
                ));
                $group[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                $group[$data['act_id']]['sort'] = $data['start_time'];
                $group[$data['act_id']]['type'] = 'group_buy';
                break;
            
            case GAT_AUCTION: // 拍卖
                $auction[$data['act_id']]['act_name'] = $data['act_name'];
                $auction[$data['act_id']]['url'] = build_uri('auction', array(
                    'auid' => $data['act_id']
                ));
                $auction[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                $auction[$data['act_id']]['sort'] = $data['start_time'];
                $auction[$data['act_id']]['type'] = 'auction';
                break;
            
            case GAT_PACKAGE: // 礼包
                $package[$data['act_id']]['act_name'] = $data['act_name'];
                $package[$data['act_id']]['url'] = 'package.php#' . $data['act_id'];
                $package[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $data['start_time']), local_date('Y-m-d', $data['end_time']));
                $package[$data['act_id']]['sort'] = $data['start_time'];
                $package[$data['act_id']]['type'] = 'package';
                break;
        }
    }
    
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $favourable = array();
    $sql = 'SELECT act_id, act_range, act_range_ext, act_name, start_time, end_time FROM ' . $GLOBALS['ecs']->table('favourable_activity') . " WHERE start_time <= '$gmtime' AND end_time >= '$gmtime'";
    if (! empty($goods_id)) {
        $sql .= " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'";
    }
    $res = $GLOBALS['db']->getAll($sql);
    
    if (empty($goods_id)) {
        foreach ($res as $rows) {
            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
            $favourable[$rows['act_id']]['url'] = 'activity.php';
            $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
            $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
            $favourable[$rows['act_id']]['type'] = 'favourable';
        }
    } else {
        $sql = "SELECT cat_id, brand_id FROM " . $GLOBALS['ecs']->table('goods') . "WHERE goods_id = '$goods_id'";
        $row = $GLOBALS['db']->getRow($sql);
        $category_id = $row['cat_id'];
        $brand_id = $row['brand_id'];
        
        foreach ($res as $rows) {
            if ($rows['act_range'] == FAR_ALL) {
                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                $favourable[$rows['act_id']]['url'] = 'activity.php';
                $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                $favourable[$rows['act_id']]['type'] = 'favourable';
            } elseif ($rows['act_range'] == FAR_CATEGORY) {
                /* 找出分类id的子分类id */
                $id_list = array();
                $raw_id_list = explode(',', $rows['act_range_ext']);
                foreach ($raw_id_list as $id) {
                    $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
                }
                $ids = join(',', array_unique($id_list));
                
                if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                }
            } elseif ($rows['act_range'] == FAR_BRAND) {
                if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                }
            } elseif ($rows['act_range'] == FAR_GOODS) {
                if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                    $favourable[$rows['act_id']]['url'] = 'activity.php';
                    $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], local_date('Y-m-d', $rows['start_time']), local_date('Y-m-d', $rows['end_time']));
                    $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                    $favourable[$rows['act_id']]['type'] = 'favourable';
                }
            }
        }
    }
    
    // if(!empty($goods_id))
    // {
    // return array('snatch'=>$snatch, 'group_buy'=>$group, 'auction'=>$auction, 'favourable'=>$favourable);
    // }
    
    $sort_time = array();
    $arr = array_merge($snatch, $group, $auction, $package, $favourable);
    foreach ($arr as $key => $value) {
        $sort_time[] = $value['sort'];
    }
    array_multisort($sort_time, SORT_NUMERIC, SORT_DESC, $arr);
    
    return $arr;
}

/**
 * 获得指定分类下所有底层分类的ID
 *
 * @access public
 * @param integer $cat 指定的分类ID
 * @return string
 */
function get_children($cat = 0)
{
    return 'g.cat_id ' . db_create_in(array_unique(array_merge(array(
        $cat
    ), array_keys(cat_list($cat, 0, false)))));
}

/**
 * 获得指定文章分类下所有底层分类的ID
 *
 * @access public
 * @param integer $cat 指定的分类ID
 *
 * @return void
 */
function get_article_children($cat = 0)
{
    return db_create_in(array_unique(array_merge(array(
        $cat
    ), array_keys(article_cat_list($cat, 0, false)))), 'cat_id');
}

/**
 * 获取邮件模板
 *
 * @access public
 * @param : $tpl_name[string] 模板代码
 *
 * @return array
 */
function get_mail_template($tpl_name)
{
    $sql = 'SELECT template_subject, is_html, template_content FROM ' . $GLOBALS['ecs']->table('mail_templates') . " WHERE template_code = '$tpl_name'";
    
    return $GLOBALS['db']->GetRow($sql);
}

/**
 * 记录订单操作记录
 *
 * @access public
 * @param string $order_sn 订单编号
 * @param integer $order_status 订单状态
 * @param integer $shipping_status 配送状态
 * @param integer $pay_status 付款状态
 * @param string $note 备注
 * @param string $username 用户名，用户自己的操作则为 buyer
 * @return void
 */
function order_action($order_sn, $order_status, $shipping_status, $pay_status, $note = '', $username = null, $place = 0)
{
    if (is_null($username)) {
        $username = $_SESSION['admin_name'];
    }
    
    $sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('order_action') . ' (order_id, action_user, order_status, shipping_status, pay_status, action_place, action_note, log_time) ' . 'SELECT ' . "order_id, '$username', '$order_status', '$shipping_status', '$pay_status', '$place', '$note', '" . gmtime() . "' " . 'FROM ' . $GLOBALS['ecs']->table('order_info') . " WHERE order_sn = '$order_sn'";
    $GLOBALS['db']->query($sql);
}

/**
 * 格式化商品价格
 *
 * @access public
 * @param float $price 商品价格
 * @return string
 */
function price_format($price, $change_price = true)
{
    if ($price === '') {
        $price = 0;
    }
    if ($change_price && defined('ECS_ADMIN') === false) {
        switch ($GLOBALS['_CFG']['price_format']) {
            case 0:
                $price = number_format($price, 2, '.', '');
                break;
            case 1: // 保留不为 0 的尾数
                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));
                
                if (substr($price, - 1) == '.') {
                    $price = substr($price, 0, - 1);
                }
                break;
            case 2: // 不四舍五入，保留1位
                $price = substr(number_format($price, 2, '.', ''), 0, - 1);
                break;
            case 3: // 直接取整
                $price = intval($price);
                break;
            case 4: // 四舍五入，保留 1 位
                $price = number_format($price, 1, '.', '');
                break;
            case 5: // 先四舍五入，不保留小数
                $price = round($price);
                break;
        }
    } else {
        $price = number_format($price, 2, '.', '');
    }
    
    return sprintf($GLOBALS['_CFG']['currency_format'], $price);
}

/**
 * 返回订单中的虚拟商品
 *
 * @access public
 * @param int $order_id 订单id值
 * @param bool $shipping 是否已经发货
 *
 * @return array()
 */
function get_virtual_goods($order_id, $shipping = false)
{
    if ($shipping) {
        $sql = 'SELECT goods_id, goods_name, send_number AS num, extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id = '$order_id' AND extension_code > ''";
    } else {
        $sql = 'SELECT goods_id, goods_name, (goods_number - send_number) AS num, extension_code FROM ' . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id = '$order_id' AND is_real = 0 AND (goods_number - send_number) > 0 AND extension_code > '' ";
    }
    $res = $GLOBALS['db']->getAll($sql);
    
    $virtual_goods = array();
    foreach ($res as $row) {
        $virtual_goods[$row['extension_code']][] = array(
            'goods_id' => $row['goods_id'],
            'goods_name' => $row['goods_name'],
            'num' => $row['num']
        );
    }
    
    return $virtual_goods;
}

/**
 * 虚拟商品发货
 *
 * @access public
 * @param array $virtual_goods 虚拟商品数组
 * @param string $msg 错误信息
 * @param string $order_sn 订单号。
 * @param string $process 设定当前流程：split，发货分单流程；other，其他，默认。
 *
 * @return bool
 */
function virtual_goods_ship(&$virtual_goods, &$msg, $order_sn, $return_result = false, $process = 'other')
{
    $virtual_card = array();
    foreach ($virtual_goods as $code => $goods_list) {
        /* 只处理虚拟卡 */
        if ($code == 'virtual_card') {
            foreach ($goods_list as $goods) {
                if (virtual_card_shipping($goods, $order_sn, $msg, $process)) {
                    if ($return_result) {
                        $virtual_card[] = array(
                            'goods_id' => $goods['goods_id'],
                            'goods_name' => $goods['goods_name'],
                            'info' => virtual_card_result($order_sn, $goods)
                        );
                    }
                } else {
                    return false;
                }
            }
            $GLOBALS['smarty']->assign('virtual_card', $virtual_card);
        }
    }
    
    return true;
}

/**
 * 虚拟卡发货
 *
 * @access public
 * @param string $goods 商品详情数组
 * @param string $order_sn 本次操作的订单
 * @param string $msg 返回信息
 * @param string $process 设定当前流程：split，发货分单流程；other，其他，默认。
 *
 * @return boolen
 */
function virtual_card_shipping($goods, $order_sn, &$msg, $process = 'other')
{
    /* 包含加密解密函数所在文件 */
    include_once (ROOT_PATH . 'includes/lib_code.php');
    
    /* 检查有没有缺货 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('virtual_card') . " WHERE goods_id = '$goods[goods_id]' AND is_saled = 0 ";
    $num = $GLOBALS['db']->GetOne($sql);
    
    if ($num < $goods['num']) {
        $msg .= sprintf($GLOBALS['_LANG']['virtual_card_oos'], $goods['goods_name']);
        
        return false;
    }
    
    /* 取出卡片信息 */
    $sql = "SELECT card_id, card_sn, card_password, end_date, crc32 FROM " . $GLOBALS['ecs']->table('virtual_card') . " WHERE goods_id = '$goods[goods_id]' AND is_saled = 0  LIMIT " . $goods['num'];
    $arr = $GLOBALS['db']->getAll($sql);
    
    $card_ids = array();
    $cards = array();
    
    foreach ($arr as $virtual_card) {
        $card_info = array();
        
        /* 卡号和密码解密 */
        if ($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY)) {
            $card_info['card_sn'] = decrypt($virtual_card['card_sn']);
            $card_info['card_password'] = decrypt($virtual_card['card_password']);
        } elseif ($virtual_card['crc32'] == crc32(OLD_AUTH_KEY)) {
            $card_info['card_sn'] = decrypt($virtual_card['card_sn'], OLD_AUTH_KEY);
            $card_info['card_password'] = decrypt($virtual_card['card_password'], OLD_AUTH_KEY);
        } else {
            $msg .= 'error key';
            
            return false;
        }
        $card_info['end_date'] = date($GLOBALS['_CFG']['date_format'], $virtual_card['end_date']);
        $card_ids[] = $virtual_card['card_id'];
        $cards[] = $card_info;
    }
    
    /* 标记已经取出的卡片 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('virtual_card') . " SET " . "is_saled = 1 ," . "order_sn = '$order_sn' " . "WHERE " . db_create_in($card_ids, 'card_id');
    if (! $GLOBALS['db']->query($sql, 'SILENT')) {
        $msg .= $GLOBALS['db']->error();
        
        return false;
    }
    
    /* 更新库存 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET goods_number = goods_number - '$goods[num]' WHERE goods_id = '$goods[goods_id]'";
    $GLOBALS['db']->query($sql);
    
    if (true) {
        /* 获取订单信息 */
        $sql = "SELECT order_id, order_sn, consignee, email FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_sn = '$order_sn'";
        $order = $GLOBALS['db']->GetRow($sql);
        
        /* 更新订单信息 */
        if ($process == 'split') {
            $sql = "UPDATE " . $GLOBALS['ecs']->table('order_goods') . "
						SET send_number = send_number + '" . $goods['num'] . "'
						WHERE order_id = '" . $order['order_id'] . "'
						AND goods_id = '" . $goods['goods_id'] . "' ";
        } else {
            $sql = "UPDATE " . $GLOBALS['ecs']->table('order_goods') . "
						SET send_number = '" . $goods['num'] . "'
						WHERE order_id = '" . $order['order_id'] . "'
						AND goods_id = '" . $goods['goods_id'] . "' ";
        }
        
        if (! $GLOBALS['db']->query($sql, 'SILENT')) {
            $msg .= $GLOBALS['db']->error();
            
            return false;
        }
    }
    
    /* 发送邮件 */
    $GLOBALS['smarty']->assign('virtual_card', $cards);
    $GLOBALS['smarty']->assign('order', $order);
    $GLOBALS['smarty']->assign('goods', $goods);
    
    $GLOBALS['smarty']->assign('send_time', date('Y-m-d H:i:s'));
    $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
    $GLOBALS['smarty']->assign('send_date', date('Y-m-d'));
    $GLOBALS['smarty']->assign('sent_date', date('Y-m-d'));
    
    $tpl = get_mail_template('virtual_card');
    $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
    send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']);
    
    return true;
}

/**
 * 返回虚拟卡信息
 *
 * @access public
 * @param
 *
 * @return void
 */
function virtual_card_result($order_sn, $goods)
{
    /* 包含加密解密函数所在文件 */
    include_once (ROOT_PATH . 'includes/lib_code.php');
    
    /* 获取已经发送的卡片数据 */
    $sql = "SELECT card_sn, card_password, end_date, crc32 FROM " . $GLOBALS['ecs']->table('virtual_card') . " WHERE goods_id= '$goods[goods_id]' AND order_sn = '$order_sn' ";
    $res = $GLOBALS['db']->query($sql);
    
    $cards = array();
    
    while ($row = $GLOBALS['db']->FetchRow($res)) {
        /* 卡号和密码解密 */
        if ($row['crc32'] == 0 || $row['crc32'] == crc32(AUTH_KEY)) {
            $row['card_sn'] = decrypt($row['card_sn']);
            $row['card_password'] = decrypt($row['card_password']);
        } elseif ($row['crc32'] == crc32(OLD_AUTH_KEY)) {
            $row['card_sn'] = decrypt($row['card_sn'], OLD_AUTH_KEY);
            $row['card_password'] = decrypt($row['card_password'], OLD_AUTH_KEY);
        } else {
            $row['card_sn'] = '***';
            $row['card_password'] = '***';
        }
        
        $cards[] = array(
            'card_sn' => $row['card_sn'],
            'card_password' => $row['card_password'],
            'end_date' => date($GLOBALS['_CFG']['date_format'], $row['end_date'])
        );
    }
    
    return $cards;
}

/**
 * 获取指定 id snatch 活动的结果
 *
 * @access public
 * @param int $id snatch_id
 *
 * @return array array(user_name, bie_price, bid_time, num)
 * num通常为1，如果为2表示有2个用户取到最小值，但结果只返回最早出价用户。
 */
function get_snatch_result($id)
{
    $sql = 'SELECT u.user_id, u.user_name, u.email, lg.bid_price, lg.bid_time, count(*) as num' . ' FROM ' . $GLOBALS['ecs']->table('snatch_log') . ' AS lg ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON lg.user_id = u.user_id' . " WHERE lg.snatch_id = '$id'" . ' GROUP BY lg.bid_price' . ' ORDER BY num ASC, lg.bid_price ASC, lg.bid_time ASC LIMIT 1';
    $rec = $GLOBALS['db']->GetRow($sql);
    
    if ($rec) {
        $rec['bid_time'] = local_date($GLOBALS['_CFG']['time_format'], $rec['bid_time']);
        $rec['formated_bid_price'] = price_format($rec['bid_price'], false);
        
        /* 活动信息 */
        $sql = 'SELECT ext_info " .
				   " FROM ' . $GLOBALS['ecs']->table('goods_activity') . " WHERE act_id= '$id' AND act_type=" . GAT_SNATCH . " LIMIT 1";
        $row = $GLOBALS['db']->getOne($sql);
        $info = unserialize($row);
        
        if (! empty($info['max_price'])) {
            $rec['buy_price'] = ($rec['bid_price'] > $info['max_price']) ? $info['max_price'] : $rec['bid_price'];
        } else {
            $rec['buy_price'] = $rec['bid_price'];
        }
        
        /* 检查订单 */
        $sql = "SELECT COUNT(*)" . " FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE extension_code = 'snatch'" . " AND extension_id = '$id'" . " AND order_status " . db_create_in(array(
            OS_CONFIRMED,
            OS_UNCONFIRMED
        ));
        
        $rec['order_count'] = $GLOBALS['db']->getOne($sql);
    }
    
    return $rec;
}

/**
 * 清除指定后缀的模板缓存或编译文件
 *
 * @access public
 * @param bool $is_cache 是否清除缓存还是清出编译文件
 * @param string $ext 需要删除的文件名，不包含后缀
 *
 * @return int 返回清除的文件个数
 */
function clear_tpl_files($is_cache = true, $ext = '')
{
    $dirs = array();
    
    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) {
        $tmp_dir = DATA_DIR;
    } else {
        $tmp_dir = 'temp';
    }
    if ($is_cache) {
        $cache_dir = ROOT_PATH . $tmp_dir . '/caches/';
        $dirs[] = ROOT_PATH . $tmp_dir . '/query_caches/';
        $dirs[] = ROOT_PATH . $tmp_dir . '/static_caches/';
        for ($i = 0; $i < 16; $i ++) {
            $hash_dir = $cache_dir . dechex($i);
            $dirs[] = $hash_dir . '/';
        }
    } else {
        $dirs[] = ROOT_PATH . $tmp_dir . '/compiled/';
        $dirs[] = ROOT_PATH . $tmp_dir . '/compiled/admin/';
    }
    
    $str_len = strlen($ext);
    $count = 0;
    
    foreach ($dirs as $dir) {
        $folder = @opendir($dir);
        
        if ($folder === false) {
            continue;
        }
        
        while ($file = readdir($folder)) {
            if ($file == '.' || $file == '..' || $file == 'index.htm' || $file == 'index.html') {
                continue;
            }
            if (is_file($dir . $file)) {
                /* 如果有文件名则判断是否匹配 */
                $pos = ($is_cache) ? strrpos($file, '_') : strrpos($file, '.');
                
                if ($str_len > 0 && $pos !== false) {
                    $ext_str = substr($file, 0, $pos);
                    
                    if ($ext_str == $ext) {
                        if (@unlink($dir . $file)) {
                            $count ++;
                        }
                    }
                } else {
                    if (@unlink($dir . $file)) {
                        $count ++;
                    }
                }
            }
        }
        closedir($folder);
    }
    
    return $count;
}

/**
 * 清除模版编译文件
 *
 * @access public
 * @param mix $ext 模版文件名， 不包含后缀
 * @return void
 */
function clear_compiled_files($ext = '')
{
    return clear_tpl_files(false, $ext);
}

/**
 * 清除缓存文件
 *
 * @access public
 * @param mix $ext 模版文件名， 不包含后缀
 * @return void
 */
function clear_cache_files($ext = '')
{
    return clear_tpl_files(true, $ext);
}

/**
 * 清除模版编译和缓存文件
 *
 * @access public
 * @param mix $ext 模版文件名后缀
 * @return void
 */
function clear_all_files($ext = '')
{
    return clear_tpl_files(false, $ext) + clear_tpl_files(true, $ext);
}

/**
 * 页面上调用的js文件
 *
 * @access public
 * @param string $files
 * @return void
 */
function smarty_insert_scripts($args)
{
    static $scripts = array();
    
    $arr = explode(',', str_replace(' ', '', $args['files']));
    
    $str = '';
    foreach ($arr as $val) {
        if (in_array($val, $scripts) == false) {
            $scripts[] = $val;
            if ($val{0} == '.') {
                $str .= '<script type="text/javascript" src="' . $val . '"></script>';
            } else {
                $str .= '<script type="text/javascript" src="js/' . $val . '"></script>';
            }
        }
    }
    
    return $str;
}

/**
 * 创建分页的列表
 *
 * @access public
 * @param integer $count
 * @return string
 */
function smarty_create_pages($params)
{
    extract($params);
    
    $str = '';
    $len = 10;
    
    if (empty($page)) {
        $page = 1;
    }
    
    if (! empty($count)) {
        $step = 1;
        $str .= "<option value='1'>1</option>";
        
        for ($i = 2; $i < $count; $i += $step) {
            $step = ($i >= $page + $len - 1 || $i <= $page - $len + 1) ? $len : 1;
            $str .= "<option value='$i'";
            $str .= $page == $i ? " selected='true'" : '';
            $str .= ">$i</option>";
        }
        
        if ($count > 1) {
            $str .= "<option value='$count'";
            $str .= $page == $count ? " selected='true'" : '';
            $str .= ">$count</option>";
        }
    }
    
    return $str;
}

/**
 * 重写 URL 地址
 *
 * @access public
 * @param string $app 执行程序
 * @param array $params 参数数组
 * @param string $append 附加字串
 * @param integer $page 页数
 * @param string $keywords 搜索关键词字符串
 * @return void
 */
function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
{
    static $rewrite = NULL;
    
    if ($rewrite === NULL) {
        $rewrite = intval($GLOBALS['_CFG']['rewrite']);
    }
    
    $args = array(
        'cid' => 0,
        'gid' => 0,
        'bid' => 0,
        'acid' => 0,
        'aid' => 0,
        'sid' => 0,
        'gbid' => 0,
        'auid' => 0,
        'sort' => '',
        'order' => ''
    );
    
    extract(array_merge($args, $params));
    
    $uri = '';
    switch ($app) {
        case 'category':
            if (empty($cid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'category-' . $cid;
                    if (isset($bid)) {
                        $uri .= '-b' . $bid;
                    }
                    if (isset($price_min)) {
                        $uri .= '-min' . $price_min;
                    }
                    if (isset($price_max)) {
                        $uri .= '-max' . $price_max;
                    }
                    if (isset($filter_attr)) {
                        $uri .= '-attr' . $filter_attr;
                    }
                    if (! empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (! empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (! empty($order)) {
                        $uri .= '-' . $order;
                    }
                } else {
                    $uri = 'category.php?id=' . $cid;
                    if (! empty($bid)) {
                        $uri .= '&amp;brand=' . $bid;
                    }
                    if (isset($price_min)) {
                        $uri .= '&amp;price_min=' . $price_min;
                    }
                    if (isset($price_max)) {
                        $uri .= '&amp;price_max=' . $price_max;
                    }
                    if (! empty($filter_attr)) {
                        $uri .= '&amp;filter_attr=' . $filter_attr;
                    }
                    
                    if (! empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (! empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (! empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }
            
            break;
        case 'goods':
            if (empty($gid)) {
                return false;
            } else {
                $uri = $rewrite ? 'goods-' . $gid : 'goods.php?id=' . $gid;
            }
            
            break;
        case 'brand':
            if (empty($bid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'brand-' . $bid;
                    if (isset($cid)) {
                        $uri .= '-c' . $cid;
                    }
                    if (! empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (! empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (! empty($order)) {
                        $uri .= '-' . $order;
                    }
                } else {
                    $uri = 'brand.php?id=' . $bid;
                    if (! empty($cid)) {
                        $uri .= '&amp;cat=' . $cid;
                    }
                    if (! empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (! empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (! empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }
            
            break;
        case 'article_cat':
            if (empty($acid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'article_cat-' . $acid;
                    if (! empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (! empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (! empty($order)) {
                        $uri .= '-' . $order;
                    }
                    if (! empty($keywords)) {
                        $uri .= '-' . $keywords;
                    }
                } else {
                    $uri = 'article_cat.php?id=' . $acid;
                    if (! empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (! empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (! empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                    if (! empty($keywords)) {
                        $uri .= '&amp;keywords=' . $keywords;
                    }
                }
            }
            
            break;
        case 'article':
            if (empty($aid)) {
                return false;
            } else {
                $uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
            }
            
            break;
        case 'group_buy':
            if (empty($gbid)) {
                return false;
            } else {
                $uri = $rewrite ? 'group_buy-' . $gbid : 'group_buy.php?act=view&amp;id=' . $gbid;
            }
            
            break;
        case 'auction':
            if (empty($auid)) {
                return false;
            } else {
                $uri = $rewrite ? 'auction-' . $auid : 'auction.php?act=view&amp;id=' . $auid;
            }
            
            break;
        case 'snatch':
            if (empty($sid)) {
                return false;
            } else {
                $uri = $rewrite ? 'snatch-' . $sid : 'snatch.php?id=' . $sid;
            }
            
            break;
        case 'search':
            break;
        case 'exchange':
            if ($rewrite) {
                $uri = 'exchange-' . $cid;
                if (isset($price_min)) {
                    $uri .= '-min' . $price_min;
                }
                if (isset($price_max)) {
                    $uri .= '-max' . $price_max;
                }
                if (! empty($page)) {
                    $uri .= '-' . $page;
                }
                if (! empty($sort)) {
                    $uri .= '-' . $sort;
                }
                if (! empty($order)) {
                    $uri .= '-' . $order;
                }
            } else {
                $uri = 'exchange.php?cat_id=' . $cid;
                if (isset($price_min)) {
                    $uri .= '&amp;integral_min=' . $price_min;
                }
                if (isset($price_max)) {
                    $uri .= '&amp;integral_max=' . $price_max;
                }
                
                if (! empty($page)) {
                    $uri .= '&amp;page=' . $page;
                }
                if (! empty($sort)) {
                    $uri .= '&amp;sort=' . $sort;
                }
                if (! empty($order)) {
                    $uri .= '&amp;order=' . $order;
                }
            }
            
            break;
        case 'exchange_goods':
            if (empty($gid)) {
                return false;
            } else {
                $uri = $rewrite ? 'exchange-id' . $gid : 'exchange.php?id=' . $gid . '&amp;act=view';
            }
            
            break;
        default:
            return false;
            break;
    }
    
    if ($rewrite) {
        if ($rewrite == 2 && ! empty($append)) {
            $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
        }
        
        $uri .= '.html';
    }
    if (($rewrite == 2) && (strpos(strtolower(EC_CHARSET), 'utf') !== 0)) {
        $uri = urlencode($uri);
    }
    return $uri;
}

/**
 * 格式化重量：小于1千克用克表示，否则用千克表示
 * 
 * @param float $weight 重量
 * @return string 格式化后的重量
 */
function formated_weight($weight)
{
    $weight = round(floatval($weight), 3);
    if ($weight > 0) {
        if ($weight < 1) {
            /* 小于1千克，用克表示 */
            return intval($weight * 1000) . $GLOBALS['_LANG']['gram'];
        } else {
            /* 大于1千克，用千克表示 */
            return $weight . $GLOBALS['_LANG']['kilogram'];
        }
    } else {
        return 0;
    }
}

/**
 * 记录帐户变动
 * 
 * @param int $user_id 用户id
 * @param float $user_money 可用余额变动
 * @param float $frozen_money 冻结余额变动
 * @param int $rank_points 等级积分变动
 * @param int $pay_points 消费积分变动
 * @param string $change_desc 变动说明
 * @param int $change_type 变动类型：参见常量文件
 * @return void
 */
function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER)
{
    /* 插入帐户变动记录 */
    $account_log = array(
        'user_id' => $user_id,
        'user_money' => $user_money,
        'frozen_money' => $frozen_money,
        'rank_points' => $rank_points,
        'pay_points' => $pay_points,
        'change_time' => gmtime(),
        'change_desc' => $change_desc,
        'change_type' => $change_type
    );
    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');
    
    /* 更新用户信息 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('users') . " SET user_money = user_money + ('$user_money')," . " frozen_money = frozen_money + ('$frozen_money')," . " rank_points = rank_points + ('$rank_points')," . " pay_points = pay_points + ('$pay_points')" . " WHERE user_id = '$user_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access public
 * @param int $cat_id 分类的ID
 * @param int $selected 当前选中分类的ID
 * @param boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param int $level 限定返回的级数。为0时返回所有级数
 * @return mix
 */
function article_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
    $sql = "SELECT c.*, COUNT(s.cat_id) AS has_children, COUNT(a.article_id) AS aricle_num " . ' FROM {PRE}article_cat AS c' . " LEFT JOIN {PRE}article_cat AS s ON s.parent_id=c.cat_id" . " LEFT JOIN {PRE}article AS a ON a.cat_id=c.cat_id" . " GROUP BY c.cat_id " . " ORDER BY parent_id, sort_order ASC";
    $res = M()->cache(true)->query($sql);
    
    $options = article_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组
    
    /* 截取到指定的缩减级别 */
    if ($level > 0) {
        if ($cat_id == 0) {
            $end_level = $level;
        } else {
            $first_item = reset($options); // 获取第一个元素
            $end_level = $first_item['level'] + $level;
        }
        
        /* 保留level小于end_level的部分 */
        foreach ($options as $key => $val) {
            if ($val['level'] >= $end_level) {
                unset($options[$key]);
            }
        }
    }
    
    $pre_key = 0;
    foreach ($options as $key => $value) {
        $options[$key]['has_children'] = 1;
        if ($pre_key > 0) {
            if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id']) {
                $options[$pre_key]['has_children'] = 1;
            }
        }
        $pre_key = $key;
    }
    
    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ' cat_type="' . $var['cat_type'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name'])) . '</option>';
        }
        
        return $select;
    } else {
        foreach ($options as $key => $value) {
            $options[$key]['url'] = build_uri('article_cat', array(
                'acid' => $value['cat_id']
            ), $value['cat_name']);
        }
        return $options;
    }
}

/**
 * 过滤和排序所有文章分类，返回一个带有缩进级别的数组
 *
 * @access private
 * @param int $cat_id 上级分类ID
 * @param array $arr 含有所有分类的数组
 * @param int $level 级别
 * @return void
 */
function article_cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();
    
    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }
    
    if (! isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
        while (! empty($arr)) {
            foreach ($arr as $key => $value) {
                $cat_id = $value['cat_id'];
                if ($level == 0 && $last_cat_id == 0) {
                    if ($value['parent_id'] > 0) {
                        break;
                    }
                    
                    $options[$cat_id] = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id'] = $cat_id;
                    $options[$cat_id]['name'] = $value['cat_name'];
                    unset($arr[$key]);
                    
                    if ($value['has_children'] == 0) {
                        continue;
                    }
                    $last_cat_id = $cat_id;
                    $cat_id_array = array(
                        $cat_id
                    );
                    $level_array[$last_cat_id] = ++ $level;
                    continue;
                }
                
                if ($value['parent_id'] == $last_cat_id) {
                    $options[$cat_id] = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id'] = $cat_id;
                    $options[$cat_id]['name'] = $value['cat_name'];
                    unset($arr[$key]);
                    
                    if ($value['has_children'] > 0) {
                        if (end($cat_id_array) != $last_cat_id) {
                            $cat_id_array[] = $last_cat_id;
                        }
                        $last_cat_id = $cat_id;
                        $cat_id_array[] = $cat_id;
                        $level_array[$last_cat_id] = ++ $level;
                    }
                } elseif ($value['parent_id'] > $last_cat_id) {
                    break;
                }
            }
            
            $count = count($cat_id_array);
            if ($count > 1) {
                $last_cat_id = array_pop($cat_id_array);
            } elseif ($count == 1) {
                if ($last_cat_id != end($cat_id_array)) {
                    $last_cat_id = end($cat_id_array);
                } else {
                    $level = 0;
                    $last_cat_id = 0;
                    $cat_id_array = array();
                    continue;
                }
            }
            
            if ($last_cat_id && isset($level_array[$last_cat_id])) {
                $level = $level_array[$last_cat_id];
            } else {
                $level = 0;
            }
        }
        $cat_options[0] = $options;
    } else {
        $options = $cat_options[0];
    }
    
    if (! $spec_cat_id) {
        return $options;
    } else {
        if (empty($options[$spec_cat_id])) {
            return array();
        }
        
        $spec_cat_id_level = $options[$spec_cat_id]['level'];
        
        foreach ($options as $key => $value) {
            if ($key != $spec_cat_id) {
                unset($options[$key]);
            } else {
                break;
            }
        }
        
        $spec_cat_id_array = array();
        foreach ($options as $key => $value) {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) || ($spec_cat_id_level > $value['level'])) {
                break;
            } else {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;
        
        return $spec_cat_id_array;
    }
}

/**
 * 调用UCenter的函数
 *
 * @param string $func
 * @param array $params
 *
 * @return mixed
 */
function uc_call($func, $params = null)
{
    restore_error_handler();
    if (! function_exists($func)) {
        include_once (ROOT_PATH . 'uc_client/client.php');
    }
    
    $res = call_user_func_array($func, $params);
    
    set_error_handler('exception_handler');
    
    return $res;
}

/**
 * error_handle回调函数
 *
 * @return
 *
 */
function exception_handler($errno, $errstr, $errfile, $errline)
{
    return;
}

/**
 * 重新获得商品图片与商品相册的地址
 *
 * @param int $goods_id 商品ID
 * @param string $image 原商品相册图片地址
 * @param boolean $thumb 是否为缩略图
 * @param string $call 调用方法(商品图片还是商品相册)
 * @param boolean $del 是否删除图片
 *
 * @return string $url
 */
function get_image_path($goods_id, $image = '', $thumb = false, $call = 'goods', $del = false)
{
    $url = empty($image) ? $GLOBALS['_CFG']['no_picture'] : $image;
    return $url;
}

/**
 * 调用使用UCenter插件时的函数
 *
 * @param string $func
 * @param array $params
 *
 * @return mixed
 */
function user_uc_call($func, $params = null)
{
    if (isset($GLOBALS['_CFG']['integrate_code']) && $GLOBALS['_CFG']['integrate_code'] == 'ucenter') {
        restore_error_handler();
        if (! function_exists($func)) {
            include_once (ROOT_PATH . 'includes/lib_uc.php');
        }
        
        $res = call_user_func_array($func, $params);
        
        set_error_handler('exception_handler');
        
        return $res;
    } else {
        return;
    }
}

/**
 * 取得商品优惠价格列表
 *
 * @param string $goods_id 商品编号
 * @param string $price_type 价格类别(0为全店优惠比率，1为商品优惠价格，2为分类优惠比率)
 *
 * @return 优惠价格列表
 */
function get_volume_price_list($goods_id, $price_type = '1')
{
    $volume_price = array();
    $temp_index = '0';
    
    $sql = "SELECT `volume_number` , `volume_price`" . " FROM " . $GLOBALS['ecs']->table('volume_price') . "" . " WHERE `goods_id` = '" . $goods_id . "' AND `price_type` = '" . $price_type . "'" . " ORDER BY `volume_number`";
    
    $res = $GLOBALS['db']->getAll($sql);
    
    foreach ($res as $k => $v) {
        $volume_price[$temp_index] = array();
        $volume_price[$temp_index]['number'] = $v['volume_number'];
        $volume_price[$temp_index]['price'] = $v['volume_price'];
        $volume_price[$temp_index]['format_price'] = price_format($v['volume_price']);
        $temp_index ++;
    }
    return $volume_price;
}

/**
 * 取得商品最终使用价格
 *
 * @param string $goods_id 商品编号
 * @param string $goods_num 购买数量
 * @param boolean $is_spec_price 是否加入规格价格
 * @param mix $spec 规格ID的数组或者逗号分隔的字符串
 *
 * @return 商品最终购买价格
 */
function get_final_price($goods_id, $goods_num = '1', $is_spec_price = false, $spec = array())
{
    $final_price = '0'; // 商品最终购买价格
    $volume_price = '0'; // 商品优惠价格
    $promote_price = '0'; // 商品促销价格
    $user_price = '0'; // 商品会员价格
                          
    // 取得商品优惠价格列表
    $price_list = get_volume_price_list($goods_id, '1');
    
    if (! empty($price_list)) {
        foreach ($price_list as $value) {
            if ($goods_num >= $value['number']) {
                $volume_price = $value['price'];
            }
        }
    }
    
    // 取得商品促销价格列表
    /* 取得商品信息 */
    $sql = "SELECT g.promote_price, g.promote_start_date, g.promote_end_date, " . "IFNULL(mp.user_price, g.shop_price * '" . $_SESSION['discount'] . "') AS shop_price " . " FROM " . $GLOBALS['ecs']->table('goods') . " AS g " . " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " . "ON mp.goods_id = g.goods_id AND mp.user_rank = '" . $_SESSION['user_rank'] . "' " . " WHERE g.goods_id = '" . $goods_id . "'" . " AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);
    
    /* 计算商品的促销价格 */
    if ($goods['promote_price'] > 0) {
        $promote_price = bargain_price($goods['promote_price'], $goods['promote_start_date'], $goods['promote_end_date']);
    } else {
        $promote_price = 0;
    }
    
    // 取得商品会员价格列表
    $user_price = $goods['shop_price'];
    
    // 比较商品的促销价格，会员价格，优惠价格
    if (empty($volume_price) && empty($promote_price)) {
        // 如果优惠价格，促销价格都为空则取会员价格
        $final_price = $user_price;
    } elseif (! empty($volume_price) && empty($promote_price)) {
        // 如果优惠价格为空时不参加这个比较。
        $final_price = min($volume_price, $user_price);
    } elseif (empty($volume_price) && ! empty($promote_price)) {
        // 如果促销价格为空时不参加这个比较。
        $final_price = min($promote_price, $user_price);
    } elseif (! empty($volume_price) && ! empty($promote_price)) {
        // 取促销价格，会员价格，优惠价格最小值
        $final_price = min($volume_price, $promote_price, $user_price);
    } else {
        $final_price = $user_price;
    }
    
    // 如果需要加入规格价格
    if ($is_spec_price) {
        if (! empty($spec)) {
            $spec_price = spec_price($spec);
            $final_price += $spec_price;
        }
    }
    
    // 返回商品最终购买价格
    return $final_price;
}

/**
 * 将 goods_attr_id 的序列按照 attr_id 重新排序
 *
 * 注意：非规格属性的id会被排除
 *
 * @access public
 * @param array $goods_attr_id_array 一维数组
 * @param string $sort 序号：asc|desc，默认为：asc
 *
 * @return string
 */
function sort_goods_attr_id_array($goods_attr_id_array, $sort = 'asc')
{
    if (empty($goods_attr_id_array)) {
        return $goods_attr_id_array;
    }
    
    // 重新排序
    $sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id
				FROM " . $GLOBALS['ecs']->table('attribute') . " AS a
				    LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v
         ON v.attr_id = a.attr_id
         AND a.attr_type = 1
				WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . "
				ORDER BY a.attr_id $sort";
    $row = $GLOBALS['db']->GetAll($sql);
    
    $return_arr = array();
    foreach ($row as $value) {
        $return_arr['sort'][] = $value['goods_attr_id'];
        
        $return_arr['row'][$value['goods_attr_id']] = $value;
    }
    
    return $return_arr;
}

/**
 *
 * 是否存在规格
 *
 * @access public
 * @param array $goods_attr_id_array 一维数组
 *
 * @return string
 */
function is_spec($goods_attr_id_array, $sort = 'asc')
{
    if (empty($goods_attr_id_array)) {
        return $goods_attr_id_array;
    }
    
    // 重新排序
    $sql = "SELECT a.attr_type, v.attr_value, v.goods_attr_id
	    FROM " . $GLOBALS['ecs']->table('attribute') . " AS a
	        LEFT JOIN " . $GLOBALS['ecs']->table('goods_attr') . " AS v
	            ON v.attr_id = a.attr_id
	            AND a.attr_type = 1
	            WHERE v.goods_attr_id " . db_create_in($goods_attr_id_array) . "
				ORDER BY a.attr_id $sort";
    $row = $GLOBALS['db']->GetAll($sql);
    
    $return_arr = array();
    foreach ($row as $value) {
        $return_arr['sort'][] = $value['goods_attr_id'];
        
        $return_arr['row'][$value['goods_attr_id']] = $value;
    }
    
    if (! empty($return_arr)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取指定id package 的信息
 *
 * @access public
 * @param int $id package_id
 *
 * @return array array(package_id, package_name, goods_id,start_time, end_time, min_price, integral)
 */
function get_package_info($id)
{
    global $ecs, $db, $_CFG;
    $id = is_numeric($id) ? intval($id) : 0;
    $now = gmtime();
    
    $sql = "SELECT act_id AS id,  act_name AS package_name, goods_id , goods_name, start_time, end_time, act_desc, ext_info" . " FROM " . $GLOBALS['ecs']->table('goods_activity') . " WHERE act_id='$id' AND act_type = " . GAT_PACKAGE;
    
    $package = $db->GetRow($sql);
    
    /* 将时间转成可阅读格式 */
    if ($package['start_time'] <= $now && $package['end_time'] >= $now) {
        $package['is_on_sale'] = "1";
    } else {
        $package['is_on_sale'] = "0";
    }
    $package['start_time'] = local_date('Y-m-d H:i', $package['start_time']);
    $package['end_time'] = local_date('Y-m-d H:i', $package['end_time']);
    $row = unserialize($package['ext_info']);
    unset($package['ext_info']);
    if ($row) {
        foreach ($row as $key => $val) {
            $package[$key] = $val;
        }
    }
    
    $sql = "SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, " . " g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, g.is_real, " . " IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS rank_price " . " FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg " . "   LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g " . "   ON g.goods_id = pg.goods_id " . " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " . " WHERE pg.package_id = " . $id . " " . " ORDER BY pg.package_id, pg.goods_id";
    
    $goods_res = $GLOBALS['db']->getAll($sql);
    
    $market_price = 0;
    $real_goods_count = 0;
    $virtual_goods_count = 0;
    
    foreach ($goods_res as $key => $val) {
        $goods_res[$key]['goods_thumb'] = get_image_path($val['goods_id'], $val['goods_thumb'], true);
        $goods_res[$key]['market_price_format'] = price_format($val['market_price']);
        $goods_res[$key]['rank_price_format'] = price_format($val['rank_price']);
        $market_price += $val['market_price'] * $val['goods_number'];
        /* 统计实体商品和虚拟商品的个数 */
        if ($val['is_real']) {
            $real_goods_count ++;
        } else {
            $virtual_goods_count ++;
        }
    }
    
    if ($real_goods_count > 0) {
        $package['is_real'] = 1;
    } else {
        $package['is_real'] = 0;
    }
    
    $package['goods_list'] = $goods_res;
    $package['market_package'] = $market_price;
    $package['market_package_format'] = price_format($market_price);
    $package['package_price_format'] = price_format($package['package_price']);
    
    return $package;
}

/**
 * 获得指定礼包的商品
 *
 * @access public
 * @param integer $package_id
 * @return array
 */
function get_package_goods($package_id)
{
    $sql = "SELECT pg.goods_id, g.goods_name, pg.goods_number, p.goods_attr, p.product_number, p.product_id
		    FROM {PRE}package_goods AS pg
		    LEFT JOIN {PRE}goods AS g ON pg.goods_id = g.goods_id
		    LEFT JOIN {PRE}products AS p ON pg.product_id = p.product_id
		    WHERE pg.package_id = '$package_id'";
    if ($package_id == 0) {
        $sql .= " AND pg.admin_id = '$_SESSION[admin_id]'";
    }
    $resource = M()->query($sql);
    
    $row = array();
    
    /* 生成结果数组 取存在货品的商品id 组合商品id与货品id */
    $good_product_str = '';
    foreach ($resource as $_row) {
        if ($_row['product_id'] > 0) {
            /* 取存商品id */
            $good_product_str .= ',' . $_row['goods_id'];
            
            /* 组合商品id与货品id */
            $_row['g_p'] = $_row['goods_id'] . '_' . $_row['product_id'];
        } else {
            /* 组合商品id与货品id */
            $_row['g_p'] = $_row['goods_id'];
        }
        
        // 生成结果数组
        $row[] = $_row;
    }
    $good_product_str = trim($good_product_str, ',');
    
    /* 释放空间 */
    unset($resource, $_row, $sql);
    
    /* 取商品属性 */
    if ($good_product_str != '') {
        $sql = "SELECT goods_attr_id, attr_value FROM {PRE}goods_attr WHERE goods_id IN ($good_product_str)";
        $result_goods_attr = M()->query($sql);
        
        $_goods_attr = array();
        foreach ($result_goods_attr as $value) {
            $_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
        }
    }
    
    /* 过滤货品 */
    $format[0] = '%s[%s]--[%d]';
    $format[1] = '%s--[%d]';
    foreach ($row as $key => $value) {
        if ($value['goods_attr'] != '') {
            $goods_attr_array = explode('|', $value['goods_attr']);
            
            $goods_attr = array();
            foreach ($goods_attr_array as $_attr) {
                $goods_attr[] = $_goods_attr[$_attr];
            }
            
            $row[$key]['goods_name'] = sprintf($format[0], $value['goods_name'], implode('，', $goods_attr), $value['goods_number']);
        } else {
            $row[$key]['goods_name'] = sprintf($format[1], $value['goods_name'], $value['goods_number']);
        }
    }
    
    return $row;
}

/**
 * 取商品的货品列表
 *
 * @param mixed $goods_id 单个商品id；多个商品id数组；以逗号分隔商品id字符串
 * @param string $conditions sql条件
 *
 * @return array
 */
function get_good_products($goods_id, $conditions = '')
{
    if (empty($goods_id)) {
        return array();
    }
    
    switch (gettype($goods_id)) {
        case 'integer':
            $_goods_id = "goods_id = '" . intval($goods_id) . "'";
            break;
        
        case 'string':
        case 'array':
            $_goods_id = db_create_in($goods_id, 'goods_id');
            break;
    }
    
    /* 取货品 */
    $sql = "SELECT * FROM {PRE}products WHERE $_goods_id $conditions";
    $result_products = M()->query($sql);
    
    /* 取商品属性 */
    $sql = "SELECT goods_attr_id, attr_value FROM {PRE}goods_attr WHERE $_goods_id";
    $result_goods_attr = M()->query($sql);
    
    $_goods_attr = array();
    foreach ($result_goods_attr as $value) {
        $_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
    }
    
    /* 过滤货品 */
    foreach ($result_products as $key => $value) {
        $goods_attr_array = explode('|', $value['goods_attr']);
        if (is_array($goods_attr_array)) {
            $goods_attr = array();
            foreach ($goods_attr_array as $_attr) {
                $goods_attr[] = $_goods_attr[$_attr];
            }
            
            $goods_attr_str = implode('，', $goods_attr);
        }
        
        $result_products[$key]['goods_attr_str'] = $goods_attr_str;
    }
    
    return $result_products;
}

/**
 * 取商品的下拉框Select列表
 *
 * @param int $goods_id 商品id
 *
 * @return array
 */
function get_good_products_select($goods_id)
{
    $return_array = array();
    $products = get_good_products($goods_id);
    
    if (empty($products)) {
        return $return_array;
    }
    
    foreach ($products as $value) {
        $return_array[$value['product_id']] = $value['goods_attr_str'];
    }
    
    return $return_array;
}

/**
 * 取商品的规格列表
 *
 * @param int $goods_id 商品id
 * @param string $conditions sql条件
 *
 * @return array
 */
function get_specifications_list($goods_id, $conditions = '')
{
    /* 取商品属性 */
    $sql = "SELECT ga.goods_attr_id, ga.attr_id, ga.attr_value, a.attr_name
				FROM {PRE}goods_attr AS ga, {PRE}attribute AS a
				WHERE ga.attr_id = a.attr_id
				AND ga.goods_id = '$goods_id'
				$conditions";
    $result = M()->query($sql);
    
    $return_array = array();
    foreach ($result as $value) {
        $return_array[$value['goods_attr_id']] = $value;
    }
    
    return $return_array;
}

/**
 * *****************************************
 * 加密、解密函数
 * *****************************************
 */

/**
 * 加密函数
 * 
 * @param string $str 加密前的字符串
 * @param string $key 密钥
 * @return string 加密后的字符串
 */
function encrypt($str, $key = AUTH_KEY)
{
    $coded = '';
    $keylength = strlen($key);
    
    for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength) {
        $coded .= substr($str, $i, $keylength) ^ $key;
    }
    
    return str_replace('=', '', base64_encode($coded));
}

/**
 * 解密函数
 * 
 * @param string $str 加密后的字符串
 * @param string $key 密钥
 * @return string 加密前的字符串
 */
function decrypt($str, $key = AUTH_KEY)
{
    $coded = '';
    $keylength = strlen($key);
    $str = base64_decode($str);
    
    for ($i = 0, $count = strlen($str); $i < $count; $i += $keylength) {
        $coded .= substr($str, $i, $keylength) ^ $key;
    }
    
    return $coded;
}