<?php

namespace whatwedo\AcfCleaner;

/**
 * Bootstrap
 *
 * @since      1.0.0
 * @package    wwd-acf-cleaner
 */

class Bootstrap
{
    public function __construct()
    {
        //add_action('init', [$this, 'autoTrigger']);
    }

    public function autoTrigger()
    {
        $postId = 6854;
        $isDry = true;

        (new Discovery($postId, $isDry))->cleanAcfUnusedData();
    }
}
