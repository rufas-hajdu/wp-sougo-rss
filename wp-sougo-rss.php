<?php
/*
Plugin Name: wp sougo rss
*/
//ini_set('display_errors', true);
require_once('SR_RssField.php');
require_once('SR_RssFieldOne.php');
require_once('WPSougoRssCore.php');

add_action('admin_menu', 'mt_add_pages');
function mt_add_pages() {
    add_menu_page('WP相互RSS', 'WP相互RSS', 8, __FILE__, 'wp_sougo_rss_main_page');
    add_submenu_page(__FILE__, 'WP相互RSS 追加ページ', 'add', 8, 'wp-sougo-rss-add-page', 'wp_sougo_rss_add_page');
}

function wp_sougo_rss_main_page() {
    $url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
	//ファイル名がindex.phpかindex.htmlならば
    //「/」を区切りとして$hogeに配列として格納
    $hoge = explode("/",$_SERVER["SCRIPT_NAME"]);
    //ファイル名以外を$hoge2に格納
    for($i=0; $i<(count($hoge)-1); $i++){
        $hoge2 .= $hoge[$i]."/";
    }
    $url = "http://".$_SERVER["HTTP_HOST"].$hoge2;
    $url = $url.'edit.php?post_type=sougorss';
    echo '<script type="text/javascript">location.href="'.$url.'"</script>';
    echo '<a href="'.$url.'">飛ばない方はこちら</a>';
}

function wp_sougo_rss_add_page() {
    include('wp-sougo-rss-add.php');
}

add_action('page_row_actions', 'sougorss_edit_form_after_title'); // 編集画面に下書きボタン
function sougorss_edit_form_after_title($posts){
    global $post;
    if (get_post_type($post) == 'sougorss') {
        $url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
        //ファイル名がindex.phpかindex.htmlならば
        //「/」を区切りとして$hogeに配列として格納
        $hoge = explode("/",$_SERVER["SCRIPT_NAME"]);
        //ファイル名以外を$hoge2に格納
        for($i=0; $i<(count($hoge)-1); $i++){
            $hoge2 .= $hoge[$i]."/";
        }
        $url = "http://".$_SERVER["HTTP_HOST"].$hoge2;
        $url = $url.'admin.php?page=wp-sougo-rss-add-page&id='. $post->ID;
        $posts['edit'] = '<span><a href="'.$url.'" target="_blank">編集</a></span>';
        unset ($posts['inline hide-if-no-js']);
    }
    return $posts;
}

add_action('init', 'wp_sougo_rss_init');
function wp_sougo_rss_init(){
    $labels = array(
        'name' => __( 'Field&nbsp;Groups', 'sougorss' ),
        'singular_name' => __( 'Advanced Custom Fields', 'sougorss' ),
        'add_new' => __( 'Add New' , 'sougorss' ),
        'add_new_item' => __( 'Add New Field Group' , 'sougorss' ),
        'edit_item' =>  __( 'Edit Field Group' , 'sougorss' ),
        'new_item' => __( 'New Field Group' , 'sougorss' ),
        'view_item' => __('View Field Group', 'sougorss'),
        'search_items' => __('Search Field Groups', 'sougorss'),
        'not_found' =>  __('No Field Groups found', 'sougorss'),
        'not_found_in_trash' => __('No Field Groups found in Trash', 'sougorss'),
    );
    register_post_type("sougorss", array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        '_builtin' =>  false,
        'capability_type' => 'page',
        'hierarchical' => true,
        'rewrite' => false,
        'query_var' => "sougorss",
        'supports' => array(
            'title',
        ),
        'show_in_menu'	=> false,
    ));
}

function wp_sougo_rss_shortcode($atts) {
    extract(shortcode_atts(array(
        'id' => '',
    ), $atts));
    if (is_numeric($id)){
        $rssFields = new WPSougoRssCore($id);
        return $rssFields->inset();;
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