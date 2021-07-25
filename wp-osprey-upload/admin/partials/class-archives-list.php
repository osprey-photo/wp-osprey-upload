<?php

require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class Archives_list extends WP_List_Table
{

    /** Class constructor */
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Download', 'sp'), //singular name of the listed records
            'plural'   => __('Downloads', 'sp'), //plural name of the listed records
            'ajax'     => false //does this table support ajax?
        ]);
    }

    /**
     * Retrieve archive data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_archives($per_page = 25, $page_number = 1)
    {
        global $wpdb;
        $sql = "SELECT * FROM  {$wpdb->prefix}osprey_archives";

        if (! empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;


        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }


    /**
     * Delete a Archive
     *
     * @param int $id Archive ID
     */
    public static function delete_archive($id)
    {
        global $wpdb;

        // TODO - send request to delete file

        $wpdb->delete(
            "{$wpdb->prefix}osprey_archives",
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

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}osprey_archives";

        return $wpdb->get_var($sql);
    }


    /** Text displayed when no archives data is available */
    public function no_items()
    {
        _e('No download files avaliable.', 'sp');
    }

    public function column_filename($item)
    {
        $url = rawurlencode($item['filename']);
        return '<a href="/osprey/api/downloads/' . $url. '">' . $item['filename'] . '</a>';
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
            case 'size':
                return $item[ $column_name ];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting archives
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
        $delete_nonce = wp_create_nonce('sp_delete_archive');
        $title = '<strong>' . $item['name'] . '</strong>';
        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&archive=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
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
            'filename'    => __('Download File', 'sp'),
            'size' => __('Images', 'sp')

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
            'archive' => array( 'archive', true )
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
            'bulk-delete' => 'Delete'
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
        
        $per_page     = $this->get_items_per_page('archives_per_page', 25);
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();
        
        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);
        
        $this->items = self::get_archives($per_page, $current_page);
    }

    public function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (! wp_verify_nonce($nonce, 'sp_delete_archive')) {
                die('Go get a life script kiddies');
            } else {
                self::delete_archive(absint($_GET['archive']));

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                wp_redirect(esc_url_raw(add_query_arg()));
                exit;
            }
        } else {
            echo("Current action");
            echo($this->current_action());
        }

        // // If the delete bulk action is triggered
        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
             || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            $delete_ids = esc_sql($_POST['bulk-ids']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_archive($id);
            }
        }
    }
}
