<?php
/*
Plugin Name: Blog Network Stats
Plugin URI: http://wordpress.org/extend/plugins/
Description: Can display top blog posts and bloggers in widgets.
Author: ways
Author URI: http://ways.as
Version: 0.0.1
Text Domain: blog-network-stats
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // Needed for database structure initialization.
require_once('top-posts.php');
require_once('top-blogs.php');
define('DB_SUFFIX', 'tpn_analytics'); // Suffix for custom db table.



function increase_article_view_count() {
    global $wpdb;
    global $post;
    global $blog_id;
    $post_id        = $post->ID;
    $ip             = ip2long($_SERVER['REMOTE_ADDR']);
    $current_time   = current_time( 'mysql' );
    $table_name     = $wpdb->base_prefix . DB_SUFFIX;

    // Only register views for articles,
    // else just return without doing anything.
    if (!is_single()) { return; }

    // Check if user/ip already is registered today.
    if (is_view_registered($ip, $current_time, $post_id, $blog_id)) { return; }

    $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'blog_id' => $blog_id,
            'ip_address' => $ip,
            'date' => $current_time,
        )
    );
}


function get_article_view_count() {

}

function tnp_install() {
   global $wpdb;
   $table_name      = $wpdb->base_prefix . DB_SUFFIX;
   $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        blog_id mediumint(9) NOT NULL,
        ip_address int(10) UNSIGNED NOT NULL,
        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";

    dbDelta( $sql );
}

function is_view_registered($client_ip, $current_time, $post_id, $blog_id) {
    global $wpdb;
    $table_name = $wpdb->base_prefix . DB_SUFFIX;

    $result = $wpdb->get_results(
    	"
    	SELECT *
    	FROM $table_name
        WHERE blog_id = $blog_id
        AND post_id = $post_id
        AND ip_address = $client_ip
        AND DATE(date) = DATE(NOW())
    	"
    );

    // If result is empty, view hasn't been counted
    // for the given ip address, for the given day.
    if (count($result) > 0) { return true; }

    // View from given ip *not* registered this day.
    else { return false; }
}

// Make sure database is initialized with the right structure
// when it is activated.
register_activation_hook( __FILE__, 'tnp_install' );
// Attach plugin function to the head action.
add_action('wp_head', 'increase_article_view_count');
// Add widget functionality.
add_action( 'widgets_init', function() { register_widget( 'Top_Network_Posts' ); } );
add_action( 'widgets_init', function() { register_widget( 'Top_Network_Blogs' ); } );
