<?php

namespace whatwedo\AcfCleaner;

/*
    Plugin Name: whatwedo ACF Cleaner
    Description: Removes unused ACF meta data from the database
    Version: 1.2.1
    Author: whatwedo
    Author URI: https://whatwedo.ch
    License: MIT
*/


// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Constants
 */

define('WWDACFCLEANER_NAME', 'wwd-acf-cleaner');
define('WWDACFCLEANER_TITLE', 'whatwedo ACF Cleaner');

// Path
define('WWDACFCLEANER_DIR', plugin_dir_path(__FILE__));
define('WWDACFCLEANER_DIR_URL', plugin_dir_url(__FILE__));


/*
    SPL Autoloading (included in PHP)
 */

spl_autoload_register(function ($class) {
    $namespace = 'whatwedo\AcfCleaner\\';
    if (strpos($class, $namespace) !== 0) {
        return;
    }

    $path = explode('\\', strtolower(str_replace('whatwedo\\AcfCleaner\\', '', $class)));
    $path[] = 'class-' . array_pop($path);

    $file = plugin_dir_path(__FILE__) . 'class/' . implode(DIRECTORY_SEPARATOR, $path) . '.php';

    require($file);
});


/**
 * Init Classes
 */

add_action('plugins_loaded', function() {
    if (!function_exists('get_field')) {
        add_action('admin_notices', function () {
            $class = 'notice notice-error';
            $message = sprintf(
                'Warning: You need to activate <a href="%s">Advanced Custom Fields</a> in order to use ' . WWDACFCLEANER_TITLE . '.'
                , admin_url('plugins.php')
            );

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        });
    }
});

add_action('acf/init', function() {
    new WP();
});
