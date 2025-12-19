<?php
/**
 * Custom Post Types Registration
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDM_CPT {
    
    private static $instance = null;
    private static $initialized = false;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Check if init has already fired
        if (did_action('init')) {
            // Init already fired, register immediately
            $this->register_post_types();
            $this->register_taxonomies();
        } else {
            // Init hasn't fired yet, hook into it
            add_action('init', [$this, 'register_post_types'], 5);
            add_action('init', [$this, 'register_taxonomies'], 5);
        }
        
        add_filter('single_template', [$this, 'load_single_templates']);
        add_filter('archive_template', [$this, 'load_archive_templates']);
    }
    
    /**
     * Register Custom Post Types
     */
    public function register_post_types() {
        // Prevent double registration
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        // CDM Collection CPT
        register_post_type('cdm_collection', [
            'labels' => [
                'name'               => __('CDM Collections', 'cdm-explorer'),
                'singular_name'      => __('CDM Collection', 'cdm-explorer'),
                'menu_name'          => __('CDM Collections', 'cdm-explorer'),
                'add_new'            => __('Add New', 'cdm-explorer'),
                'add_new_item'       => __('Add New Collection', 'cdm-explorer'),
                'edit_item'          => __('Edit Collection', 'cdm-explorer'),
                'view_item'          => __('View Collection', 'cdm-explorer'),
                'all_items'          => __('All Collections', 'cdm-explorer'),
                'search_items'       => __('Search Collections', 'cdm-explorer'),
                'not_found'          => __('No collections found', 'cdm-explorer'),
            ],
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'cdm-explorer',
            'show_in_rest'       => true,
            'capability_type'    => 'post',
            'has_archive'        => 'cdm-collections',
            'rewrite'            => ['slug' => 'cdm-collection', 'with_front' => false],
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon'          => 'dashicons-category',
        ]);
        
        // CDM Item CPT
        register_post_type('cdm_item', [
            'labels' => [
                'name'               => __('CDM Items', 'cdm-explorer'),
                'singular_name'      => __('CDM Item', 'cdm-explorer'),
                'menu_name'          => __('CDM Items', 'cdm-explorer'),
                'add_new'            => __('Add New', 'cdm-explorer'),
                'add_new_item'       => __('Add New Item', 'cdm-explorer'),
                'edit_item'          => __('Edit Item', 'cdm-explorer'),
                'view_item'          => __('View Item', 'cdm-explorer'),
                'all_items'          => __('All Items', 'cdm-explorer'),
                'search_items'       => __('Search Items', 'cdm-explorer'),
                'not_found'          => __('No items found', 'cdm-explorer'),
            ],
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'cdm-explorer',
            'show_in_rest'       => true,
            'capability_type'    => 'post',
            'has_archive'        => 'cdm-items',
            'rewrite'            => ['slug' => 'cdm-item', 'with_front' => false],
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon'          => 'dashicons-format-image',
        ]);
    }
    
    /**
     * Register Taxonomies
     */
    public function register_taxonomies() {
        // Prevent double registration
        if (taxonomy_exists('cdm_collection_tax')) {
            return;
        }
        
        // Collection taxonomy for items
        register_taxonomy('cdm_collection_tax', 'cdm_item', [
            'labels' => [
                'name'              => __('Collections', 'cdm-explorer'),
                'singular_name'     => __('Collection', 'cdm-explorer'),
                'search_items'      => __('Search Collections', 'cdm-explorer'),
                'all_items'         => __('All Collections', 'cdm-explorer'),
                'edit_item'         => __('Edit Collection', 'cdm-explorer'),
                'update_item'       => __('Update Collection', 'cdm-explorer'),
                'add_new_item'      => __('Add New Collection', 'cdm-explorer'),
                'new_item_name'     => __('New Collection Name', 'cdm-explorer'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'cdm-collections'],
        ]);
    }
    
    /**
     * Load custom single templates
     */
    public function load_single_templates($template) {
        global $post;
        
        if ($post->post_type === 'cdm_collection') {
            $custom = CDM_EXPLORER_PATH . 'templates/single-cdm_collection.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }
        
        if ($post->post_type === 'cdm_item') {
            $custom = CDM_EXPLORER_PATH . 'templates/single-cdm_item.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }
        
        return $template;
    }
    
    /**
     * Load custom archive templates
     */
    public function load_archive_templates($template) {
        if (is_post_type_archive('cdm_collection')) {
            $custom = CDM_EXPLORER_PATH . 'templates/archive-cdm_collection.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }
        
        if (is_post_type_archive('cdm_item')) {
            $custom = CDM_EXPLORER_PATH . 'templates/archive-cdm_item.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }
        
        return $template;
    }
}

