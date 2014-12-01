<?php

/**
 * 文章及文章分类相关函数库
 */

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');

class LibArticle {

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
    public static function get_cat_articles($cat_id, $page = 1, $size = 20, $requirement = '')
    {
        // 取出所有非0的文章
        if ($cat_id == '-1') {
            $cat_str = 'cat_id > 0';
        } else {
            $cat_str = get_article_children($cat_id);
        }
        
        // 增加搜索条件，如果有搜索内容就进行搜索
        $condition = 'is_open = 1';
        if ($requirement != '') {
            $condition .= ' AND title like \'%' . $requirement . '%\'';
        } else {
            $condition .= ' AND ' . $cat_str;
        }
        
        $res = M('article')->field('article_id, title, author, add_time, file_url, open_type')->where($condition)->order('article_type DESC, article_id DESC')->limit(($page - 1) * $size, $size)->select();
    
        $arr = array();
        foreach ($res as $row){
            $article_id = $row['article_id'];
            $arr[$article_id]['id'] = $article_id;
            $arr[$article_id]['title'] = $row['title'];
            $arr[$article_id]['short_title'] = C('setting.article_title_length') > 0 ? sub_str($row['title'], C('setting.article_title_length')) : $row['title'];
            $arr[$article_id]['author'] = empty($row['author']) || $row['author'] == '_SHOPHELP' ? C('setting.shop_name') : $row['author'];
            $arr[$article_id]['url'] = $row['open_type'] != 1 ? U('article/detail', array('aid' => $article_id)) : trim($row['file_url']);
            $arr[$article_id]['add_time'] = date(C('setting.date_format'), $row['add_time']);
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
    public static function get_article_count($cat_id, $requirement = '')
    {
        $condition = get_article_children($cat_id) . " AND is_open = 1";
        if ($requirement != '') {
            $condition .= ' AND  title like \'%' . $requirement . '%\'';
        }
        return M('article')->where($condition)->count();
    }
}

