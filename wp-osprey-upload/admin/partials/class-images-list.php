<?php


require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once plugin_dir_path(dirname(__FILE__)) . 'partials/osprey-server.php';

class Images_list extends WP_List_Table
{

    /** Class constructor */
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Image', 'sp'), //singular name of the listed records
            'plural'   => __('Images', 'sp'), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ]);
    }

    public $zipfile;

    /**
     * Retrieve image data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_images($per_page = 25, $page_number = 1)
    {
        global $wpdb;
        $sql =
        "SELECT wp_osprey_uploads.id,wp_osprey_uploads.filename,wp_osprey_uploads.title title,wp_osprey_uploads.displayname,wp_osprey_purposes.title purpose,reg_date FROM wp_osprey_uploads , wp_osprey_purposes WHERE wp_osprey_uploads.purpose = wp_osprey_purposes.id";
        if (! empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;


        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }

    public static function get_image($imageid)
    {
        global $wpdb;
        $sql =
        "SELECT wp_osprey_uploads.id,wp_osprey_uploads.filename,wp_osprey_uploads.title title,wp_osprey_uploads.displayname,wp_osprey_purposes.title purpose,reg_date FROM wp_osprey_uploads , wp_osprey_purposes WHERE wp_osprey_uploads.purpose = wp_osprey_purposes.id AND wp_osprey_uploads.id = ".$imageid;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }


    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_image($id)
    {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}osprey_uploads",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }


    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}osprey_uploads";

        return $wpdb->get_var($sql);
    }


    /** Text displayed when no customer data is available */
    public function no_items()
    {
        _e('No images avaliable.', 'sp');
    }


    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'filename':
            case 'title':
            case 'purpose':
            case 'displayname':
                return $item[ $column_name ];
            // case 'enabled':
            // 	return $item[ $column_name ] == 1 ? 'Enabled':'Disabled';
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-ids[]" value="%s" />',
            $item['id']
        );
    }


    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_name($item)
    {
        $delete_nonce = wp_create_nonce('sp_delete_image');

        $title = '<strong>' . $item['name'] . '</strong>';

        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&purpose=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce)
        ];

        return $title . $this->row_actions($actions);
    }


    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'filename'    => __('Filename', 'sp'),
            'title' => __('Title', 'sp'),
            'purpose'    => __('Purpose', 'sp'),
            'displayname'    => __('Name', 'sp')
            
        ];

        return $columns;
    }


    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'title' => array( 'title', true ),
            'purpose' => array( 'purpose', true ),
            'displayname' => array('displayname ',true)
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => 'Delete',
            'bulk-archive' => 'Download',
            'bulk-add-media' => 'Add to Media'
        ];

        return $actions;
    }

    public function column_filename($item)
    {
        $url = rawurlencode($item['filename']);
        return '<a href="/osprey/api/uploads/' . $url. '" class="thickbox">' . $item['filename'] . '</a>';
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
        
        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('images_per_page', 35);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = self::get_images($per_page, $current_page);
    }


    public function add_to_media_library($id)
    {
        echo("hello".$id);
        $image = self::get_image($id)[0];
    
        $url = get_site_url(null, '/osprey/api/uploads/' .rawurlencode($image['filename']));
        $file_array  = [ 'name' => $image['filename'], 'tmp_name' => $url ];
        // Do the validation and storage stuff.
        $mediaid = media_sideload_image($url, null, null, 'id');
        return $mediaid;
    }

    public function process_bulk_action()
    {

        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (! wp_verify_nonce($nonce, 'sp_delete_customer')) {
                die('Go get a life script kiddies');
            } else {
                self::delete_image(absint($_GET['customer']));

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }
        }

        // If the delete bulk action is triggered
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
             || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            $delete_ids = esc_sql($_POST['bulk-ids']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_image($id);
            }

            // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                // wp_redirect( esc_url_raw(add_query_arg()) );
            // exit;
        } elseif ((isset($_POST['action']) && $_POST['action'] == 'bulk-archive')
        || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-archive')
        ) {
            $fn = function ($id) {
                return array('id'=>$id);
            };

            $archive_ids = array_map($fn, esc_sql($_POST['bulk-ids']));
            $osprey = new Osprey_Server();
            $result = $osprey->request_archive($archive_ids, false); ?>
<div class="notice notice-success is-dismissible">
    <b>Created Download ZIP file: </b>
    <p>
        <?php echo(print_r($result, true)); ?>
    <p>
</div>

<?php
        } elseif ((isset($_POST['action']) && $_POST['action'] == 'bulk-add-media')
        || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-add-media')
        ) {
            $ids = esc_sql($_POST['bulk-ids']);
            
            // loop over the array of record IDs and delete them
            foreach ($ids as $id) {
                self::add_to_media_library($id);
                $imgsrc = get_site_url('/wp-admin/upload.php?item='.$id); ?>
<div class="notice notice-success is-dismissible">
    <b>Added to media library</b>
</div>
<?php
            }
        }
    }
}
