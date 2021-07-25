<?php

require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class Purposes_list extends WP_List_Table
{

    /** Class constructor */
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Purpose', 'sp'), //singular name of the listed records
            'plural'   => __('Purposes', 'sp'), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ]);
    }

    /**
     * Retrieve purpose data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_purposes($per_page = 25, $page_number = 1)
    {
        global $wpdb;
        $sql = "SELECT wp_osprey_purposes.*, count(wp_osprey_uploads.id) as number_images FROM `wp_osprey_purposes` left join wp_osprey_uploads on (wp_osprey_purposes.id = wp_osprey_uploads.purpose) GROUP BY wp_osprey_purposes.id";

        if (! empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;


        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    public static function get_image_ids($purpose_id)
    {
        global $wpdb;

        $sql = "SELECT wp_osprey_uploads.id FROM wp_osprey_uploads,wp_osprey_purposes WHERE wp_osprey_uploads.purpose = " . $purpose_id;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }

    public static function get_purpose($purpose_id)
    {
        global $wpdb;

        $sql = "SELECT * FROM wp_osprey_purposes WHERE id = " . $purpose_id;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }


    /**
     * Delete a Purpose
     *
     * @param int $id Purpose ID
     */
    public static function delete_purpose($id)
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}osprey_uploads WHERE purpose=".esc_sql($id);
        $imagesWithPurpose = $wpdb->get_var($sql);
        if ($imagesWithPurpose == 0) {
            $wpdb->delete(
                "{$wpdb->prefix}osprey_purposes",
                [ 'id' => $id ],
                [ '%d' ]
            );
        } else {
            ?>
<div class="notice notice-error is-dismissible">
    <p><?php
            _e(' Can not delete that purpose, there are still images exist with that purpose.'); ?>
    </p>
</div><?php
        }
    }

    public static function enable_purpose($id)
    {
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}osprey_purposes",
            [ "enabled" => 1 ],
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    public static function disable_purpose($id)
    {
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}osprey_purposes",
            [ "enabled" => 0 ],
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

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}osprey_purposes";

        return $wpdb->get_var($sql);
    }


    /** Text displayed when no purposes data is available */
    public function no_items()
    {
        _e('No purposes avaliable.', 'sp');
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
            case 'title':
            case 'description':
            case 'number_images':
                return $item[ $column_name ];
            case 'enabled':
                return $item[ $column_name ] == 1 ? 'Enabled':'Disabled';
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
        $delete_nonce = wp_create_nonce('sp_delete_purpose');

        $title = '<strong>' . $item['name'] . '</strong>';

        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&purpose=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
            'enable' => sprintf('<a href="?page=%s&action=%s&purpose=%s&_wpnonce=%s">Enable</a>', esc_attr($_REQUEST['page']), 'enable', absint($item['ID']), $delete_nonce)
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
            'title'    => __('Title', 'sp'),
            'description' => __('Description', 'sp'),
            'enabled'    => __('Enabled', 'sp'),
            'number_images' => __('Number of images', 'sp')
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
            'enabled' => array( 'enabled', false )
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
            'bulk-enable' => 'Enable',
            'bulk-disable' => 'Disable',
            'bulk-download' => 'Download (inc anon)'
        ];

        return $actions;
    }


    /**
     * Handles data query and filter, sorting, and pagination..`
     */
    public function prepare_items()
    {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page('purposes_per_page', 25);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = self::get_purposes($per_page, $current_page);
    }

    public function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (! wp_verify_nonce($nonce, 'sp_delete_purpose')) {
                die('Go get a life script kiddies');
            } else {
                self::delete_purpose(absint($_GET['purpose']));

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }
        }
        // else {
        //     echo("Current action");
        //     echo($this->current_action());
        // }

        // If the delete bulk action is triggered
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
             || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            $delete_ids = esc_sql($_POST['bulk-ids']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_purpose($id);
            }
        } elseif ((isset($_POST['action']) && $_POST['action'] == 'bulk-enable')
        || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-enable')
           ) {
            $enabled_ids = esc_sql($_POST['bulk-ids']);
            // loop over the array of record IDs and delete them
            foreach ($enabled_ids as $id) {
                self::enable_purpose($id);
            }
        } elseif ((isset($_POST['action']) && $_POST['action'] == 'bulk-disable')
        || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-disable')
           ) {
            $enabled_ids = esc_sql($_POST['bulk-ids']);
            // loop over the array of record IDs and delete them
            foreach ($enabled_ids as $id) {
                self::disable_purpose($id);
            }
        } elseif ((isset($_POST['action']) && $_POST['action'] == 'bulk-download')
        || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-download')
           ) {
            if (isset($_POST['bulk-ids'])) {
                $enabled_ids = esc_sql($_POST['bulk-ids']);
                $osprey = new Osprey_Server();
                // loop over the array of record IDs and delete them
                foreach ($enabled_ids as $id) {
                    $archive_ids = self::get_image_ids($id);
                    $purpose = self::get_purpose($id);
                    if (count($purpose)>0) {
                        $name = $purpose[0]['title'];
                    }
                    echo('<pre>'.print_r($name, true).'</pre>');
                    $result = $osprey->request_archive($archive_ids, false, $name);
                    $resultanon = $osprey->request_archive($archive_ids, true, $name); ?>
<div class="notice notice-success is-dismissible">
    <b>Created Download ZIP file: </b>
    <p>
        <?php echo($result); ?>
    </p>
    <p>
        <?php echo($resultanon); ?>
    </p>
</div>

<?php
                }
            }
        }
    }
}
