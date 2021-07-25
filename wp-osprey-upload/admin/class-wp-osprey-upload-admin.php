<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/admin
 */

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-purposes-list.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-images-list.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-archives-list.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/admin
 * @author     Your Name <email@example.com>
 */
class Wp_Osprey_Upload_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_filter('removable_query_args', array($this, 'add_removable_arg'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wp_Osprey_Upload_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wp_Osprey_Upload_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-osprey-upload-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wp_Osprey_Upload_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wp_Osprey_Upload_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-osprey-upload-admin.js', array( 'jquery' ), $this->version, false );

    }

    public function add_purpose() {

        global $wpdb;
        $purpose_id = $wpdb->insert( 
            $wpdb->prefix."osprey_purposes", 
            array( 
                'title' => $_POST['purpose_title'], 
                'description' =>$_POST['purpose_description'], 
                'enabled' =>  (isset($_POST['purpose_enabled'])) ? true :false
            ), 
            array( 
                '%s',
                '%s', 
                '%d', 
            ) 
        );

        header(200);
        wp_redirect(add_query_arg( array( 'purpose' => $_POST['purpose_title'], 'result' => true ) , admin_url('admin.php?page=osprey-upload-purposes')));
    }

    public function create_menu(){
        add_menu_page("Osprey Upload","Image Upload",'manage_options',"osprey-upload", array($this, 'main_admin'));
        add_submenu_page("osprey-upload","Manage Purposes","Purposes",'manage_options',"osprey-upload-purposes",array($this, 'purposes'));
        add_submenu_page("osprey-upload","Manage Images","Images",'manage_options',"osprey-upload-images",array($this, 'images'));
        add_submenu_page("osprey-upload","Manage Downloads","Downloads",'manage_options',"osprey-upload-archives",array($this, 'archives'));
        $this->images_obj = new Images_list();
        $this->purposes_obj = new Purposes_list();
        $this->archives_obj = new Archives_list();

    }

    public function purposes(){
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Manage Image Upload Purposes</h1>		
            <hr class="wp-header-end">
            <h2>Create new Purpose</h2>
            <form method="post"  action="<?php echo admin_url( 'admin-post.php' ); ?>">
                <table class="form-table" id="new-upload-purpose">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="input_id">Name</label></th>
                            <td><input name="purpose_title" type="text" id="input_id" value="purpose" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row">Enabled</th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span>checkbox</span>
                                    </legend>
                                    <label for="checkbox_id">
                                        <input name="purpose_enabled" type="checkbox" id="checkbox_id" value="true">
                                        Should this purpose be displayed?
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Description</th>
                            <td>
                                <textarea name="purpose_description" rows="5" cols="40" id="purpose_description" class="large-text code" spellcheck="true"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="submit" class="button button-primary" value="Add new purpose">
                <input type="hidden" name="action" value="add_purpose">
            </form>

            <br class="clear"/>
            <h2>List of Purposes</h2>   
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->purposes_obj->prepare_items();
                                $this->purposes_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    public function images(){
        add_thickbox(); 
        ?>
            <div class="wrap">
            <h1>Manage Images</h1>
            <div id="imgs-poststuff">
                <div id="imgs-post-body" class="metabox-holder columns-2">
                    <div id="imgs-post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->images_obj->prepare_items();
                                $this->images_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    public function archives(){
        ?>
            <div class="wrap">
            <h1>Manage Archives</h1>
            <div id="arch-poststuff">
                <div id="arch-post-body" class="metabox-holder columns-2">
                    <div id="arch-post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->archives_obj->prepare_items();
                                $this->archives_obj->display(); 
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    public function main_admin(){
        ?>
            <div class="wrap">
                <h1>Image Uploads</h1>
                <h2>Purposes</h2>
                <p>These come in the 'why are you uploading this image selection box'</p>
                <b>To add a new purpose</b>
                <ol>
                    <li>Enter the Title of the Purpose as displayed to the 'uploader', eg, Competition 3</li>
                    <li>Is this purpose enabled? This allows regular 'purposes' eg, competitions to be created but not presented to the 'uploader'</li>
                    <li>Enter a short description</li>
                </ol>

                <hr/>
                <h2>Clean up files</h2>
                <p>
                Deletion of the actual files on disk won't take place until you click the button below.
                </p>
                <form method="post"  action="<?php echo admin_url( 'admin-post.php' ); ?>">
                    <input type="submit" class="button button-primary" value="Remove unrequired image files">
                    <input type="hidden" name="action" value="purge_files">
                </form>
            </div>
        <?php
    }
    
    public function osprey_admin_notice() {
        ?>
            <?php
                if (isset($_GET['page']) && $_GET['page'] == 'osprey-upload-purposes'){
                    if (isset($_GET['purpose'])){
                        ?><div class="notice notice-success is-dismissible"><p><?php
                        _e(' Added the purpose: ' . $_GET['purpose'], 'shapeSpace');
                        ?></p></div><?php
                    }
                } 
                
            ?>
        <?php
    }

    public function add_removable_arg($args) {
        array_push($args, 'purpose','result');
        return $args;
    }

    // purposes customer WP_List_Table object
    public $purposes_obj;
    public $images_obj;
    public $archives_obj;
}
