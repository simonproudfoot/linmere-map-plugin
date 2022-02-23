<?php
/*
Plugin Name: Interactive map
Plugin URI: 
Description: Inteactive map plugin for Linmere
Version: 0.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: baseplugin
Domain Path: /languages
*/

/**
 * Copyright (c) YEAR Your Name (email: Email). All rights reserved.
 *
 */

// don't call the file directly
if (!defined('ABSPATH')) exit;




// if( function_exists('acf_add_options_page') ) {

// 	acf_add_options_page(array(
// 		'page_title' 	=> 'Map content',
// 		'menu_title'	=> 'Map content',
// 		'menu_slug' 	=> 'map-content',
//         'post_id' => 'map-content',
// 		'capability'	=> 'edit_posts',
// 		'redirect'		=> false
// 	));

// }


function acf_to_rest_api($response, $post, $request)
{
    if (!function_exists('get_fields')) return $response;

    if (isset($post)) {
        $acf = get_fields($post->id);
        $response->data['acf'] = $acf;
    }
    return $response;
}
add_filter('rest_prepare_post', 'acf_to_rest_api', 10, 3);



/**
 * Base_Plugin class
 *
 * @class Base_Plugin The class that holds the entire Base_Plugin plugin
 */
final class Base_Plugin
{

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '0.1.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Constructor for the Base_Plugin class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */




    function add_kiosk_custom_page()
    {
        $post_title = "kiosk";
        $post_content = "";
        $post_status = "publish"; //publish, draft, etc
        $post_type = "page"; // or whatever post type desired

        /* Attempt to find post id by post name if it exists */
        $found_post_title = get_page_by_title($post_title, OBJECT, $post_type);
        $found_post_id = $found_post_title->ID;

        /**********************************************************
         ** Check If Page does not exist, if true, create a new post 
         ************************************************************/
        if (FALSE === get_post_status($found_post_id)) :

            $post_args = array(
                'post_title' => $post_title,
                'post_type' => $post_type,
                'post_content' => $post_content,
                'post_status'  => $post_status,
                //'post_author'  => get_current_user_id(),

                /* If you have meta fields to enter data into */
                'meta_input'   => array(
                    'meta_key1' => 'my value',
                    'meta_key2' => 'my other value',
                ),
            );


            /* Add a new wp post into db, return it's post id */
            $returned_post_id = wp_insert_post($post_args);

            /* Update page template only if using "page" as the post_type */
            update_post_meta($returned_post_id, '_wp_page_template', 'my-page-template.php');

            /* Add values into meta fields. Work with ACF CUSTOM FIELDS!! */
            $field_key = "My_Field_KEY";
            $value = "my custom value";
            update_field($field_key, $value, $returned_post_id);

            $field_key = "My_Other_Field_KEY";
            $value = "my other custom value";
            update_field($field_key, $value, $returned_post_id);

            /* Save a checkbox or select value */
            // $field_key = "My_Field_KEY";
            // $value = array("red", "blue", "yellow");
            // update_field( $field_key, $value, $returned_post_id );

            /* Save to a repeater field value */
            // $field_key = "My_Field_KEY";
            // $value = array(
            //   array(
            //     "ss_name" => "Foo",
            //     "ss_type" => "Bar"
            //   )
            // );
            // update_field( $field_key, $value, $returned_post_id );

            /* Echo a response! */
            echo "<span class='pg-new'><strong>" . $post_title . " Created!</strong></span><br>";
            echo "<a href='" . esc_url(get_permalink($returned_post_id)) . "' target='_Blank'>" . $post_title . "</a><p>";


        else :
            /***************************
             ** IF POST EXISTS, update it 
             ****************************/

            /* Update post */
            $update_post_args = array(
                'ID'           => $found_post_id,
                'post_title'   => $post_title,
                'post_content' => $post_content,
            );

            /* Update the post into the database */
            wp_update_post($update_post_args);

            /* Update values into meta fields */
            $field_key = "My_Field_KEY";
            $value = "my custom value";
            update_field($field_key, $value, $found_post_id);

            $field_key = "My_Other_Field_KEY";
            $value = "my other custom value";
            update_field($field_key, $value, $found_post_id);

            /* Echo a response! */
            echo "<span class='pg-update'><strong>" . $post_title . " Updated!</strong></span><br>";
            echo "<a href='" . esc_url(get_permalink($found_post_id)) . "' target='_Blank'>View</a> | <a href='post.php?post=" . $found_post_id . "&action=edit'>" . $post_title . "</a><p>";

        endif;
    }


    public function __construct()
    {

        $this->define_constants();

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_activation_hook(__FILE__, array($this, 'add_kiosk_custom_page'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init_plugin'));
    }

    /**
     * Initializes the Base_Plugin() class
     *
     * Checks for an existing Base_Plugin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new Base_Plugin();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->container)) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('BASEPLUGIN_VERSION', $this->version);
        define('BASEPLUGIN_FILE', __FILE__);
        define('BASEPLUGIN_PATH', dirname(BASEPLUGIN_FILE));
        define('BASEPLUGIN_INCLUDES', BASEPLUGIN_PATH . '/includes');
        define('BASEPLUGIN_URL', plugins_url('', BASEPLUGIN_FILE));
        define('BASEPLUGIN_ASSETS', BASEPLUGIN_URL . '/assets');
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {

        $installed = get_option('baseplugin_installed');

        if (!$installed) {
            update_option('baseplugin_installed', time());
        }

        update_option('baseplugin_version', BASEPLUGIN_VERSION);
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate()
    {
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {

        require_once BASEPLUGIN_INCLUDES . '/Assets.php';

        if ($this->is_request('admin')) {
            require_once BASEPLUGIN_INCLUDES . '/Admin.php';
        }

        if ($this->is_request('frontend')) {
            require_once BASEPLUGIN_INCLUDES . '/Frontend.php';
        }

        if ($this->is_request('ajax')) {
            // require_once BASEPLUGIN_INCLUDES . '/class-ajax.php';
        }

        require_once BASEPLUGIN_INCLUDES . '/Api.php';
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {

        add_action('init', array($this, 'init_classes'));

        // Localize our plugin
        add_action('init', array($this, 'localization_setup'));
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {

        // if ($this->is_request('admin')) {
        //     $this->container['admin'] = new App\Admin();
        // }

        if ($this->is_request('frontend')) {
            $this->container['frontend'] = new App\Frontend();
        }

        if ($this->is_request('ajax')) {
            // $this->container['ajax'] =  new App\Ajax();
        }

        $this->container['api'] = new App\Api();
        $this->container['assets'] = new App\Assets();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('baseplugin', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'rest':
                return defined('REST_REQUEST');

            case 'cron':
                return defined('DOING_CRON');

            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }
} // Base_Plugin

$baseplugin = Base_Plugin::init();
