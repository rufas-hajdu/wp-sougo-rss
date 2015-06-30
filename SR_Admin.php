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
    }

    function init()
    {
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        //add_action('admin_menu', array($this, 'admin_menu'));
        //add_action('save_post', array($this, 'save_post'));
        //add_action('delete_post', array($this, 'delete_post'));
        add_action('admin_init', array($this, 'load_scripts'));
        add_action('add_meta_boxes', array($this, 'remove_all_metaboxes'), 99, 2);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 100);

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
