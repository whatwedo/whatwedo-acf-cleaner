<?php

namespace whatwedo\AcfCleaner;

/**
 * WP Hooks
 *
 * @since      1.0.0
 * @package    wwd-acf-cleaner
 */

class WP
{
    public function __construct()
    {
    }

    /*
        Enqueue frontend script
     */

    /*
    function enqueue_script() {

        wp_enqueue_script( WWDACFCLEANER_NAME, WWDACFCLEANER_ASSET_DIR . 'script/app.js', array('jquery'), WWDACFCLEANER_VERSION, true );

    }
    */


    /*
        Enqueue admin script
     */

    /*
    function enqueue_admin_script( $hook ) {

        wp_enqueue_script( WWDACFCLEANER_NAME . '-admin', WWDACFCLEANER_ASSET_DIR . 'script/admin.js', array('jquery'), WWDACFCLEANER_VERSION, true );

    }
    */
}
