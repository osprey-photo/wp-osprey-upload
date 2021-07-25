<?php

class Osprey_Server
{

    /** Class constructor */
    public function __construct()
    {
    }

    public function request_archive($image_ids, $anonymised, $name = 'images')
    {
        $url = home_url('/osprey/api/archive.php');
        $payload = array(
                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'        => json_encode(array('imageids'=>$image_ids,'anonymised'=>$anonymised,'name'=>$name)),
                'method'      => 'POST',
                'data_format' => 'body',
            );
        $result = wp_remote_post($url, $payload);
        $jsonstr = $result['body'];
            
        // need to check the result and report errors if anything
        $jsonresult = json_decode($jsonstr, true);
        if ($jsonresult['code'] == 200) {
            global $wpdb;
            $purpose_id = $wpdb->insert(
                $wpdb->prefix."osprey_archives",
                array(
                        'filename' => $jsonresult['zipfilename'],
                        'size' => $jsonresult['size']
                    ),
                array(
                        '%s',
                        '%d',
                    )
            );
            return $jsonresult['zipfilename'];
        }

        return print_r($jsonstr, true);
    }
}
