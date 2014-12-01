<?php

$database = require DATA_PATH . 'config.php';
$conf = array(
    'SHOW_PAGE_TRACE'=>true,
    'TMPL_FILE_DEPR' => '_',
    'APP_AUTOLOAD_PATH' => 'touch.vendor',
    'LOAD_BASE_EXT_FILE' => 'constant,lib_time,lib_base,lib_common'
);

return array_merge($database, $conf);
