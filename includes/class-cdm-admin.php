<?php
/**
 * Admin Panel Handler
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDM_Admin {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_cdm_validate_url', [$this, 'ajax_validate_url']);
        add_action('wp_ajax_cdm_import_collections', [$this, 'ajax_import_collections']);
        add_action('wp_ajax_cdm_import_items', [$this, 'ajax_import_items']);
        add_action('wp_ajax_cdm_get_import_status', [$this, 'ajax_get_import_status']);
        
        // Meta boxes for preview image and editable fields
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes'], 10, 2);
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('CDM Explorer', 'cdm-explorer'),
            __('CDM Explorer', 'cdm-explorer'),
            'manage_options',
            'cdm-explorer',
            [$this, 'render_main_page'],
            'dashicons-database-import',
            30
        );
        
        // Settings submenu
        add_submenu_page(
            'cdm-explorer',
            __('Settings', 'cdm-explorer'),
            __('Settings', 'cdm-explorer'),
            'manage_options',
            'cdm-explorer-settings',
            [$this, 'render_settings_page']
        );
        
        // Import submenu
        add_submenu_page(
            'cdm-explorer',
            __('Import', 'cdm-explorer'),
            __('Import', 'cdm-explorer'),
            'manage_options',
            'cdm-explorer-import',
            [$this, 'render_import_page']
        );
        
        // Shortcodes reference
        add_submenu_page(
            'cdm-explorer',
            __('Shortcodes', 'cdm-explorer'),
            __('Shortcodes', 'cdm-explorer'),
            'manage_options',
            'cdm-explorer-shortcodes',
            [$this, 'render_shortcodes_page']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('cdm_explorer_settings', 'cdm_explorer_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings'],
        ]);
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        $sanitized['cdm_url'] = esc_url_raw($input['cdm_url'] ?? '');
        $sanitized['items_per_page'] = absint($input['items_per_page'] ?? 20);
        $sanitized['enable_cache'] = !empty($input['enable_cache']);
        $sanitized['cache_duration'] = absint($input['cache_duration'] ?? 3600);
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        // Load media uploader on CDM post type edit screens
        if (in_array($hook, ['post.php', 'post-new.php']) && in_array($post_type, ['cdm_collection', 'cdm_item'])) {
            wp_enqueue_media();
            wp_enqueue_style(
                'cdm-explorer-admin',
                CDM_EXPLORER_URL . 'assets/css/admin.css',
                [],
                CDM_EXPLORER_VERSION
            );
            wp_enqueue_script(
                'cdm-explorer-admin',
                CDM_EXPLORER_URL . 'assets/js/admin.js',
                ['jquery'],
                CDM_EXPLORER_VERSION,
                true
            );
            wp_localize_script('cdm-explorer-admin', 'cdmExplorer', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cdm_explorer_nonce'),
                'strings' => [
                    'selectImage' => __('Select Preview Image', 'cdm-explorer'),
                    'useImage' => __('Use this image', 'cdm-explorer'),
                ],
            ]);
            return;
        }
        
        if (strpos($hook, 'cdm-explorer') === false) {
            return;
        }
        
        wp_enqueue_style(
            'cdm-explorer-admin',
            CDM_EXPLORER_URL . 'assets/css/admin.css',
            [],
            CDM_EXPLORER_VERSION
        );
        
        wp_enqueue_script(
            'cdm-explorer-admin',
            CDM_EXPLORER_URL . 'assets/js/admin.js',
            ['jquery'],
            CDM_EXPLORER_VERSION,
            true
        );
        
        wp_localize_script('cdm-explorer-admin', 'cdmExplorer', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cdm_explorer_nonce'),
            'strings' => [
                'validating' => __('Validating...', 'cdm-explorer'),
                'importing' => __('Importing...', 'cdm-explorer'),
                'success' => __('Success!', 'cdm-explorer'),
                'error' => __('Error occurred', 'cdm-explorer'),
                'confirmImport' => __('This will import all items from the selected collections. Continue?', 'cdm-explorer'),
            ],
        ]);
    }
    
    /**
     * Render main admin page
     */
    public function render_main_page() {
        $settings = get_option('cdm_explorer_settings', []);
        $cdm_url = $settings['cdm_url'] ?? '';
        
        include CDM_EXPLORER_PATH . 'templates/admin/main.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $settings = get_option('cdm_explorer_settings', []);
        include CDM_EXPLORER_PATH . 'templates/admin/settings.php';
    }
    
    /**
     * Render import page
     */
    public function render_import_page() {
        $settings = get_option('cdm_explorer_settings', []);
        include CDM_EXPLORER_PATH . 'templates/admin/import.php';
    }
    
    /**
     * Render shortcodes reference page
     */
    public function render_shortcodes_page() {
        include CDM_EXPLORER_PATH . 'templates/admin/shortcodes.php';
    }
    
    /**
     * AJAX: Validate ContentDM URL
     */
    public function ajax_validate_url() {
        check_ajax_referer('cdm_explorer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'cdm-explorer')]);
        }
        
        $url = esc_url_raw($_POST['url'] ?? '');
        
        if (empty($url)) {
            wp_send_json_error(['message' => __('Please enter a URL', 'cdm-explorer')]);
        }
        
        $api = new CDM_API($url);
        $result = $api->validate();
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        // Save the URL
        $settings = get_option('cdm_explorer_settings', []);
        $settings['cdm_url'] = $url;
        update_option('cdm_explorer_settings', $settings);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Import collections
     */
    public function ajax_import_collections() {
        check_ajax_referer('cdm_explorer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'cdm-explorer')]);
        }
        
        $settings = get_option('cdm_explorer_settings', []);
        $cdm_url = $settings['cdm_url'] ?? '';
        
        if (empty($cdm_url)) {
            wp_send_json_error(['message' => __('ContentDM URL not configured', 'cdm-explorer')]);
        }
        
        $importer = new CDM_Importer($cdm_url);
        $result = $importer->import_collections();
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Import items from collection
     */
    public function ajax_import_items() {
        check_ajax_referer('cdm_explorer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'cdm-explorer')]);
        }
        
        $alias = sanitize_text_field($_POST['alias'] ?? '');
        $max_items = absint($_POST['max_items'] ?? 100);
        
        if (empty($alias)) {
            wp_send_json_error(['message' => __('Collection alias required', 'cdm-explorer')]);
        }
        
        $settings = get_option('cdm_explorer_settings', []);
        $cdm_url = $settings['cdm_url'] ?? '';
        
        if (empty($cdm_url)) {
            wp_send_json_error(['message' => __('ContentDM URL not configured', 'cdm-explorer')]);
        }
        
        $importer = new CDM_Importer($cdm_url);
        $result = $importer->import_items($alias, $max_items);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Get import status
     */
    public function ajax_get_import_status() {
        check_ajax_referer('cdm_explorer_nonce', 'nonce');
        
        $collections = wp_count_posts('cdm_collection');
        $items = wp_count_posts('cdm_item');
        
        wp_send_json_success([
            'collections' => $collections->publish ?? 0,
            'items' => $items->publish ?? 0,
        ]);
    }
    
    /**
     * Add meta boxes for CDM post types
     */
    public function add_meta_boxes() {
        // Preview Image meta box for collections
        add_meta_box(
            'cdm_preview_image',
            __('Preview Image', 'cdm-explorer'),
            [$this, 'render_preview_image_meta_box'],
            'cdm_collection',
            'side',
            'default'
        );
        
        // Preview Image meta box for items
        add_meta_box(
            'cdm_preview_image',
            __('Preview Image', 'cdm-explorer'),
            [$this, 'render_preview_image_meta_box'],
            'cdm_item',
            'side',
            'default'
        );
        
        // CDM Fields meta box for collections (editable imported fields)
        add_meta_box(
            'cdm_collection_fields',
            __('Collection Metadata', 'cdm-explorer'),
            [$this, 'render_collection_fields_meta_box'],
            'cdm_collection',
            'normal',
            'default'
        );
        
        // CDM Metadata meta box for items (editable imported metadata)
        add_meta_box(
            'cdm_item_metadata',
            __('Item Metadata', 'cdm-explorer'),
            [$this, 'render_item_metadata_meta_box'],
            'cdm_item',
            'normal',
            'default'
        );
    }
    
    /**
     * Render preview image meta box
     */
    public function render_preview_image_meta_box($post) {
        wp_nonce_field('cdm_preview_image_nonce', 'cdm_preview_image_nonce');
        
        $preview_image = get_post_meta($post->ID, '_cdm_preview_image', true);
        $preview_image_id = get_post_meta($post->ID, '_cdm_preview_image_id', true);
        
        // For items, also get the CDM image URL as fallback display
        $cdm_image_url = '';
        if ($post->post_type === 'cdm_item') {
            $cdm_image_url = get_post_meta($post->ID, '_cdm_image_url', true);
        }
        ?>
        <div class="cdm-preview-image-field">
            <div class="cdm-preview-image-container" style="margin-bottom: 10px;">
                <?php if ($preview_image) : ?>
                    <img src="<?php echo esc_url($preview_image); ?>" alt="" style="max-width: 100%; height: auto; display: block;">
                <?php elseif ($cdm_image_url) : ?>
                    <img src="<?php echo esc_url($cdm_image_url); ?>" alt="" style="max-width: 100%; height: auto; display: block; opacity: 0.6;">
                    <p class="description" style="margin-top: 5px;"><?php _e('Current image from ContentDM', 'cdm-explorer'); ?></p>
                <?php else : ?>
                    <div style="background: #f0f0f1; padding: 20px; text-align: center; color: #646970;">
                        <?php _e('No image set', 'cdm-explorer'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="cdm_preview_image" id="cdm_preview_image" value="<?php echo esc_attr($preview_image); ?>">
            <input type="hidden" name="cdm_preview_image_id" id="cdm_preview_image_id" value="<?php echo esc_attr($preview_image_id); ?>">
            
            <p>
                <button type="button" class="button cdm-upload-preview-image" id="cdm_upload_preview_image">
                    <?php echo $preview_image ? __('Change Image', 'cdm-explorer') : __('Upload Image', 'cdm-explorer'); ?>
                </button>
                <?php if ($preview_image) : ?>
                    <button type="button" class="button cdm-remove-preview-image" id="cdm_remove_preview_image">
                        <?php _e('Remove', 'cdm-explorer'); ?>
                    </button>
                <?php endif; ?>
            </p>
            
            <p class="description">
                <?php _e('Upload a custom preview image for this item. This will override the ContentDM image in displays.', 'cdm-explorer'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render collection fields meta box
     */
    public function render_collection_fields_meta_box($post) {
        wp_nonce_field('cdm_collection_fields_nonce', 'cdm_collection_fields_nonce');
        
        $alias = get_post_meta($post->ID, '_cdm_alias', true);
        $server_url = get_post_meta($post->ID, '_cdm_server_url', true);
        $collection_url = get_post_meta($post->ID, '_cdm_collection_url', true);
        $item_count = get_post_meta($post->ID, '_cdm_item_count', true);
        $fields = get_post_meta($post->ID, '_cdm_fields', true);
        $last_import = get_post_meta($post->ID, '_cdm_last_import', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="cdm_alias"><?php _e('Collection Alias', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="text" name="cdm_alias" id="cdm_alias" value="<?php echo esc_attr($alias); ?>" class="regular-text">
                    <p class="description"><?php _e('The ContentDM collection alias (e.g., "photos")', 'cdm-explorer'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="cdm_server_url"><?php _e('Server URL', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="url" name="cdm_server_url" id="cdm_server_url" value="<?php echo esc_url($server_url); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="cdm_collection_url"><?php _e('Collection URL', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="url" name="cdm_collection_url" id="cdm_collection_url" value="<?php echo esc_url($collection_url); ?>" class="regular-text">
                    <p class="description"><?php _e('Direct link to view this collection on ContentDM', 'cdm-explorer'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="cdm_item_count"><?php _e('Item Count', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="number" name="cdm_item_count" id="cdm_item_count" value="<?php echo esc_attr($item_count); ?>" class="small-text" min="0">
                </td>
            </tr>
            <?php if ($last_import) : ?>
            <tr>
                <th><?php _e('Last Import', 'cdm-explorer'); ?></th>
                <td><?php echo esc_html($last_import); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($fields) && is_array($fields)) : ?>
            <tr>
                <th><?php _e('Available Fields', 'cdm-explorer'); ?></th>
                <td>
                    <div style="max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">
                        <?php foreach ($fields as $field) : ?>
                            <code style="display: inline-block; margin: 2px 5px 2px 0; padding: 2px 6px; background: #fff; border: 1px solid #ddd; border-radius: 3px;">
                                <?php echo esc_html($field['nick'] ?? $field['name'] ?? 'unknown'); ?>
                            </code>
                        <?php endforeach; ?>
                    </div>
                    <p class="description"><?php _e('These fields are available in this collection from ContentDM.', 'cdm-explorer'); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }
    
    /**
     * Render item metadata meta box
     */
    public function render_item_metadata_meta_box($post) {
        wp_nonce_field('cdm_item_metadata_nonce', 'cdm_item_metadata_nonce');
        
        $pointer = get_post_meta($post->ID, '_cdm_pointer', true);
        $alias = get_post_meta($post->ID, '_cdm_alias', true);
        $server_url = get_post_meta($post->ID, '_cdm_server_url', true);
        $item_url = get_post_meta($post->ID, '_cdm_item_url', true);
        $image_url = get_post_meta($post->ID, '_cdm_image_url', true);
        $image_width = get_post_meta($post->ID, '_cdm_image_width', true);
        $image_height = get_post_meta($post->ID, '_cdm_image_height', true);
        $metadata = get_post_meta($post->ID, '_cdm_metadata', true);
        $last_import = get_post_meta($post->ID, '_cdm_last_import', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="cdm_pointer"><?php _e('CDM Pointer', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="text" name="cdm_pointer" id="cdm_pointer" value="<?php echo esc_attr($pointer); ?>" class="regular-text">
                    <p class="description"><?php _e('The ContentDM item pointer/ID', 'cdm-explorer'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="cdm_item_alias"><?php _e('Collection Alias', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="text" name="cdm_item_alias" id="cdm_item_alias" value="<?php echo esc_attr($alias); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="cdm_item_url"><?php _e('Item URL', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="url" name="cdm_item_url" id="cdm_item_url" value="<?php echo esc_url($item_url); ?>" class="regular-text">
                    <p class="description"><?php _e('Direct link to view this item on ContentDM', 'cdm-explorer'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="cdm_image_url"><?php _e('CDM Image URL', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="url" name="cdm_image_url" id="cdm_image_url" value="<?php echo esc_url($image_url); ?>" class="regular-text">
                    <p class="description"><?php _e('ContentDM image URL. Use the Preview Image field above to override.', 'cdm-explorer'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Image Dimensions', 'cdm-explorer'); ?></label></th>
                <td>
                    <input type="number" name="cdm_image_width" value="<?php echo esc_attr($image_width); ?>" class="small-text" min="0" placeholder="Width"> x 
                    <input type="number" name="cdm_image_height" value="<?php echo esc_attr($image_height); ?>" class="small-text" min="0" placeholder="Height"> px
                </td>
            </tr>
            <?php if ($last_import) : ?>
            <tr>
                <th><?php _e('Last Import', 'cdm-explorer'); ?></th>
                <td><?php echo esc_html($last_import); ?></td>
            </tr>
            <?php endif; ?>
        </table>
        
        <?php if (!empty($metadata) && is_array($metadata)) : ?>
        <h4 style="margin-top: 20px;"><?php _e('Raw Metadata from ContentDM', 'cdm-explorer'); ?></h4>
        <div style="max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;">
            <table class="widefat striped" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 30%;"><?php _e('Field', 'cdm-explorer'); ?></th>
                        <th><?php _e('Value', 'cdm-explorer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metadata as $key => $value) : 
                        if (is_array($value)) continue;
                        if (empty($value)) continue;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($key); ?></strong></td>
                        <td><?php echo esc_html(substr($value, 0, 200)); echo strlen($value) > 200 ? '...' : ''; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="description"><?php _e('This is read-only imported data. Edit the main content above to customize the display.', 'cdm-explorer'); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id, $post) {
        // Skip autosaves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save preview image
        if (isset($_POST['cdm_preview_image_nonce']) && wp_verify_nonce($_POST['cdm_preview_image_nonce'], 'cdm_preview_image_nonce')) {
            if (isset($_POST['cdm_preview_image'])) {
                update_post_meta($post_id, '_cdm_preview_image', esc_url_raw($_POST['cdm_preview_image']));
            }
            if (isset($_POST['cdm_preview_image_id'])) {
                update_post_meta($post_id, '_cdm_preview_image_id', absint($_POST['cdm_preview_image_id']));
            }
        }
        
        // Save collection fields
        if ($post->post_type === 'cdm_collection' && isset($_POST['cdm_collection_fields_nonce']) && wp_verify_nonce($_POST['cdm_collection_fields_nonce'], 'cdm_collection_fields_nonce')) {
            if (isset($_POST['cdm_alias'])) {
                update_post_meta($post_id, '_cdm_alias', sanitize_text_field($_POST['cdm_alias']));
            }
            if (isset($_POST['cdm_server_url'])) {
                update_post_meta($post_id, '_cdm_server_url', esc_url_raw($_POST['cdm_server_url']));
            }
            if (isset($_POST['cdm_collection_url'])) {
                update_post_meta($post_id, '_cdm_collection_url', esc_url_raw($_POST['cdm_collection_url']));
            }
            if (isset($_POST['cdm_item_count'])) {
                update_post_meta($post_id, '_cdm_item_count', absint($_POST['cdm_item_count']));
            }
        }
        
        // Save item metadata
        if ($post->post_type === 'cdm_item' && isset($_POST['cdm_item_metadata_nonce']) && wp_verify_nonce($_POST['cdm_item_metadata_nonce'], 'cdm_item_metadata_nonce')) {
            if (isset($_POST['cdm_pointer'])) {
                update_post_meta($post_id, '_cdm_pointer', sanitize_text_field($_POST['cdm_pointer']));
            }
            if (isset($_POST['cdm_item_alias'])) {
                update_post_meta($post_id, '_cdm_alias', sanitize_text_field($_POST['cdm_item_alias']));
            }
            if (isset($_POST['cdm_item_url'])) {
                update_post_meta($post_id, '_cdm_item_url', esc_url_raw($_POST['cdm_item_url']));
            }
            if (isset($_POST['cdm_image_url'])) {
                update_post_meta($post_id, '_cdm_image_url', esc_url_raw($_POST['cdm_image_url']));
            }
            if (isset($_POST['cdm_image_width'])) {
                update_post_meta($post_id, '_cdm_image_width', absint($_POST['cdm_image_width']));
            }
            if (isset($_POST['cdm_image_height'])) {
                update_post_meta($post_id, '_cdm_image_height', absint($_POST['cdm_image_height']));
            }
        }
    }
}

