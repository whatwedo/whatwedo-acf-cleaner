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
    private $actionNonceName = WWDACFCLEANER_NAME . '-action-nonce';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);

        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_filter('script_loader_tag', [$this, 'addScriptAttribute'], 10, 3);

        add_action('wp_ajax_singleDiscovery', [$this, 'singleDiscovery']);
        add_action('wp_ajax_batchDiscovery', [$this, 'batchDiscoveryRequest']);
        add_action('wp_ajax_batchCleanup', [$this, 'batchCleanupRequest']);
    }

    public function addAdminMenu()
    {
        $this->tool_menu_id = add_management_page(
            __('WWD ACF Cleaner by whatwedo', 'wwdac'),
            __('ACF Cleaner', 'wwdac'),
            'manage_options',
            'wwd-acf-cleaner',
            [$this, 'managementInterfaceRender']
        );
    }

    public function managementInterfaceRender()
    {

        echo '<div id="wwdac-app"></div>';

        /*
        // Test hardcoded post on server side
        $postId = 2832;
        $isDry = true;
        $discovery = new Discovery($postId, $isDry);
        print_r($discovery->getUnusedData());
        */
    }

    /*
        Enqueue admin script
     */

    public function enqueueAdminAssets()
    {
        if (get_current_screen()->id === 'tools_page_wwd-acf-cleaner') {
            wp_enqueue_script('vuejs', WWDACFCLEANER_DIR_URL . 'assets/vendors/vue.global.prod.js', [], true);
            wp_register_script('wwdac-vuejs', WWDACFCLEANER_DIR_URL . 'assets/wwd-acf-cleaner.js', 'vuejs', true);

            wp_localize_script('wwdac-vuejs', 'wwdacData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'action' => 'discoverPost',
                'nonce' => wp_create_nonce($this->actionNonceName),
                'postTypes' => Data::getAllCustomPostTypes(),
                'posts' => (new Data)->batchDiscovery(['post', 'page']),
            ]);
            wp_enqueue_script('wwdac-vuejs');

            wp_enqueue_style('tailwind-css', WWDACFCLEANER_DIR_URL . 'assets/vendors/tailwind.min.css', [], true);
        }
    }

    public function addScriptAttribute($tag, $handle, $src)
    {
        if ('wwdac-vuejs' !== $handle) {
            return $tag;
        }
        $tag = str_replace(' src', ' type="module" src', $tag);

        return $tag;
    }

    public function singleDiscovery()
    {
        Helper::checkNonce($this->actionNonceName);

        $postId = (int) $_POST['postId'];
        $data = (new Data())->singleDiscovery($postId);

        Helper::returnAjaxData($data);
    }

    public function batchDiscoveryRequest()
    {
        Helper::checkNonce($this->actionNonceName);

        $params = $this->checkParams();
        $batchData = (new Data())->batchDiscovery($params['postType'], $params['paged'], true);

        Helper::returnAjaxData($batchData);
    }

    public function batchCleanupRequest()
    {
        Helper::checkNonce($this->actionNonceName);

        $params = $this->checkParams();
        $batchData = (new Data())->batchDiscovery($params['postType'], $params['paged'], false);

        Helper::returnAjaxData($batchData);
    }

    private function checkParams()
    {
        $postType = array_map('sanitize_text_field', explode(',', $_POST['postType']));
        foreach ($postType as $singlePostType) {
            if (!post_type_exists($singlePostType)) {
                unset($postType[$singlePostType]);
            }
        }
        $paged = (int) $_POST['paged'];

        return [
            'postType' => $postType,
            'paged' => $paged,
        ];
    }
}
