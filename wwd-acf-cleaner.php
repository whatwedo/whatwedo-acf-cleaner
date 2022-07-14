<?php

namespace whatwedo\AcfCleaner;

/*
    Plugin Name: whatwedo ACF Cleaner
    Description: Removes unused ACF meta data from the database
    Version: 1.1.0
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

new WP();
