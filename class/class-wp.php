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
        add_action('wp_ajax_singleCleanup', [$this, 'singleCleanupRequest']);

        add_action('add_meta_boxes', [$this, 'registerCleanerMetabox']);
    }

    public function registerCleanerMetabox() {
		global $post;

        if(!current_user_can('manage_options')) {
            return;
        }

		/* TODO: move this to ajax call on-demand. */
		$discovery = new Discovery($post->ID, true);
		$unused = $discovery->getUnusedData();
		if ($unused) {
			add_meta_box('unused-acf-fields', 'Unused ACF Fields', function() use ($post, $unused) {
                echo $this->cleanerMetaboxContent($post->ID, $unused);
            });
		}
	}

	public function cleanerMetaboxContent($postId, $unused) {
        $content = '<div id="acf_cleanup_data">';
		    foreach ($unused as $name => $key) {
			    $field = trim($name, '_');
			    $value = get_field($field, $postId);
			    $value = is_array($value) ? var_export($value, true) : print_r($value, true);
			    $escaped_value = esc_html($value);
                $content .= <<<EOD
                    <div class="acf_cleanup_list" style="display: flex; flex-direction: row; margin-bottom: 0.5rem;">
                        <span style="width: 25%; margin-right: 0.5rem; margin-top: 4px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" data-key="$key" title="$field">$field</span>
                        <input style="height: 2em; flex-grow: 1" disabled value="$escaped_value">
                    </div>
                EOD;
            }
            $content .= '<button class="button secondary" id="acf_cleanup_action">Clean data</button>';
            $content .= <<<EOD
                <script>jQuery('#acf_cleanup_action').on('click', function(e){
                    e.preventDefault();
                    jQuery(e.currentTarget).addClass('disabled')
                    if(confirm('Are you sure?')){
                        wp.ajax.send('singleCleanup', {
                            data: { postId: $postId },
                            success: function(response) {
                                jQuery('#acf_cleanup_data').empty().html('Success!<br>Removed ' + response.count + ' fields');
                                jQuery(e.currentTarget).removeClass('disabled');
                            },
                            error: function(response) {
                                alert(response)
                                jQuery(e.currentTarget).removeClass('disabled');
                            }
                        })
                    } else {
                        jQuery(e.currentTarget).removeClass('disabled');
                    }
                    })
                </script>
            EOD;
        $content .= '</div>';

        return $content;
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
        $postId = 7303; // 41969; // 41240; // 40589;
        $isDry = true;
        $discovery = new Discovery($postId, $isDry);

        foreach ($discovery->getUnusedData() as $name => $key) {
            echo '<pre>';
            print_r($name . ' (' . $key . ')');
            echo '</pre>';
        }
        */
    }

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

	public function singleCleanupRequest()
	{
		$postId = $_POST['postId'];
		$isDry = false;
		$discovery = new Discovery($postId, $isDry);

		$count = count($discovery->cleanAcfUnusedData());
		if ($count) {
			wp_send_json_success(['count' => count($discovery->cleanAcfUnusedData())]);
		}

		wp_send_json_error( 'Someting went wrong...' );
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
