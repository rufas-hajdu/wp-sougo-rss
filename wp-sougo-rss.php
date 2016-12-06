<?php
/*
Plugin Name: wp sougo rss
*/
ini_set('display_errors', true);
require_once('SR_RssField.php');
require_once('SR_RssFieldOne.php');
require_once('WPSougoRssCore.php');
require_once('SR_Admin.php');



function wp_sougo_rss_shortcode($atts) {
    extract(shortcode_atts(array(
        'id' => '',
    ), $atts));
    if (is_numeric($id)){
        $rssFields = new WPSougoRssCore($id);
        return $rssFields->inset();
    }
    return '';
}
add_shortcode('wp_sougo_rss', 'wp_sougo_rss_shortcode');

function wp_sougo_rss_change_posts_columns($columns) {
    global $post;
    if (get_post_type($post) == 'sougorss') {
        foreach($columns as $key => $value) {
            switch($key){
                case 'cb':
                    break;
                case 'title':
                    break;
                case 'date':
                    break;
                default:
                    unset($columns[$key]);
                    break;
            }
        }
        $columns['shortcode'] = "ショートコード";
        return $columns;
    }
    return $columns;
}
function wp_sougo_rss_change_column($column_name, $post_id) {
    global $post;
    if (get_post_type($post) == 'sougorss'){
        if ($column_name == 'shortcode'){
            echo '[wp_sougo_rss id="'.$post_id.'"]';
        }
    }
}

function wp_sougo_rss_update_rss(){
    $args = array(
        'numberposts' => -1, //表示する記事の数
        'post_type' => 'sougorss' //投稿タイプ名
        // 条件を追加する場合はここに追記
    );
    $customPosts = get_posts($args);
    foreach($customPosts as $customPost){
        $rssFields = new WPSougoRssCore($customPost->ID);
        $rssFields->update_osrdata();
    }
    wp_reset_postdata();
}

function wp_sougo_rss_activation() {
    if ( ! wp_next_scheduled( 'wp_sougo_rss_event' ) ) {
        wp_schedule_event(time(), '60sec', 'wp_sougo_rss_event');
    }
}


function wp_sougo_rss_interval(){
    // 60秒毎を追加
    $schedules['60sec'] = array(
        'interval' => 60,
        'display' => 'every 60 seconds'
    );
    return $schedules;
}

add_filter( 'manage_posts_columns', 'wp_sougo_rss_change_posts_columns',1000);
add_action( 'manage_pages_custom_column', 'wp_sougo_rss_change_column', 1000, 2 );
add_action('wp', 'wp_sougo_rss_activation');
add_filter('cron_schedules', 'wp_sougo_rss_interval');
add_action( 'wp_sougo_rss_event', 'wp_sougo_rss_update_rss' );
?>