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
add_filter( 'manage_posts_columns', 'wp_sougo_rss_change_posts_columns',1000);
add_action( 'manage_pages_custom_column', 'wp_sougo_rss_change_column', 1000, 2 );
?>