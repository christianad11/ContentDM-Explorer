<?php
/**
 * ContentDM API Handler
 * 
 * Handles all API communication with ContentDM servers
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDM_API {
    
    private $server_url = '';
    private $api_base = '';
    private $image_base = '';
    private $timeout = 15;
    
    public function __construct($server_url = '') {
        if ($server_url) {
            $this->set_server($server_url);
        }
    }
    
    /**
     * Set the ContentDM server URL
     */
    public function set_server($url) {
        $url = rtrim(trim($url), '/');
        $parsed = parse_url($url);
        
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }
        
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'];
        
        $this->server_url = $scheme . '://' . $host;
        $this->api_base = $this->server_url . '/digital/bl/dmwebservices/index.php';
        $this->image_base = $this->server_url . '/digital/api/singleitem/image';
        
        return true;
    }
    
    /**
     * Get server URL
     */
    public function get_server_url() {
        return $this->server_url;
    }
    
    /**
     * Make an API call
     */
    public function call($function) {
        if (empty($this->api_base)) {
            return new WP_Error('not_initialized', __('CDM API not initialized', 'cdm-explorer'));
        }
        
        $url = $this->api_base . '?q=' . $function . '/json';
        
        $response = wp_remote_get($url, [
            'timeout' => $this->timeout,
            'sslverify' => true,
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid JSON response', 'cdm-explorer'));
        }
        
        return $data;
    }
    
    /**
     * Validate server connection
     */
    public function validate() {
        $collections = $this->call('dmGetCollectionList');
        
        if (is_wp_error($collections)) {
            return $collections;
        }
        
        if (!is_array($collections)) {
            return new WP_Error('invalid_response', __('Invalid response from server', 'cdm-explorer'));
        }
        
        return [
            'valid' => true,
            'collections' => $collections,
            'total' => count($collections),
        ];
    }
    
    /**
     * Get all collections
     */
    public function get_collections() {
        return $this->call('dmGetCollectionList');
    }
    
    /**
     * Get collection field info
     */
    public function get_collection_fields($alias) {
        $alias = $this->clean_alias($alias);
        return $this->call("dmGetCollectionFieldInfo/{$alias}");
    }
    
    /**
     * Get items from collection
     */
    public function get_items($alias, $limit = 100, $start = 0, $fields = '!title!creato') {
        $alias = $this->clean_alias($alias);
        return $this->call("dmQuery/{$alias}/.^.^all^and/{$fields}/{$limit}/{$start}");
    }
    
    /**
     * Get all items from collection (handles pagination)
     */
    public function get_all_items($alias, $max_items = 1000) {
        $alias = $this->clean_alias($alias);
        $all_items = [];
        $start = 0;
        $limit = 100;
        
        while (count($all_items) < $max_items) {
            $result = $this->get_items($alias, $limit, $start);
            
            if (is_wp_error($result) || empty($result['records'])) {
                break;
            }
            
            $all_items = array_merge($all_items, $result['records']);
            
            $total = $result['pager']['total'] ?? 0;
            $start += $limit;
            
            if ($start >= $total) {
                break;
            }
        }
        
        return array_slice($all_items, 0, $max_items);
    }
    
    /**
     * Get single item info
     */
    public function get_item_info($alias, $pointer) {
        $alias = $this->clean_alias($alias);
        return $this->call("dmGetItemInfo/{$alias}/{$pointer}");
    }
    
    /**
     * Get image info
     */
    public function get_image_info($alias, $pointer) {
        $alias = $this->clean_alias($alias);
        return $this->call("dmGetImageInfo/{$alias}/{$pointer}");
    }
    
    /**
     * Get image URL
     */
    public function get_image_url($alias, $pointer, $size = 'default') {
        $alias = $this->clean_alias($alias);
        return $this->image_base . "/{$alias}/{$pointer}/{$size}.jpg";
    }
    
    /**
     * Get item page URL
     */
    public function get_item_url($alias, $pointer) {
        $alias = $this->clean_alias($alias);
        return $this->server_url . "/digital/collection/{$alias}/id/{$pointer}";
    }
    
    /**
     * Get collection page URL
     */
    public function get_collection_url($alias) {
        $alias = $this->clean_alias($alias);
        return $this->server_url . "/digital/collection/{$alias}";
    }
    
    /**
     * Clean alias (remove leading slash)
     */
    private function clean_alias($alias) {
        return ltrim($alias, '/');
    }
}

