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

        if (!$post) {
            return false;
        }

        add_action('init', [$this, 'getUnusedData']);

        $this->cleanAcfUnusedData();
    }

    public function getPostObject()
    {
        $post = get_post($this->postId);
        if (!$post) return false;
        return $post;
    }

    public function getUnusedData()
    {
        if (empty($this->unusedData)) {
            $this->unusedData = $this->checkMetadataUsage($this->postId);
        }

        return $this->unusedData;
    }

    public function cleanAcfUnusedData()
    {
        $this->getUnusedData(); // make sure data are loaded

        if (!$this->isDry) {
            $unusedData = unserialize(serialize($this->unusedData)); // Hacky: create a copy
            $this->deleteMetadata($this->postId, $unusedData);
        }

        return $this->unusedData;
    }

    protected function getStoredMetadataKeys($postId)
    {
        $data = acf_get_meta($postId);
        return array_filter($data, function ($key) {
            return strpos($key, '_') === 0 ? true : false;
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getUsedFieldKeys($postId)
    {
        $groups = acf_get_field_groups(['post_id' => $postId]);
        $blueprints = [];
        $neutralKeys = [];
        foreach ($groups as $group) {
            $fields = acf_get_fields($group['key']);

            foreach ($fields as $field) {
                $blueprints[$field['name']] = $field;

                if(isset($field['_clone'])) {
                    $neutralKeys[] = $field['_clone'];
                }

                if($field['type'] === 'repeater' || $field['type'] === 'flexible_content') {
                    $neutralKeys[] = $field['key'];

                    // Strange second key - but needed for some fields
                    if($field['__key']) {
                        $neutralKeys[] = $field['__key'];
                    }
                }
            }
        }

        $blueprintKeys = $this->field_pluck($blueprints, 'key');
        $flattenBlueprintKeys = $this->array_flatten($blueprintKeys);
        $flattenBlueprintKeys['wwdacfcleaner_clone'] = $neutralKeys;

        return $this->combineKeys($flattenBlueprintKeys);
    }

    protected function checkMetadataUsage($postId)
    {
        $allUsedKeys = $this->getUsedFieldKeys($postId);
        $savedKeys = $this->getStoredMetadataKeys($postId);

        return array_filter($savedKeys, function ($value) use ($allUsedKeys) {
            return in_array($value, $allUsedKeys) ? false : true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function deleteMetadata($postId, $unusedData)
    {
        foreach ($unusedData as $name => $key) {
	        $name = trim($name,'_');

            acf_delete_metadata($postId, $name, false);
            acf_delete_metadata($postId, $name, true);
        }
    }

    private function field_pluck($array, $key) {
        if(!is_array($array)) {
            return [];
        }

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
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->array_flatten($value));
            } else {
                $result = array_merge($result, [$key => $value]);
            }
        }
        return $result;
    }

    private function combineKeys($array)
    {
        $flatArray = [];
        foreach ($array as $value) {
            if(is_array($value)) {
                $flatArray = array_merge($flatArray, $value);
            } else {
                $flatArray[] = $value;
            }
        }
        return $flatArray;
    }
}
