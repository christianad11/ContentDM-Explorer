<?php
/**
 * Admin Import Page Template
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

$cdm_url = $settings['cdm_url'] ?? '';
?>
<div class="wrap cdm-admin">
    <h1 class="cdm-admin__title">
        <span class="dashicons dashicons-download"></span>
        <?php _e('Import from ContentDM', 'cdm-explorer'); ?>
    </h1>
    
    <?php if (empty($cdm_url)) : ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('Please configure your ContentDM URL in Settings before importing.', 'cdm-explorer'); ?>
                <a href="<?php echo admin_url('admin.php?page=cdm-explorer-settings'); ?>">
                    <?php _e('Go to Settings', 'cdm-explorer'); ?>
                </a>
            </p>
        </div>
    <?php else : ?>
    
    <!-- Server Info -->
    <div class="cdm-card">
        <h2 class="cdm-card__title"><?php _e('Connected Server', 'cdm-explorer'); ?></h2>
        <p><code><?php echo esc_html($cdm_url); ?></code></p>
    </div>
    
    <!-- Import Collections -->
    <div class="cdm-card">
        <h2 class="cdm-card__title"><?php _e('Step 1: Import Collections', 'cdm-explorer'); ?></h2>
        <p class="cdm-card__desc">
            <?php _e('Import the list of collections from your ContentDM server. This creates collection posts in WordPress.', 'cdm-explorer'); ?>
        </p>
        
        <button type="button" id="import-collections-btn" class="button button-primary button-large">
            <span class="dashicons dashicons-category"></span>
            <?php _e('Import Collections', 'cdm-explorer'); ?>
        </button>
        
        <div id="import-collections-status" class="cdm-import-status" style="display: none;">
            <div class="cdm-progress">
                <div class="cdm-progress__bar"></div>
            </div>
            <p class="cdm-import-status__text"></p>
        </div>
    </div>
    
    <!-- Import Items -->
    <div class="cdm-card">
        <h2 class="cdm-card__title"><?php _e('Step 2: Import Items', 'cdm-explorer'); ?></h2>
        <p class="cdm-card__desc">
            <?php _e('Select collections to import items from. Each item will be created as a WordPress post with metadata.', 'cdm-explorer'); ?>
        </p>
        
        <div id="collections-list" class="cdm-collections-list">
            <?php
            $collections = get_posts([
                'post_type' => 'cdm_collection',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
            ]);
            
            if (empty($collections)) :
            ?>
                <p class="cdm-no-data"><?php _e('No collections imported yet. Import collections first.', 'cdm-explorer'); ?></p>
            <?php else : ?>
                <table class="widefat cdm-import-table">
                    <thead>
                        <tr>
                            <th class="check-column"><input type="checkbox" id="select-all-collections"></th>
                            <th><?php _e('Collection', 'cdm-explorer'); ?></th>
                            <th><?php _e('Alias', 'cdm-explorer'); ?></th>
                            <th><?php _e('Total Items', 'cdm-explorer'); ?></th>
                            <th><?php _e('Imported', 'cdm-explorer'); ?></th>
                            <th><?php _e('Actions', 'cdm-explorer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($collections as $collection) : 
                            $alias = get_post_meta($collection->ID, '_cdm_alias', true);
                            $item_count = get_post_meta($collection->ID, '_cdm_item_count', true);
                            $imported = get_post_meta($collection->ID, '_cdm_imported_items', true);
                            
                            // Ensure numeric values
                            $item_count = is_numeric($item_count) ? (int) $item_count : 0;
                            $imported = is_numeric($imported) ? (int) $imported : 0;
                        ?>
                            <tr data-alias="<?php echo esc_attr($alias); ?>">
                                <td><input type="checkbox" name="collections[]" value="<?php echo esc_attr($alias); ?>"></td>
                                <td><strong><?php echo esc_html($collection->post_title); ?></strong></td>
                                <td><code><?php echo esc_html($alias); ?></code></td>
                                <td><?php echo number_format_i18n($item_count); ?></td>
                                <td class="imported-count"><?php echo number_format_i18n($imported); ?></td>
                                <td>
                                    <button type="button" class="button import-items-btn" data-alias="<?php echo esc_attr($alias); ?>">
                                        <?php _e('Import Items', 'cdm-explorer'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cdm-import-options">
                    <label>
                        <?php _e('Max items per collection:', 'cdm-explorer'); ?>
                        <select id="max-items">
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="250">250</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                        </select>
                    </label>
                    
                    <button type="button" id="import-selected-btn" class="button button-primary" disabled>
                        <?php _e('Import Selected Collections', 'cdm-explorer'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="import-items-status" class="cdm-import-status" style="display: none;">
            <div class="cdm-progress">
                <div class="cdm-progress__bar"></div>
            </div>
            <p class="cdm-import-status__text"></p>
        </div>
    </div>
    
    <?php endif; ?>
</div>

