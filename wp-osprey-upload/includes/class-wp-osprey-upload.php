<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Osprey_Upload
 * @subpackage Wp_Osprey_Upload/includes
 * @author     Your Name <email@example.com>
 */
class Wp_Osprey_Upload
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Osprey_Upload_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('PLUGIN_NAME_VERSION')) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-osprey-upload';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_shortcode('wp-osprey-upload', array($this, 'shortcode'));

		add_shortcode('wp-osprey-upload-uploaded', array($this, 'uploadedImgs'));
	}

	public function uploadedImgs($attrs = [], $contnet = null, $tag = '')
	{
		// normalize attribute keys, lowercase
		$attrs = array_change_key_case((array)$attrs, CASE_LOWER);

		if (is_user_logged_in()) {

			$current_user = wp_get_current_user();
			if (!($current_user instanceof WP_User)) {
				return;
			}

			$form = $this->readDb($current_user->user_login);

			return '<div>' . $form . '</div>';
			
		} else {
			echo 'Sorry; this is only for members';
			return;
		}

		
	}

	function readDb($name)
	{
		global $wpdb;
		
		// output data of each row
		$result = "<table><tr>";
		$result .=  "<th>Your Filename</th>";
		$result .=  "<th>Title</th>";
		$result .=  "<th>Purpose</th>";
		$result .=  "<th>Date</th>";
		// $result .=  "<th>Submitted Filename</th>";
		$result .=  "</tr>";

		$queryresult = $wpdb->get_results("SELECT wp_osprey_uploads.filename,wp_osprey_uploads.title title,wp_osprey_purposes.title purpose,reg_date,displayname FROM `wp_osprey_uploads` , `wp_osprey_purposes` WHERE username='" . $name ."' and wp_osprey_uploads.purpose = wp_osprey_purposes.id");
		foreach ($queryresult as $rows) {

			$url = rawurlencode($rows->filename);

			$result .=  "<tr>";
			$result .=  "<td><a href=\"/osprey/api/uploads/" . $url. "\" class=\"foobox\" >" . $rows->filename . "</a></td>";
			$result .=  "<td>"  . $rows->title . "</td>";
			$result .=  "<td>" . $rows->purpose . "</td>";
			$result .=  "<td>" . $rows->reg_date . "</td>";
			// $result .=  "<td>" . $rows->displayname."%".$rows->title.".jpg</td>";

			$result .=  "</tr>";
		}
		$result .=  "</table>";

		return $result;
	}

	function readAllDb()
	{

		$newdb = new wpdb('root', 'root', 'OSPREY_UPLOAD', 'localhost');
		// output data of each row
		$result = "<table>";
		$result .= "<tr><th>id</th>";
		$result .=  "<th>Username</th>";
		$result .=  "<th>filename</th>";
		$result .=  "<th>Title</th>";
		$result .=  "<th>Purpose</th>";
		$result .=  "<th>Date</th>";
		$result .=  "</tr>";

		$queryresult = $newdb->get_results("SELECT * FROM Uploads");

		foreach ($queryresult as $rows) {
			$result .=  "<tr>";
			$result .=  "<td>" . $rows->id . "</td>";
			$result .=  "<td>" . $rows->username . "</td>";
			$result .=  "<td><a href=\"osprey/api/uploads/" . $rows->filename . "\">" . $rows->filename . "</a></td>";
			$result .=  "<td>"  . $rows->title . "</td>";
			$result .=  "<td>" . $rows->purpose . "</td>";
			$result .=  "<td>" . $rows->reg_date . "</td>";
			$result .=  "<td><button class=\"addMedia\">Add Media</button> </td>";

			$result .=  "</tr>";
		}
		$result .=  "</table>";

		$newdb->close();
		return $result;
	}


	function add_media_library()
	{
		if (!isset($_REQUEST['id'])) {
			// set the return value you want on error
			// return value can be ANY data type (e.g., array())
			$return_value = 'your error message';

			wp_send_json_error($return_value);
		}

		$id = intval($_REQUEST['id']);
		// do processing you want based on $id

		// set the return value you want on success
		// return value can be ANY data type (e.g., array())
		$return_value = 'your success message/data';

		wp_send_json_success($return_value);
	}

	public function shortcode($attrs)
	{
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			if (!($current_user instanceof WP_User)) {
				return;
			}

		} else {
			return;
		}

		$form = <<<EOD
        <form action="" method="post" enctype="multipart/form-data" id="ofu_uploadform">
            <input type="file" class="my-pond" name="filepond[]" multiple="multiple">
        </form>

        <form id="detailsform">
            <table id="filedetails">
                <tbody>
                    <tr>
                        <th>Your filename</th>
                        <th>Title</th>
                        <th>Purpose</th>
                    </tr>
                </tbody>
            </table>

            <button type="submit" id="osprey-submit-btn" class="button" disabled="true"><span>Submit...</span></button>
        </form>
		<div>
			<p>
				<span  id="results"></span>
			</p>
		</div>
EOD;

		return '<div>' . $form . '</div>';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Osprey_Upload_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Osprey_Upload_i18n. Defines internationalization functionality.
	 * - Wp_Osprey_Upload_Admin. Defines all hooks for the admin area.
	 * - Wp_Osprey_Upload_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-osprey-upload-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-osprey-upload-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-osprey-upload-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-osprey-upload-public.php';

		$this->loader = new Wp_Osprey_Upload_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Osprey_Upload_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Wp_Osprey_Upload_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Wp_Osprey_Upload_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'create_menu', 0);
		$this->loader->add_action('admin_post_add_purpose', $plugin_admin, 'add_purpose' );
		$this->loader->add_action('admin_notices',$plugin_admin, 'osprey_admin_notice');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Wp_Osprey_Upload_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		// $this->loader->add_action('wp_ajax_call_add_media_library', $this, 'add_media_library');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Osprey_Upload_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

}
