<?php

namespace whatwedo\AcfCleaner;

use FineSolutions\Helper\QueryHelper;

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

        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_filter('script_loader_tag', [$this, 'addScriptAttribute'], 10, 3);

        add_action('wp_ajax_singleDiscovery', [$this, 'singleDiscovery']);
        //add_action('wp_ajax_cleanPost', [$this, 'cleanPost']);
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
        $postId = 2832;
        $isDry = true;
        $discovery = new Discovery($postId, $isDry);
        print_r($discovery->getUnusedData());
        */

        /*
        $posts = QueryHelper::getBlogPosts(5);

        foreach ($posts as $post) {
            $discovery = new Discovery($post->getId(), true);

            echo '<h3>' . $post->getTitle() . ' (ID: ' . $post->getId() . ')</h3>';
            $unusedData = $discovery->getUnusedData();
            print_r($unusedData);
            echo 'Unused datasets: ' . sizeof($unusedData);
        }
        */
    }

    /*
        Enqueue admin script
     */

    public function enqueueAdminAssets()
    {
        if (get_current_screen()->id === 'tools_page_wwd-acf-cleaner') {
            //wp_enqueue_script('react');
            //wp_enqueue_script('react-dom');

            wp_enqueue_script('vuejs', 'https://unpkg.com/vue@^3/dist/vue.global.prod.js');
            wp_register_script('wwdac-vuejs', WWDACFCLEANER_DIR_URL . 'assets/wwd-acf-cleaner.js', 'vuejs', true);

            wp_localize_script('wwdac-vuejs', 'wwdacData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'action' => 'discoverPost',
                'nonce' => wp_create_nonce($this->actionNonceName),
                'postTypes' => $this->getAllCustomPostTypes(),
                'posts' => $this->batchDiscovery(['post']),
            ]);
            wp_enqueue_script('wwdac-vuejs');

            wp_enqueue_style('tailwind-css', 'https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css');
        }
    }

    public function addScriptAttribute($tag, $handle, $src)
    {
        if ('wwdac-vuejs' !== $handle) {
            return $tag;
        }
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';

        return $tag;
    }

    public function singleDiscovery()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], $this->actionNonceName)) {
            exit('This is not allowed to do');
        }

        $postId = $_REQUEST['postId'];

        $discovery = new Discovery($postId, true);
        if(!$discovery) {
            echo [];
            wp_die();
        }

        echo json_encode($this->ajaxDataResponse($discovery->getUnusedData(), $discovery->getPostObject()));
        wp_die();
    }

    public function batchDiscoveryRequest()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], $this->actionNonceName)) {
            exit('This is not allowed to do');
        }

        $postType = $_REQUEST['postType'];
        $paged = $_REQUEST['paged'];

        echo json_encode($batchData = $this->batchDiscovery($postType, $paged, true));
        wp_die();
    }

    public function batchCleanupRequest()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], $this->actionNonceName)) {
            exit('This is not allowed to do');
        }

        $postType = $_REQUEST['postType'];
        $paged = $_REQUEST['paged'];

        echo json_encode($this->batchDiscovery($postType, $paged, true)); // TODO: change this
        wp_die();
    }

    public function batchDiscovery($postType = [], $paged = 1, $isDry = true)
    {
        $data = [];
        $chunk = $this->getChunkedPosts($postType, $paged);

        foreach ($chunk['postIds'] as $postId) {
            $discovery = new Discovery($postId, $isDry);
            $response = $this->ajaxDataResponse($discovery->getUnusedData(), $discovery->getPostObject());
            array_push($data, $response);
        }

        $chunk['data'] = $data;
        return $chunk;
    }

    private function getChunkedPosts($postType = [], int $paged = 1)
    {
        if(empty($postType)) {
            $postType = array_keys($this->getAllCustomPostTypes());
        }

        $the_query = new \WP_Query([
            'post_type' => $postType,
            'posts_per_page' => 5,
            'fields' => 'ids',
            'paged' => $paged,
        ]);

        return [
            'postIds' => $the_query->posts,
            'foundPosts' => $the_query->found_posts,
            'currentPage' => $paged,
            'totalPage' => $the_query->max_num_pages,
            'nextPage' => $paged < $the_query->max_num_pages ? $paged + 1 : false,
        ];
    }

    private function getAllCustomPostTypes()
    {
        $cpts = get_post_types([
            'public'   => true,
            '_builtin' => false
        ], 'object');

        return array_column($cpts, 'label', 'name');
    }

    private function ajaxDataResponse($unusedData, $post)
    {
        return [
            //'dataset' => $unusedData,
            'amount' => sizeof($unusedData),
            'post' => [
                'id' => $post->ID,
                'name' => $post->post_title,
                'permalink' => get_permalink($post->ID),
                'postType' => $post->post_type,
            ]
        ];
    }
}
