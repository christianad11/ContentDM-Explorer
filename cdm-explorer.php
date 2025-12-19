<?php
/**
 * Plugin Name: ContentDM Explorer
 * Plugin URI: https://ca11.tech/
 * Description: Import and display ContentDM digital collections in WordPress. Creates CPTs for collections and items with shortcodes.
 * Version: 1.0.0-beta
 * Author: Christian Abou Daher
 * Author URI: https://ca11.tech/
 * License: GPL v2 or later
 * Text Domain: cdm-explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('CDM_EXPLORER_VERSION', '1.0.0-beta');
define('CDM_EXPLORER_PATH', plugin_dir_path(__FILE__));
define('CDM_EXPLORER_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class CDM_Explorer {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
        
        // Initialize CPT early so hooks are registered before 'init' fires
        CDM_CPT::instance();
    }
    
    private function includes() {
        require_once CDM_EXPLORER_PATH . 'includes/class-cdm-api.php';
        require_once CDM_EXPLORER_PATH . 'includes/class-cdm-cpt.php';
        require_once CDM_EXPLORER_PATH . 'includes/class-cdm-admin.php';
        require_once CDM_EXPLORER_PATH . 'includes/class-cdm-importer.php';
        require_once CDM_EXPLORER_PATH . 'includes/class-cdm-shortcodes.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }
    
    public function init() {
        // Initialize other components (CPT already initialized in constructor)
        CDM_Admin::instance();
        CDM_Shortcodes::instance();
        
        // Load text domain
        load_plugin_textdomain('cdm-explorer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        // Ensure CPT is registered before flushing
        CDM_CPT::instance()->register_post_types();
        CDM_CPT::instance()->register_taxonomies();
        flush_rewrite_rules();
        
        // Set default options
        if (!get_option('cdm_explorer_settings')) {
            update_option('cdm_explorer_settings', [
                'cdm_url' => '',
                'items_per_page' => 20,
                'enable_cache' => true,
                'cache_duration' => 3600,
            ]);
        }
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'cdm-explorer-frontend',
            CDM_EXPLORER_URL . 'assets/css/frontend.css',
            [],
            CDM_EXPLORER_VERSION
        );
    }
}

// Initialize plugin
function cdm_explorer() {
    return CDM_Explorer::instance();
}

add_action('plugins_loaded', 'cdm_explorer');

