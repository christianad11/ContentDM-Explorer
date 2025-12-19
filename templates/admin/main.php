<?php
/**
 * Admin Main Page Template
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cdm-admin">
    <h1 class="cdm-admin__title">
        <span class="dashicons dashicons-database-import"></span>
        <?php _e('ContentDM Explorer', 'cdm-explorer'); ?>
        <span class="cdm-badge cdm-badge--beta">BETA</span>
    </h1>
    
    <div class="cdm-admin__header">
        <p class="cdm-admin__description">
            <?php _e('Import and manage digital collections from ContentDM in WordPress.', 'cdm-explorer'); ?>
        </p>
    </div>
    
    <!-- Connection Status -->
    <div class="cdm-card">
        <h2 class="cdm-card__title"><?php _e('Connection Status', 'cdm-explorer'); ?></h2>
        
        <div class="cdm-connection-form">
            <div class="cdm-form-group">
                <label for="cdm-url"><?php _e('ContentDM Server URL', 'cdm-explorer'); ?></label>
                <div class="cdm-input-group">
                    <input type="url" id="cdm-url" class="cdm-input" 
                           value="<?php echo esc_attr($cdm_url); ?>"
                           placeholder="https://cdm12345.contentdm.oclc.org">
                    <button type="button" id="cdm-validate-btn" class="button button-primary">
                        <?php _e('Connect', 'cdm-explorer'); ?>
                    </button>
                </div>
                <p class="cdm-form-hint">
                    <?php _e('Enter your ContentDM instance URL to connect and import collections.', 'cdm-explorer'); ?>
                </p>
            </div>
            
            <div id="cdm-connection-status" class="cdm-status" style="display: none;">
                <span class="cdm-status__icon"></span>
                <span class="cdm-status__text"></span>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="cdm-stats-grid">
        <div class="cdm-stat-card">
            <div class="cdm-stat-card__icon">
                <span class="dashicons dashicons-category"></span>
            </div>
            <div class="cdm-stat-card__content">
                <span class="cdm-stat-card__number" id="stat-collections">
                    <?php echo wp_count_posts('cdm_collection')->publish ?? 0; ?>
                </span>
                <span class="cdm-stat-card__label"><?php _e('Collections', 'cdm-explorer'); ?></span>
            </div>
        </div>
        
        <div class="cdm-stat-card">
            <div class="cdm-stat-card__icon">
                <span class="dashicons dashicons-format-image"></span>
            </div>
            <div class="cdm-stat-card__content">
                <span class="cdm-stat-card__number" id="stat-items">
                    <?php echo wp_count_posts('cdm_item')->publish ?? 0; ?>
                </span>
                <span class="cdm-stat-card__label"><?php _e('Items', 'cdm-explorer'); ?></span>
            </div>
        </div>
        
        <div class="cdm-stat-card">
            <div class="cdm-stat-card__icon">
                <span class="dashicons dashicons-admin-links"></span>
            </div>
            <div class="cdm-stat-card__content">
                <span class="cdm-stat-card__number">
                    <?php echo $cdm_url ? '✓' : '—'; ?>
                </span>
                <span class="cdm-stat-card__label"><?php _e('Server Status', 'cdm-explorer'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="cdm-card">
        <h2 class="cdm-card__title"><?php _e('Quick Actions', 'cdm-explorer'); ?></h2>
        <div class="cdm-actions-grid">
            <a href="<?php echo admin_url('admin.php?page=cdm-explorer-import'); ?>" class="cdm-action-card">
                <span class="dashicons dashicons-download"></span>
                <span class="cdm-action-card__title"><?php _e('Import Data', 'cdm-explorer'); ?></span>
                <span class="cdm-action-card__desc"><?php _e('Import collections and items', 'cdm-explorer'); ?></span>
            </a>
            
            <a href="<?php echo admin_url('edit.php?post_type=cdm_collection'); ?>" class="cdm-action-card">
                <span class="dashicons dashicons-category"></span>
                <span class="cdm-action-card__title"><?php _e('View Collections', 'cdm-explorer'); ?></span>
                <span class="cdm-action-card__desc"><?php _e('Manage imported collections', 'cdm-explorer'); ?></span>
            </a>
            
            <a href="<?php echo admin_url('edit.php?post_type=cdm_item'); ?>" class="cdm-action-card">
                <span class="dashicons dashicons-format-image"></span>
                <span class="cdm-action-card__title"><?php _e('View Items', 'cdm-explorer'); ?></span>
                <span class="cdm-action-card__desc"><?php _e('Browse imported items', 'cdm-explorer'); ?></span>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=cdm-explorer-shortcodes'); ?>" class="cdm-action-card">
                <span class="dashicons dashicons-shortcode"></span>
                <span class="cdm-action-card__title"><?php _e('Shortcodes', 'cdm-explorer'); ?></span>
                <span class="cdm-action-card__desc"><?php _e('Display content on your site', 'cdm-explorer'); ?></span>
            </a>
        </div>
    </div>
    
    <p class="cdm-admin__footer">
        <?php _e('Developed independently by', 'cdm-explorer'); ?> 
        <a href="https://ca11.tech/" target="_blank">Christian Abou Daher</a>
    </p>
</div>

