<?php
/**
 * Single Collection Template
 * 
 * @package CDM_Explorer
 */

get_header();

while (have_posts()) : the_post();
    $alias = get_post_meta(get_the_ID(), '_cdm_alias', true);
    $item_count = get_post_meta(get_the_ID(), '_cdm_item_count', true);
    $item_count = is_numeric($item_count) ? (int) $item_count : 0;
    $collection_url = get_post_meta(get_the_ID(), '_cdm_collection_url', true);
    $fields = get_post_meta(get_the_ID(), '_cdm_fields', true);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('cdm-single-collection'); ?>>
    <header class="cdm-single-collection__header">
        <h1 class="cdm-single-collection__title"><?php the_title(); ?></h1>
        
        <div class="cdm-single-collection__meta">
            <span class="cdm-meta-item">
                <strong><?php _e('Items:', 'cdm-explorer'); ?></strong> 
                <?php echo number_format_i18n($item_count); ?>
            </span>
            <span class="cdm-meta-item">
                <strong><?php _e('Alias:', 'cdm-explorer'); ?></strong> 
                <code><?php echo esc_html($alias); ?></code>
            </span>
            <?php if ($collection_url) : ?>
                <a href="<?php echo esc_url($collection_url); ?>" class="cdm-btn cdm-btn--secondary" target="_blank">
                    <?php _e('View on ContentDM', 'cdm-explorer'); ?> →
                </a>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="cdm-single-collection__content">
        <?php the_content(); ?>
    </div>
    
    <?php if (!empty($fields) && is_array($fields)) : ?>
    <section class="cdm-single-collection__fields">
        <h2><?php _e('Collection Fields', 'cdm-explorer'); ?></h2>
        <table class="cdm-fields-table">
            <thead>
                <tr>
                    <th><?php _e('Field Name', 'cdm-explorer'); ?></th>
                    <th><?php _e('Nickname', 'cdm-explorer'); ?></th>
                    <th><?php _e('Type', 'cdm-explorer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $field) : ?>
                <tr>
                    <td><?php echo esc_html($field['name'] ?? ''); ?></td>
                    <td><code><?php echo esc_html($field['nick'] ?? ''); ?></code></td>
                    <td><?php echo esc_html($field['type'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>
    
    <section class="cdm-single-collection__items">
        <h2><?php _e('Items in this Collection', 'cdm-explorer'); ?></h2>
        <?php echo do_shortcode('[cdm_items collection="' . esc_attr($alias) . '" columns="4" limit="20"]'); ?>
        
        <p class="cdm-view-all">
            <a href="<?php echo esc_url(get_post_type_archive_link('cdm_item') . '?cdm_collection=' . $alias); ?>" class="cdm-btn cdm-btn--primary">
                <?php _e('View All Items', 'cdm-explorer'); ?>
            </a>
        </p>
    </section>
</article>

<?php
endwhile;

get_footer();

