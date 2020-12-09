<?php

namespace whatwedo\AcfCleaner;

/**
 * Data
 *
 * @since      1.0.0
 * @package    wwd-acf-cleaner
 */

class Data
{
    public function singleDiscovery($postId)
    {
        $discovery = new Discovery($postId, true);
        if(!$discovery) {
            return [];
        }

        $data = Helper::ajaxDataResponse($discovery->getUnusedData(), $discovery->getPostObject());
        return $data;
    }


    public function batchDiscovery($postType = [], $paged = 1, $isDry = true)
    {
        $data = [];
        $chunk = $this->getChunkedPosts($postType, $paged);

        foreach ($chunk['postIds'] as $postId) {
            $discovery = new Discovery($postId, $isDry);
            $response = Helper::ajaxDataResponse($discovery->getUnusedData(), $discovery->getPostObject());
            array_push($data, $response);
        }

        $chunk['data'] = $data;
        return $chunk;
    }

    private function getChunkedPosts($postType = [], int $paged = 1)
    {
        if(empty($postType)) {
            $postType = array_keys(self::getAllCustomPostTypes());
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

    public static function getAllCustomPostTypes($addCount = true)
    {
        $cpts = get_post_types([
            'public'   => true,
        ], 'object');

        $cpts = array_column($cpts, 'label', 'name');
        asort($cpts);

        if($addCount) {
            foreach ($cpts as $cpt => $name) {
                $amount = (array) wp_count_posts($cpt);
                $cpts[$cpt] = $name . ' (' . array_sum($amount) . ' posts)';
            }
        }

        return $cpts;
    }
}
