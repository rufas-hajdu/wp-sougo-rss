<?php
define('SOUGORSS_DIR', __DIR__);
define('SOUGORSS_CPT', 'sougorss');
define( 'SOUGORSS_URL', plugins_url( 'wp-sougo-rss' ) );

/**
 * Created by IntelliJ IDEA.
 * User: yousan
 * Date: 6/30/15
 * Time: 2:55 PM
 */
class SR_Admin
{

    function __construct() {
        // setup variables
        //define( 'CFS_VERSION', '2.4.3' );
        //define( 'CFS_DIR', dirname( __FILE__ ) );
        //define( 'CFS_URL', plugins_url( 'custom-field-suite' ) );

        add_action( 'init', array( $this, 'init' ) );
        add_action('init', array($this, 'wp_sougo_rss_init'));
    }

    function init()
    {
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        //add_action('admin_menu', array($this, 'admin_menu'));
        add_action('save_post', array($this, 'save_post'));
        add_action('delete_post', array($this, 'delete_post'));
        add_action('admin_init', array($this, 'load_scripts'));
        add_action('add_meta_boxes', array($this, 'remove_all_metaboxes'), 99, 2);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 100);
        add_action('page_row_actions', array($this, 'sougorss_edit_form_after_title')); // 一覧画面で
    }

    function sougorss_edit_form_after_title($posts){
        global $post;
        if (get_post_type($post) == 'sougorss') {
            foreach($posts as $key => $value){
                if (!($key == 'edit' || $key == 'trash')) {
                    unset ($posts[$key]);
                }
            }
        }
        return $posts;
    }




    public function wp_sougo_rss_init(){
        $labels = array(
            'name' => __( '相互RSS', 'sougorss' ),
            'singular_name' => __( 'Advanced Custom Fields', 'sougorss' ),
            'add_new' => __( 'Add New' , 'sougorss' ),
            'add_new_item' => __( 'Add New 相互RSS' , 'sougorss' ),
            'edit_item' =>  __( 'Edit 相互RSS' , 'sougorss' ),
            'new_item' => __( 'New  相互RSS' , 'sougorss' ),
            'view_item' => __('View  相互RSS', 'sougorss'),
            'search_items' => __('Search Field Groups', 'sougorss'),
            'not_found' =>  __('ありませんでした', 'sougorss'),
            'not_found_in_trash' => __('No Field Groups found in Trash', 'sougorss'),
        );
        register_post_type("sougorss", array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            '_builtin' =>  false,
            'capability_type' => 'page',
            'hierarchical' => true,
            'rewrite' => false,
            'query_var' => "sougorss",
            'supports' => array(
                'title',
            ),
            'show_in_menu'	=> true,
        ));
    }

    public function delete_post($post_id) {

    }

    /**
     * 記事の保存
     */
    public function save_post($post_id) {
        $this->storeRssFields($post_id);
    }



    /**
     *  POSTのデータを整形してRssFieldの形に直す
     */
    private function storeRssFields($post_id){
        $rssField = new SR_RssField();
        foreach ((array)$_POST['save_field'] as $postdata) { // 0, 1, 2, 3, 4, ...
            $rssFieldOne = new SR_RssFieldOne();
            foreach ($rssFieldOne as $key => $value) { // url, icon, start, ...
                $rssFieldOne->$key = $postdata[$key];
            }
            $rssField->rssFieldOnes[] = $rssFieldOne;
        }
        foreach ($rssField as $key => $value) {

            switch ($key) {
                case 'rssFieldOnes':
                    break;
                default:
                    $rssField->$key = @$_POST['save_'.$key];
                    break;
            }
        }
        $rssField->ID = $post_id;
        $this->storeRssField($rssField);
    }

    /**
     * 実際にデータベースに保管するところ
     */
    private function storeRssField(SR_RssField $rssField) {
        global $current_user; /** @var WP_User */
//        $data = array(
//            'ID'           => $rssField->ID,
//            'post_name'    => $rssField->title,			// スラッグ
//            'post_author'  =>  $current_user->ID,			// 投稿者のID
//            'post_date'    => date_i18n('Y-m-d H:i:s') ,	// 投稿時刻
//            'post_type'    => SOUGORSS_CPT ,			// 投稿タイプ（カスタム投稿タイプも指定できるよ）
//            'post_status'  => 'publish' ,		// publish (公開) とか、draft (下書き) とか
//            'post_title'   => $rssField->title,		// 投稿のタイトル
//            'post_content' => '' ,			// 投稿の本文
//            //'post_category'=> array(1, 2) ,		// カテゴリーID を配列で
//            //'post_tags'    => array('タグ1', 'タグ2') ,	// 投稿のタグを配列で
//        );
        if (is_numeric($rssField->ID)) { // update
            //$posts = new wp_post_helper($rssField->ID); // get post data;
            //$posts->set($data);
            //$ID = $posts->update();
        } else { // insert
            //$posts = new wp_post_helper($data);
            //$ID = $posts->insert();
        }
        //wp_update_post($data);
        foreach ($rssField as $key => $value) { // upate custom fields
            switch ($key) {
                case 'ID':
                case 'title':
                    break;
                default:;
                    delete_post_meta($rssField->ID, $key);
                    add_post_meta($rssField->ID, $key, $value);
                    break;
            }
        }
    }

    /**
     * @return SR_Rssfield[]
     */
    private function getSavedRssFields($id) {
        global $post;
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'sougorss',
            'posts_per_page' => -1,
            'p' => $id,
        );
        $saved_setting = new SR_RssField;
        $query = new WP_Query($args);
        while($query->have_posts()) {
            $query->the_post();
            foreach($saved_setting as $key => $value){
                switch ($key){
                    case 'ID':
                        $saved_setting->$key = $id;
                        break;
                    case 'title':
                        $saved_setting->$key = get_the_title();
                        break;
                    case 'rssFieldOnes':
                        $saved_setting->$key = get_post_meta($post->ID,$key,false);
                        break;
                    default:
                        $saved_setting->$key = get_post_meta($post->ID,$key,true);
                        break;
                }
            }
        }
        wp_reset_postdata();
        return $saved_setting;
    }



    /**
     * jsを読ませる
     */
    public function load_scripts() {
        wp_enqueue_script( 'sougorss', SOUGORSS_URL . '/js/sougorss.js', array( 'jquery' ));
    }

    function remove_all_metaboxes() {
        global $wp_meta_boxes;
        /** Simply unset all of the metaboxes, no checking */
        unset($wp_meta_boxes[SOUGORSS_CPT]['normal']);
        unset($wp_meta_boxes[SOUGORSS_CPT]['advanced']);
    }

    /**
     * admin_head
     * @since 1.0.0
     */
    function admin_head() {
        $screen = get_current_screen();

        if ( is_object( $screen ) && 'post' == $screen->base ) {
            include( SOUGORSS_DIR . '/templates/admin_head.php' );
        }
    }

    /**
     * admin_footer
     * @since 1.0.0
     */
    function admin_footer() {
        $screen = get_current_screen();
        if ( 'edit' == $screen->base && 'sougorss' == $screen->post_type ) {
            include( SOUGORSS_DIR . '/templates/admin_footer.php' );
        }
    }


    /**
     * add_meta_boxes
     * @since 1.0.0
     */
    function add_meta_boxes() {
        /** @var WP_Post $post */
        global $post, $rssFields;
        $rssFields = $this->getSavedRssFields($post->ID);
        add_meta_box( 'sougorss_rsses', __('外部RSS', 'sougorss'), array( $this, 'meta_box' ), 'sougorss', 'normal', 'high', array( 'box' => 'rsses' ) );
        add_meta_box( 'sougorss_viewsetting', __('表示設定', 'sougorss'), array( $this, 'meta_box' ), 'sougorss', 'normal', 'high', array( 'box' => 'viewsetting' ) );
    }


    /**
     * meta_box
     * @param object $post
     * @param array $metabox
     * @since 1.0.0
     */
    function meta_box( $post, $metabox ) {
        $box = $metabox['args']['box'];
        include( SOUGORSS_DIR . "/templates/meta_box_$box.php" );
    }

}

$srAdmin = new SR_Admin();
