<?php
/**
 * Admin Shortcodes Reference Page
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cdm-admin">
    <h1 class="cdm-admin__title">
        <span class="dashicons dashicons-shortcode"></span>
        <?php _e('Shortcode Reference', 'cdm-explorer'); ?>
    </h1>
    
    <p class="cdm-admin__description">
        <?php _e('Use these shortcodes to display ContentDM content on your WordPress site.', 'cdm-explorer'); ?>
    </p>
    
    <!-- Collections Shortcode -->
    <div class="cdm-card">
        <h2 class="cdm-card__title">[cdm_collections]</h2>
        <p class="cdm-card__desc"><?php _e('Display a grid of imported collections.', 'cdm-explorer'); ?></p>
        
        <h4><?php _e('Attributes', 'cdm-explorer'); ?></h4>
        <table class="widefat cdm-shortcode-table">
            <thead>
                <tr>
                    <th><?php _e('Attribute', 'cdm-explorer'); ?></th>
                    <th><?php _e('Default', 'cdm-explorer'); ?></th>
                    <th><?php _e('Description', 'cdm-explorer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>columns</code></td><td>3</td><td><?php _e('Number of grid columns', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>limit</code></td><td>-1 (all)</td><td><?php _e('Number of collections to show', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>orderby</code></td><td>title</td><td><?php _e('Order by: title, date, menu_order', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>order</code></td><td>ASC</td><td><?php _e('Sort order: ASC or DESC', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>style</code></td><td>card</td><td><?php _e('Display style: card, list, minimal', 'cdm-explorer'); ?></td></tr>
            </tbody>
        </table>
        
        <h4><?php _e('Example', 'cdm-explorer'); ?></h4>
        <pre class="cdm-code">[cdm_collections columns="4" limit="8" style="card"]</pre>
    </div>
    
    <!-- Items Shortcode -->
    <div class="cdm-card">
        <h2 class="cdm-card__title">[cdm_items]</h2>
        <p class="cdm-card__desc"><?php _e('Display a grid of items from collections.', 'cdm-explorer'); ?></p>
        
        <h4><?php _e('Attributes', 'cdm-explorer'); ?></h4>
        <table class="widefat cdm-shortcode-table">
            <thead>
                <tr>
                    <th><?php _e('Attribute', 'cdm-explorer'); ?></th>
                    <th><?php _e('Default', 'cdm-explorer'); ?></th>
                    <th><?php _e('Description', 'cdm-explorer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>collection</code></td><td>(all)</td><td><?php _e('Filter by collection alias', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>columns</code></td><td>4</td><td><?php _e('Number of grid columns', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>limit</code></td><td>20</td><td><?php _e('Number of items to show', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>orderby</code></td><td>date</td><td><?php _e('Order by: title, date, rand', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>order</code></td><td>DESC</td><td><?php _e('Sort order: ASC or DESC', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>style</code></td><td>grid</td><td><?php _e('Display style: grid, list, masonry', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>show_image</code></td><td>true</td><td><?php _e('Show item thumbnails', 'cdm-explorer'); ?></td></tr>
            </tbody>
        </table>
        
        <h4><?php _e('Example', 'cdm-explorer'); ?></h4>
        <pre class="cdm-code">[cdm_items collection="photos" columns="3" limit="12"]</pre>
    </div>
    
    <!-- Single Item Shortcode -->
    <div class="cdm-card">
        <h2 class="cdm-card__title">[cdm_item]</h2>
        <p class="cdm-card__desc"><?php _e('Display a single item with its metadata.', 'cdm-explorer'); ?></p>
        
        <h4><?php _e('Attributes', 'cdm-explorer'); ?></h4>
        <table class="widefat cdm-shortcode-table">
            <thead>
                <tr>
                    <th><?php _e('Attribute', 'cdm-explorer'); ?></th>
                    <th><?php _e('Default', 'cdm-explorer'); ?></th>
                    <th><?php _e('Description', 'cdm-explorer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>id</code></td><td>(required)</td><td><?php _e('WordPress post ID or CDM pointer', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>collection</code></td><td></td><td><?php _e('Collection alias (required if using pointer)', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>show_image</code></td><td>true</td><td><?php _e('Show item image', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>show_metadata</code></td><td>true</td><td><?php _e('Show metadata fields', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>show_link</code></td><td>true</td><td><?php _e('Show link to ContentDM', 'cdm-explorer'); ?></td></tr>
            </tbody>
        </table>
        
        <h4><?php _e('Example', 'cdm-explorer'); ?></h4>
        <pre class="cdm-code">[cdm_item id="123" show_image="true" show_metadata="true"]</pre>
    </div>
    
    <!-- Gallery Shortcode -->
    <div class="cdm-card">
        <h2 class="cdm-card__title">[cdm_gallery]</h2>
        <p class="cdm-card__desc"><?php _e('Display a lightbox-enabled image gallery.', 'cdm-explorer'); ?></p>
        
        <h4><?php _e('Attributes', 'cdm-explorer'); ?></h4>
        <table class="widefat cdm-shortcode-table">
            <thead>
                <tr>
                    <th><?php _e('Attribute', 'cdm-explorer'); ?></th>
                    <th><?php _e('Default', 'cdm-explorer'); ?></th>
                    <th><?php _e('Description', 'cdm-explorer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>collection</code></td><td></td><td><?php _e('Filter by collection alias', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>columns</code></td><td>4</td><td><?php _e('Number of columns', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>limit</code></td><td>20</td><td><?php _e('Number of images', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>lightbox</code></td><td>true</td><td><?php _e('Enable lightbox on click', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>captions</code></td><td>true</td><td><?php _e('Show image captions', 'cdm-explorer'); ?></td></tr>
            </tbody>
        </table>
        
        <h4><?php _e('Example', 'cdm-explorer'); ?></h4>
        <pre class="cdm-code">[cdm_gallery collection="photos" columns="5" lightbox="true"]</pre>
    </div>
    
    <!-- Search Shortcode -->
    <div class="cdm-card">
        <h2 class="cdm-card__title">[cdm_search]</h2>
        <p class="cdm-card__desc"><?php _e('Display a search form for items.', 'cdm-explorer'); ?></p>
        
        <h4><?php _e('Attributes', 'cdm-explorer'); ?></h4>
        <table class="widefat cdm-shortcode-table">
            <thead>
                <tr>
                    <th><?php _e('Attribute', 'cdm-explorer'); ?></th>
                    <th><?php _e('Default', 'cdm-explorer'); ?></th>
                    <th><?php _e('Description', 'cdm-explorer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>placeholder</code></td><td>Search items...</td><td><?php _e('Input placeholder text', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>collection</code></td><td></td><td><?php _e('Limit search to collection', 'cdm-explorer'); ?></td></tr>
                <tr><td><code>button_text</code></td><td>Search</td><td><?php _e('Submit button text', 'cdm-explorer'); ?></td></tr>
            </tbody>
        </table>
        
        <h4><?php _e('Example', 'cdm-explorer'); ?></h4>
        <pre class="cdm-code">[cdm_search placeholder="Search the archive..." button_text="Find"]</pre>
    </div>
    
    <p class="cdm-admin__footer">
        <?php _e('Developed independently by', 'cdm-explorer'); ?> 
        <a href="https://ca11.tech/" target="_blank">Christian Abou Daher</a>
    </p>
</div>

