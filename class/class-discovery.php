<?php

namespace whatwedo\AcfCleaner;

/**
 * Discovery
 *
 * @since      1.0.0
 * @package    wwd-acf-cleaner
 */

class Discovery
{
    protected $postId;
    protected $isDry;
    protected $unusedData = [];

    public function __construct($postId, $isDry)
    {
        $this->postId = $postId;
        $this->isDry = $isDry;

        $post = $this->getPostObject();

        if(!$post) {
            return false;
        }

        add_action('init', [$this, 'getUnusedData']);
    }

    public function getPostObject()
    {
        $post = get_post($this->postId);
        if(!$post) return false;
        return $post;
    }

    public function getUnusedData()
    {
        $this->unusedData = $this->checkMetadataUsage($this->postId);
        return $this->unusedData;
    }

    public function cleanAcfUnusedData()
    {
        $unusedData = $this->unusedData;
        print_r($unusedData);

        /*
        if(!$isDry) {
            $this->deleteMetadata($postId, $unusedData);
        }
        */

        return $unusedData;
    }

    protected function getStoredMetadataKeys($postId)
    {
        $data = acf_get_meta($postId);
        return array_filter($data, function($key) {
            return strpos($key, '_') === 0 ? true : false;
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getUsedFieldKeys($postId)
    {
        $blueprints = get_field_objects($postId);
        $blueprintKeys = $this->field_pluck($blueprints, 'key');
        return $this->array_flatten($blueprintKeys);
    }

    protected function checkMetadataUsage($postId)
    {
        $allUsedKeys = $this->getUsedFieldKeys($postId);
        return array_filter($this->getStoredMetadataKeys($postId), function($key) use ($allUsedKeys) {
            return in_array($key, $allUsedKeys) ? false : true;
        });
    }

    protected function deleteMetadata($postId, $unusedData)
    {
        foreach ($unusedData as $name => $key) {
            acf_delete_metadata($postId, $name, false);
            acf_delete_metadata($postId, '_' . $name, true);
        }
    }

    private function field_pluck($array, $key) {

        /* TODO: get fieldname as key on all fields - repeater, flexible */
        return array_map(function($v) use ($key) {
            // Repeater fields
            if(isset($v['sub_fields'])) {
                $data = $this->field_pluck($v['sub_fields'], $key);
                array_push($data, $v[$key]);
                return $data;
            }

            // Flexible content
            if(isset($v['layouts'])) {
                $data = $this->field_pluck($v['layouts'], $key);
                array_push($data, $v[$key]);
                return $data;
            }

            // Normal fields
            return is_object($v) ? $v->$key : $v[$key];
        }, $array);
    }

    private function array_flatten($array) {
        if (!is_array($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }
        return $result;
    }
}
