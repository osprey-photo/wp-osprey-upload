<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/public
 * @author     Your Name <email@example.com>
 */
class Wp_Osprey_Upload_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in wp_osprey_upload_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The wp_osprey_upload_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-osprey-upload-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'filepond','https://unpkg.com/filepond/dist/filepond.css',array(),null,'all');
		wp_enqueue_style( 'filepond-plugin-image-preview','https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css',array(),null,'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in wp_osprey_upload_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The wp_osprey_upload_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		
        // // <!-- Babel polyfill, contains Promise -->
		wp_enqueue_script('bable-promise',"https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.6.15/browser-polyfill.min.js",array(), null, false);

        // // <!-- Get FilePond polyfills from the CDN -->
        wp_enqueue_script('filepond-promise',"https://unpkg.com/filepond-polyfill/dist/filepond-polyfill.js",array(), null, false);
		wp_enqueue_script('filepond-img-preview',"https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js",array(), null, false);
		wp_enqueue_script('filepond-img-resize',"https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js",array(), null, false);
		wp_enqueue_script('filepond-img-exif',"https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js",array(), null, false);
        wp_enqueue_script('filepond-img-transform',"https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js",array(), null, false);
		wp_enqueue_script('filepond-metadata',"https://unpkg.com/filepond-plugin-file-metadata/dist/filepond-plugin-file-metadata.js",array(), null, false);
		wp_enqueue_script('filepond-rename',"https://unpkg.com/filepond-plugin-file-rename/dist/filepond-plugin-file-rename.js",array(), null, false);
		wp_enqueue_script('filepond-jquery',"https://unpkg.com/jquery-filepond/filepond.jquery.js",array('filepond','jquery'), null, false);
		wp_enqueue_script('filepond',"https://unpkg.com/filepond/dist/filepond.js",array(),null, false);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-osprey-upload-public.js', array( 'jquery' ), null, false );
	
		if ( is_user_logged_in() ) {
			
			$current_user = wp_get_current_user();
			if ( ! ( $current_user instanceof WP_User ) ) {
				return;
			}
			
			$dataToBePassed = array(
				'user'            => $current_user->user_login 
			);
			wp_localize_script( $this->plugin_name, 'php_vars', $dataToBePassed );

		}
}

}
