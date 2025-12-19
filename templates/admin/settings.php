<?php
/**
 * Admin Settings Page Template
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cdm-admin">
    <h1 class="cdm-admin__title">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php _e('CDM Explorer Settings', 'cdm-explorer'); ?>
    </h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('cdm_explorer_settings'); ?>
        
        <div class="cdm-card">
            <h2 class="cdm-card__title"><?php _e('Server Configuration', 'cdm-explorer'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cdm_url"><?php _e('ContentDM URL', 'cdm-explorer'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="cdm_url" name="cdm_explorer_settings[cdm_url]" 
                               value="<?php echo esc_attr($settings['cdm_url'] ?? ''); ?>" 
                               class="regular-text"
                               placeholder="https://cdm12345.contentdm.oclc.org">
                        <p class="description">
                            <?php _e('Your ContentDM server URL', 'cdm-explorer'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="cdm-card">
            <h2 class="cdm-card__title"><?php _e('Display Settings', 'cdm-explorer'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="items_per_page"><?php _e('Items per Page', 'cdm-explorer'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="items_per_page" name="cdm_explorer_settings[items_per_page]" 
                               value="<?php echo esc_attr($settings['items_per_page'] ?? 20); ?>" 
                               class="small-text" min="1" max="100">
                        <p class="description">
                            <?php _e('Number of items to display per page in archives', 'cdm-explorer'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="cdm-card">
            <h2 class="cdm-card__title"><?php _e('Cache Settings', 'cdm-explorer'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Cache', 'cdm-explorer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cdm_explorer_settings[enable_cache]" 
                                   value="1" <?php checked(!empty($settings['enable_cache'])); ?>>
                            <?php _e('Cache API responses for better performance', 'cdm-explorer'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cache_duration"><?php _e('Cache Duration', 'cdm-explorer'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="cache_duration" name="cdm_explorer_settings[cache_duration]" 
                               value="<?php echo esc_attr($settings['cache_duration'] ?? 3600); ?>" 
                               class="small-text" min="60" max="86400">
                        <?php _e('seconds', 'cdm-explorer'); ?>
                        <p class="description">
                            <?php _e('How long to cache API responses (60-86400 seconds)', 'cdm-explorer'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(__('Save Settings', 'cdm-explorer')); ?>
    </form>
</div>

