<?php

namespace App\Core;

use WP_Error;
use WP_Query;

trait WordPressFunc
{
    /**
     * 获取post
     *
     * @param array $args {
     *  参数
     *      @property int $numberposts=5 条数
     *      @property string $orderby=date 排序的字段
     *      @property int $category=0 分类ID
     *      @property string $order=desc 排序方式 asc | desc
     *      @property array $include=[] 包含的ID
     *      @property array $exclude=[] 排除的ID
     *      @property string $meta_key='' 附加参数
     *      @property string $meta_value='' 附加参数值
     *      @property string $post_type='post' 文章类型
     *      @property bool $suppress_filters=true 是否打开过滤器
     *      @property int $paged 当前页数
     *      @property int $p 当前ID
     *      @property string $s 搜索的字符串
     * }
     * @param bool $single 是否返回单条
     * @return \WP_Post|\WP_Post[]|array|null
     */
    protected function getPosts($args = [], $single = false)
    {
        if (!isset($args['numberposts'])) {
            $args['numberposts'] = -1;
        }

        $list = get_posts($args);
        if ($single) {
            $list = current($list);
        }

        return $list;
    }

    /**
     * 获取指定的文章
     *
     * @param int|\WP_Post $post_id 文章ID
     * @param int $output 返回的类型
     * @return \WP_Post|array|null
     */
    protected function getPost($post_id = 0, $post_type = 'post')
    {
        return $this->getPosts([
            'p' => $post_id,
            'numberposts' => 1,
            'post_type' => $post_type,
        ], true);
    }

    /**
     * 获取指定的文章
     *
     * @param integer|array $term_id 分类ID
     * @param string $taxonomy 分类法名字
     * @param array $option 扩展
     * @return \WP_Post[]|array
     */
    protected function getPostsByTaxonomy($term_id = 0, $taxonomy = 'category', $option = [])
    {
        $option['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'field'     => 'term_id',
                'terms' => $term_id
            ]
        ];
        return $this->getPosts($option);
    }


    /**
     * 获取指定用户信息
     *
     * @param int $user_id 用户ID
     * @return array|null
     */
    protected function getWpUser($user_id = 0)
    {
        $user = $this->getUsers([
            'include' => [$user_id]
        ]);

        if (empty($user)) {
            return null;
        }

        return $user[0];
    }

    /**
     * 获取用户列表
     * @param array $args {
     *  参数
     *      @param int $number=5 条数
     *      @param string $orderby=date 排序的字段
     *      @param string $order=desc 排序方式 asc | desc
     *      @param array $include=[] 包含的ID
     *      @param array $exclude=[] 排除的ID
     *      @param string $meta_key='' 附加参数
     *      @param string $meta_value='' 附加参数值
     *      @param int $paged 当前页数
     *      @param string $s 搜索的字符串
     * }
     * @return array|null
     */
    protected function getUsers($args = [])
    {
        $list = get_users($args);
        return $this->changeListData($list);
    }

    /**
     * 转换用户信息，隐藏掉不应该显示的部分
     *
     * @param array $user_list
     * @return array
     */
    private function changeListData($user_list = [])
    {
        $temp = [];
        foreach ($user_list as $user) {
            $temp[] = [
                'user_id' => $user->ID,
                'user_name' => $user->data->user_login,
                'user_nicename' => $user->data->user_nicename,
                'user_email' => $user->data->user_email,
                'display_name' => $user->data->display_name,
                'register_time' => $user->data->user_registered,
                'register_timestamp' => strtotime($user->data->user_registered),
                'user_role' => $user->roles,
                'user_head' =>  method_exists($this, 'generateUrl') ? $this->generateUrl('user_avatar', ['user_id' => $user->ID]) : '/api/user/avatar/' . $user->ID,
                'user_description' => get_user_meta($user->ID, 'description', true),
                'sex' => get_user_meta($user->ID, 'sex', true) ?? 0,
            ];
        }
        unset($user_list);
        return $temp;
    }

    /**
     * 添加评论
     *
     * @param int|string $post_id 文章ID
     * @param string $content 评论内容
     * @param integer $user_id 用户ID 默认当前用户
     * @param int $reply_id 回复的评论ID
     * @param array $commentData 补充内容
     * @return int|null
     */
    protected function addComment($post_id, $content, $user_id = 0, $reply_id = 0, $commentData = [])
    {
        if ($user_id == 0) {
            $user = $this->nowUser();
        } else {
            $user = get_user_by('ID', $user_id);
        }

        $commentData['comment_post_ID'] = $post_id;
        $commentData['comment_content'] = $content;
        $commentData['user_id'] = $user->ID;
        $commentData['comment_author_email'] = $user->data->email;

        if ($reply_id > 0) {
            $commentData['comment_parent'] = $reply_id;
        }

        return @wp_new_comment($commentData);
    }

    /**
     * 获取当前用户信息
     *
     * @return \WP_User
     */
    protected function nowUser()
    {
        return wp_get_current_user();
    }

    /**
     * 获取当前用户ID
     *
     * @return int
     */
    protected function nowUserId()
    {
        return get_current_user_id();
    }

    /**
     * 获取指定的评论
     *
     * @param integer|\WP_Commnet $comment
     * @param int $output
     * @return \WP_Commnet|array
     */
    protected function getComment($comment = 0, $output = OBJECT)
    {
        return get_comment($comment, $output);
    }

    /**
     * 设置User Mate数据
     *
     * @param int $userId 用户ID 设置0为当前用户
     * @param string $metaKey
     * @param mixed $metaValue
     * @return string|int|object|array|float|double|mixed|null
     */
    protected function setUserMate($userId, $metaKey = '', $metaValue = null)
    {
        $userId = $userId > 0 ? $userId : $this->nowUserId();
        $metaData = $this->getUserMate($userId, $metaKey);
        if ($metaValue != null) {
            if ($metaData  !== false) {
                return update_user_meta($userId, $metaKey, $metaValue);
            }
            return add_user_meta($userId, $metaKey, $metaValue);
        }
    }

    /**
     * 是否已登录
     *
     * @return boolean
     */
    protected function isLogin()
    {
        return is_user_logged_in();
    }

    /**
     * 是否为管理员
     *
     * @return boolean
     */
    protected function isAdmin()
    {
        return is_super_admin($this->nowUserId());
    }

    /**
     * 获取用户扩展数据
     *
     * @param integer $userId 设置0为当前用户
     * @param mixed $metaKey
     * @return string|int|object|array|float|double|mixed|null
     */
    protected function getUserMate($userId = 0, $metaKey = null)
    {
        $userId = $userId > 0 ? $userId : $this->nowUserId();
        return get_user_meta($userId, $metaKey, true) ?? null;
    }

    /**
     * 设置Post Mate数据
     *
     * @param int $postId 文章ID
     * @param string $metaKey
     * @param mixed $metaValue
     * @return string|int|object|array|float|double|mixed|null
     */
    protected function setPostMate($postId, $metaKey = '', $metaValue = null)
    {
        $metaData = $this->getPostMate($postId, $metaKey);
        if ($metaValue != null) {
            if ($metaData  !== false) {
                return update_post_meta($postId, $metaKey, $metaValue);
            }
            return add_post_meta($postId, $metaKey, $metaValue);
        }
    }

    /**
     * 获取文章扩展数据
     *
     * @param integer $postId 文章ID
     * @param mixed $metaKey
     * @return string|int|object|array|float|double|mixed|null
     */
    protected function getPostMate($postId = 0, $metaKey = '')
    {
        return get_post_meta($postId, $metaKey, true) ?? null;
    }

    /**
     * 设置Comment Mate数据
     *
     * @param int $commentId 评论ID
     * @param string $metaKey
     * @param mixed $metaValue
     * @return string|int|object|array|float|double|mixed|null
     */
    protected function setCommentMate($commentId, $metaKey = '', $metaValue = null)
    {
        $metaData = $this->getCommentMate($commentId, $metaKey);
        if ($metaValue != null) {
            if ($metaData  !== false) {
                return update_comment_meta($commentId, $metaKey, $metaValue);
            }
            return add_comment_meta($commentId, $metaKey, $metaValue);
        }
    }

    /**
     * 获取评论扩展数据
     *
     * @param integer $commentId 文章ID
     * @param mixed $metaKey
     * @return string|int|object|array|float|double|mixed|null
     */
    protected function getCommentMate($commentId = 0, $metaKey = '')
    {
        return get_comment_meta($commentId, $metaKey, true) ?? null;
    }

    /**
     * 获取系统参数值
     *
     * @param string $optionName 参数名
     * @return mixed|null
     */
    protected function getOption($optionName = '', $default = null)
    {
        return get_option($optionName, $default);
    }

    /**
     * 设置系统参数值
     *
     * @param string $optionName
     * @param string $optionValue
     * @return mixed|null
     */
    protected function setOption($optionName = '', $optionValue = null)
    {
        $notFound = 'NotFound' . $optionName;
        $option = $this->getOption($optionName, $notFound);

        if (!is_null($optionValue)) {
            if ($option != $notFound) {
                return update_option($optionName, $optionValue);
            }

            return add_option($optionName, $optionValue);
        }
    }

    /**
     * 是否是手机打开
     *
     * @return boolean
     */
    public function isMobile()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $is_mobile = false;
        } elseif (
            strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // Many mobile devices (all iPhone, iPad, etc.)
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
        ) {
            $is_mobile = true;
        } else {
            $is_mobile = false;
        }

        /**
         * Filters whether the request should be treated as coming from a mobile device or not.
         *
         * @since 4.9.0
         *
         * @param bool $is_mobile Whether the request is from a mobile device or not.
         */
        return $is_mobile;
    }

    /**
     * 登录
     *
     * @param string $userName 用户名
     * @param string $userPassword 用户密码
     * @return WP_Error|bool
     */
    protected function WpLogin($userName, $userPassword)
    {
        $user = wp_signon([
            'user_login' => $userName,
            'user_password' => $userPassword,
        ]);

        if (!is_wp_error($user)) {
            wp_set_current_user($user->ID);
            return 'ok';
        }

        return $user;
    }

    /**
     * 获取分类列表
     *
     * @param string $type 类型
     * @param array $option {
     *  附加属性
     *  @property int|array $object_ids id列表
     *  @property bool $count 是否返回文章数量
     *  @property string|int $parent 父级别ID
     *  @property string $orderby 排序方式
     *  @property string $order 排序
     *  @property bool $hide_empty 隐藏内容为空的分类
     *  @property array $include 包含的ID
     *  @property array $exclude 排除的ID
     *  @property array $exclude_tree 排除的树状ID列表
     *  @property string $search 搜索
     *  @property string|array $name__like 名字like
     *  @property string|array $slug 标志like
     * }
     * @return array|\WP_Term[]
     */
    protected function getCategories($type = 'category', $option = [])
    {

        if (is_array($type)) {
            $option = $type;
            $type = 'category';
        }

        $option['taxonomy'] = $type;
        $catData = get_terms($option);

        return $catData;
    }

    /**
     * 获取指定分类信息
     *
     * @param integer|\WP_Term $id
     * @param string $type
     * @return array|\WP_Term
     */
    protected function getCategory(int $id, $type = 'category', $object = OBJECT)
    {
        return get_term($id, $type, $object);
    }

    /**
     * 转换WP格式的文章内容到HTML内容
     *
     * @param string $content 文章内容
     * @return string
     */
    protected function parseContent($content = '')
    {
        return str_replace(']]>', ']]&gt;', apply_filters('the_content', $content));
    }

    /**
     * 获取文章数量
     *
     * @param array $postOption
     * @return int
     */
    protected function getPostsCount($postOption = [])
    {
        $postOption['post_status'] = 'publish';

        $post = new WP_Query($postOption);
        return $post->found_posts;
    }

    /**
     * 获取POST文章数量
     *
     * @param integer $cat_id
     * @param array $postOption
     * @return int
     */
    protected function getPostsCountByCatId(int $cat_id, $postOption = [])
    {
        $postOption['cat'] = $cat_id;
        return $this->getPostsCount($postOption);
    }

    /**
     * 获取指定分类法文章数量
     *
     * @param integer $cat_id
     * @param string $taxonomy
     * @param array $postOption
     * @return int
     */
    protected function getPostsCountByTaxonomy(int $cat_id, $taxonomy = 'category', $postOption = [])
    {
        $postOption['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'terms' => $cat_id
            ]
        ];

        return $this->getPostsCount($postOption);
    }
}
