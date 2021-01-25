<?php

namespace whatwedo\AcfCleaner;

/**
 * Helper
 *
 * @since      1.0.0
 * @package    wwd-acf-cleaner
 */

class Helper
{
    public static function ajaxDataResponse($unusedData, $post)
    {
        return [
            'amount' => sizeof($unusedData),
            'post' => [
                'id' => $post->ID,
                'name' => $post->post_title,
                'permalink' => get_permalink($post->ID),
                'postType' => $post->post_type,
            ]
        ];
    }

    public static function returnAjaxData(array $data)
    {
        if(!$data) {
            echo [];
            wp_die();
        }

        echo json_encode($data);
        wp_die();
    }

    public static function checkNonce($action)
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], $action)) {
            exit('This is not allowed to do');
        }
    }
}
