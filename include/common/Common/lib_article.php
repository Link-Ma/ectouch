<?php

/**
 * 文章及文章分类相关函数库
*/

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

/**
 * 获得文章分类下的文章列表
 *
 * @access public
 * @param integer $cat_id            
 * @param integer $page            
 * @param integer $size            
 *
 * @return array
 */
function get_cat_articles($cat_id, $page = 1, $size = 20, $requirement = '')
{
    // 取出所有非0的文章
    if ($cat_id == '-1') {
        $cat_str = 'cat_id > 0';
    } else {
        $cat_str = get_article_children($cat_id);
    }
    
    $sql = 'SELECT article_id, title, author, add_time, file_url, open_type ' . ' FROM {pre}article WHERE is_open = 1';
    // 增加搜索条件，如果有搜索内容就进行搜索
    if ($requirement != '') {
        $sql .= ' AND title like \'%' . $requirement . '%\' ';
    } else {
        
        $sql .= ' AND ' . $cat_str;
    }
    $sql .= ' ORDER BY article_type DESC, article_id DESC limit ' . ($page - 1) * $size . ',' . $size;
    
    $res = M()->query($sql);
    
    $arr = array();
    foreach ($res as $row) {
        $article_id = $row['article_id'];
        $arr[$article_id]['id'] = $article_id;
        $arr[$article_id]['title'] = $row['title'];
        $arr[$article_id]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$article_id]['author'] = empty($row['author']) || $row['author'] == '_SHOPHELP' ? $GLOBALS['_CFG']['shop_name'] : $row['author'];
        $arr[$article_id]['url'] = $row['open_type'] != 1 ? build_uri('article', array(
            'aid' => $article_id
        ), $row['title']) : trim($row['file_url']);
        $arr[$article_id]['add_time'] = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
    }
    return $arr;
}

/**
 * 获得指定分类下的文章总数
 *
 * @param integer $cat_id            
 *
 * @return integer
 */
function get_article_count($cat_id, $requirement = '')
{
    $sql = 'SELECT COUNT(*) as count FROM {pre}article WHERE ' . get_article_children($cat_id) . ' AND is_open = 1';
    if ($requirement != '') {
        $sql .= ' AND  title like \'%' . $requirement . '%\'';
    }
    $res = M()->query($sql);
    return $res[0]['count'];
}
