<?php
/*
Plugin Name: Top Network Posts
Plugin URI: http://wordpress.org/extend/plugins/
Description: Show a list of most viewed articles or sites in a Wordpress network.
Author: ways
Author URI: http://ways.as
Version: 0.0.1
Text Domain: top-network-posts
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

function increaseArticleViewCount() {
    global $wp_query;
    $postID = $wp_query->post->ID;

    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }

    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    echo $count . "ape";
}

add_action('wp_head', 'increaseArticleViewCount');
