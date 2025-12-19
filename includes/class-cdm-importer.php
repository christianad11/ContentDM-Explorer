<?php
/**
 * ContentDM Importer
 * 
 * Handles importing collections and items from ContentDM to WordPress
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDM_Importer {
    
    private $api;
    private $server_url;
    
    public function __construct($server_url) {
        $this->server_url = $server_url;
        $this->api = new CDM_API($server_url);
    }
    
    /**
     * Import all collections
     */
    public function import_collections() {
        $collections = $this->api->get_collections();
        
        if (is_wp_error($collections)) {
            return $collections;
        }
        
        if (!is_array($collections)) {
            return new WP_Error('invalid_data', __('Invalid collection data', 'cdm-explorer'));
        }
        
        $imported = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($collections as $collection) {
            $result = $this->import_single_collection($collection);
            
            if (is_wp_error($result)) {
                $errors[] = $result->get_error_message();
            } elseif ($result['updated']) {
                $updated++;
            } else {
                $imported++;
            }
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($collections),
            'errors' => $errors,
        ];
    }
    
    /**
     * Import a single collection
     */
    private function import_single_collection($collection) {
        $alias = ltrim($collection['alias'] ?? '', '/');
        $name = $collection['name'] ?? $alias;
        
        if (empty($alias)) {
            return new WP_Error('no_alias', __('Collection has no alias', 'cdm-explorer'));
        }
        
        // Check if collection already exists
        $existing = $this->get_collection_by_alias($alias);
        $is_update = !empty($existing);
        
        // Get collection fields for description
        $fields = $this->api->get_collection_fields($alias);
        $field_list = '';
        if (is_array($fields)) {
            $field_names = array_column($fields, 'name');
            $field_list = implode(', ', array_slice($field_names, 0, 10));
        }
        
        // Get item count
        $items_result = $this->api->get_items($alias, 1, 0);
        $item_count = $items_result['pager']['total'] ?? 0;
        
        $post_data = [
            'post_type' => 'cdm_collection',
            'post_title' => $name,
            'post_status' => 'publish',
            'post_content' => sprintf(
                '<p>%s</p><p><strong>%s:</strong> %d</p><p><strong>%s:</strong> %s</p>',
                __('Digital collection imported from ContentDM.', 'cdm-explorer'),
                __('Total Items', 'cdm-explorer'),
                $item_count,
                __('Fields', 'cdm-explorer'),
                $field_list ?: __('N/A', 'cdm-explorer')
            ),
        ];
        
        if ($is_update) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Save meta data
        update_post_meta($post_id, '_cdm_alias', $alias);
        update_post_meta($post_id, '_cdm_server_url', $this->server_url);
        update_post_meta($post_id, '_cdm_collection_url', $this->api->get_collection_url($alias));
        update_post_meta($post_id, '_cdm_item_count', $item_count);
        update_post_meta($post_id, '_cdm_last_import', current_time('mysql'));
        
        if (is_array($fields)) {
            update_post_meta($post_id, '_cdm_fields', $fields);
        }
        
        // Create/update taxonomy term
        $term = term_exists($alias, 'cdm_collection_tax');
        if (!$term) {
            wp_insert_term($name, 'cdm_collection_tax', ['slug' => $alias]);
        }
        
        return [
            'post_id' => $post_id,
            'updated' => $is_update,
        ];
    }
    
    /**
     * Import items from a collection
     */
    public function import_items($alias, $max_items = 100) {
        $alias = ltrim($alias, '/');
        
        // Get collection post
        $collection = $this->get_collection_by_alias($alias);
        if (!$collection) {
            return new WP_Error('no_collection', __('Collection not found. Import collections first.', 'cdm-explorer'));
        }
        
        // Get items from API
        $items = $this->api->get_all_items($alias, $max_items);
        
        if (is_wp_error($items)) {
            return $items;
        }
        
        if (empty($items)) {
            return new WP_Error('no_items', __('No items found in collection', 'cdm-explorer'));
        }
        
        $imported = 0;
        $updated = 0;
        $errors = [];
        
        foreach ($items as $item) {
            $result = $this->import_single_item($alias, $item, $collection->ID);
            
            if (is_wp_error($result)) {
                $errors[] = $result->get_error_message();
            } elseif ($result['updated']) {
                $updated++;
            } else {
                $imported++;
            }
        }
        
        // Update collection item count
        update_post_meta($collection->ID, '_cdm_imported_items', $imported + $updated);
        update_post_meta($collection->ID, '_cdm_last_item_import', current_time('mysql'));
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($items),
            'errors' => $errors,
        ];
    }
    
    /**
     * Import a single item
     */
    private function import_single_item($alias, $item, $collection_id) {
        $pointer = $item['pointer'] ?? '';
        
        if (empty($pointer)) {
            return new WP_Error('no_pointer', __('Item has no pointer', 'cdm-explorer'));
        }
        
        // Get full item info
        $full_item = $this->api->get_item_info($alias, $pointer);
        if (is_wp_error($full_item)) {
            $full_item = $item; // Fall back to basic data
        }
        
        $title = $full_item['title'] ?? $item['title'] ?? "Item {$pointer}";
        
        // Check if item already exists
        $existing = $this->get_item_by_pointer($alias, $pointer);
        $is_update = !empty($existing);
        
        // Build content from metadata
        $content = $this->build_item_content($full_item);
        
        $post_data = [
            'post_type' => 'cdm_item',
            'post_title' => wp_strip_all_tags($title),
            'post_status' => 'publish',
            'post_content' => $content,
        ];
        
        if ($is_update) {
            $post_data['ID'] = $existing->ID;
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Save meta data
        update_post_meta($post_id, '_cdm_pointer', $pointer);
        update_post_meta($post_id, '_cdm_alias', $alias);
        update_post_meta($post_id, '_cdm_collection_id', $collection_id);
        update_post_meta($post_id, '_cdm_server_url', $this->server_url);
        update_post_meta($post_id, '_cdm_item_url', $this->api->get_item_url($alias, $pointer));
        update_post_meta($post_id, '_cdm_image_url', $this->api->get_image_url($alias, $pointer));
        update_post_meta($post_id, '_cdm_metadata', $full_item);
        update_post_meta($post_id, '_cdm_last_import', current_time('mysql'));
        
        // Get image info
        $image_info = $this->api->get_image_info($alias, $pointer);
        if (!is_wp_error($image_info) && is_array($image_info)) {
            update_post_meta($post_id, '_cdm_image_width', $image_info['width'] ?? 0);
            update_post_meta($post_id, '_cdm_image_height', $image_info['height'] ?? 0);
        }
        
        // Assign to collection taxonomy
        wp_set_object_terms($post_id, $alias, 'cdm_collection_tax');
        
        return [
            'post_id' => $post_id,
            'updated' => $is_update,
        ];
    }
    
    /**
     * Build item content from metadata
     */
    private function build_item_content($item) {
        $skip_fields = ['dmaccess', 'dmimage', 'dmcreated', 'dmmodified', 'dmoclcno', 
                        'dmrecord', 'restrictionCode', 'cdmfilesize', 'cdmfilesizeformatted', 
                        'cdmprintpdf', 'cdmhasocr', 'cdmisnewspaper', 'find', 'title'];
        
        $content = '<div class="cdm-item-metadata">';
        
        foreach ($item as $key => $value) {
            if (in_array($key, $skip_fields) || empty($value) || is_array($value)) {
                continue;
            }
            
            $label = ucwords(str_replace(['_', '-'], ' ', $key));
            $content .= sprintf(
                '<p><strong>%s:</strong> %s</p>',
                esc_html($label),
                wp_kses_post($value)
            );
        }
        
        $content .= '</div>';
        
        return $content;
    }
    
    /**
     * Get collection by alias (and clean up duplicates if found)
     */
    private function get_collection_by_alias($alias) {
        $posts = get_posts([
            'post_type' => 'cdm_collection',
            'meta_key' => '_cdm_alias',
            'meta_value' => $alias,
            'posts_per_page' => -1, // Get all to detect duplicates
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'ASC', // Keep oldest
        ]);
        
        // If duplicates found, delete extras and keep the first one
        if (count($posts) > 1) {
            for ($i = 1; $i < count($posts); $i++) {
                wp_delete_post($posts[$i]->ID, true);
            }
        }
        
        return $posts[0] ?? null;
    }
    
    /**
     * Get item by pointer (and clean up duplicates if found)
     */
    private function get_item_by_pointer($alias, $pointer) {
        $posts = get_posts([
            'post_type' => 'cdm_item',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_cdm_alias',
                    'value' => $alias,
                ],
                [
                    'key' => '_cdm_pointer',
                    'value' => $pointer,
                ],
            ],
            'posts_per_page' => -1, // Get all to detect duplicates
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'ASC', // Keep oldest
        ]);
        
        // If duplicates found, delete extras and keep the first one
        if (count($posts) > 1) {
            for ($i = 1; $i < count($posts); $i++) {
                wp_delete_post($posts[$i]->ID, true);
            }
        }
        
        return $posts[0] ?? null;
    }
}

