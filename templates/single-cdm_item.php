<?php
/**
 * Single Item Template
 * 
 * @package CDM_Explorer
 */

get_header();

while (have_posts()) : the_post();
    $alias = get_post_meta(get_the_ID(), '_cdm_alias', true);
    $pointer = get_post_meta(get_the_ID(), '_cdm_pointer', true);
    // Use preview image if set, otherwise fall back to CDM image
    $preview_image = get_post_meta(get_the_ID(), '_cdm_preview_image', true);
    $cdm_image_url = get_post_meta(get_the_ID(), '_cdm_image_url', true);
    $image_url = $preview_image ?: $cdm_image_url;
    $item_url = get_post_meta(get_the_ID(), '_cdm_item_url', true);
    $width = get_post_meta(get_the_ID(), '_cdm_image_width', true);
    $height = get_post_meta(get_the_ID(), '_cdm_image_height', true);
    $metadata = get_post_meta(get_the_ID(), '_cdm_metadata', true);
    
    // Get collection info
    $collection = get_posts([
        'post_type' => 'cdm_collection',
        'meta_key' => '_cdm_alias',
        'meta_value' => $alias,
        'posts_per_page' => 1,
    ]);
    $collection = $collection[0] ?? null;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('cdm-single-item'); ?>>
    
    <div class="cdm-single-item__layout">
        <!-- Image Column -->
        <div class="cdm-single-item__image-col">
            <?php if ($image_url) : ?>
                <figure class="cdm-single-item__figure">
                    <a href="<?php echo esc_url($image_url); ?>" target="_blank">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>">
                    </a>
                    <?php if ($width && $height) : ?>
                        <figcaption class="cdm-single-item__dimensions">
                            <?php printf('%d × %d pixels', $width, $height); ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>
        </div>
        
        <!-- Content Column -->
        <div class="cdm-single-item__content-col">
            <header class="cdm-single-item__header">
                <?php if ($collection) : ?>
                    <nav class="cdm-breadcrumb">
                        <a href="<?php echo get_post_type_archive_link('cdm_collection'); ?>">
                            <?php _e('Collections', 'cdm-explorer'); ?>
                        </a>
                        <span>›</span>
                        <a href="<?php echo get_permalink($collection->ID); ?>">
                            <?php echo esc_html($collection->post_title); ?>
                        </a>
                        <span>›</span>
                        <span><?php _e('Item', 'cdm-explorer'); ?></span>
                    </nav>
                <?php endif; ?>
                
                <h1 class="cdm-single-item__title"><?php the_title(); ?></h1>
                
                <div class="cdm-single-item__meta">
                    <span class="cdm-meta-item">
                        <strong><?php _e('ID:', 'cdm-explorer'); ?></strong> <?php echo esc_html($pointer); ?>
                    </span>
                    <span class="cdm-meta-item">
                        <strong><?php _e('Collection:', 'cdm-explorer'); ?></strong> 
                        <?php echo esc_html($alias); ?>
                    </span>
                </div>
            </header>
            
            <div class="cdm-single-item__content">
                <?php the_content(); ?>
            </div>
            
            <footer class="cdm-single-item__actions">
                <?php if ($item_url) : ?>
                    <a href="<?php echo esc_url($item_url); ?>" class="cdm-btn cdm-btn--primary" target="_blank">
                        <?php _e('View on ContentDM', 'cdm-explorer'); ?> →
                    </a>
                <?php endif; ?>
                
                <?php if ($cdm_image_url) : ?>
                    <a href="<?php echo esc_url($cdm_image_url); ?>" class="cdm-btn cdm-btn--secondary" download>
                        <?php _e('Download Image', 'cdm-explorer'); ?>
                    </a>
                <?php endif; ?>
            </footer>
        </div>
    </div>
    
    <!-- Related Items -->
    <?php if ($alias) : ?>
    <section class="cdm-single-item__related">
        <h2><?php _e('More from this Collection', 'cdm-explorer'); ?></h2>
        <?php 
        echo do_shortcode('[cdm_items collection="' . esc_attr($alias) . '" columns="4" limit="4" orderby="rand"]');
        ?>
    </section>
    <?php endif; ?>
    
</article>

<?php
endwhile;

get_footer();

