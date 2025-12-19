<?php
/**
 * Item Archive Template
 * 
 * @package CDM_Explorer
 */

get_header();

// Handle collection filter
$collection_filter = isset($_GET['cdm_collection']) ? sanitize_text_field($_GET['cdm_collection']) : '';
?>

<div class="cdm-archive cdm-archive--items">
    <header class="cdm-archive__header">
        <h1 class="cdm-archive__title"><?php _e('Archive Items', 'cdm-explorer'); ?></h1>
        
        <?php if ($collection_filter) : 
            $collection = get_posts([
                'post_type' => 'cdm_collection',
                'meta_key' => '_cdm_alias',
                'meta_value' => $collection_filter,
                'posts_per_page' => 1,
            ]);
            if ($collection) :
        ?>
            <p class="cdm-archive__filter-info">
                <?php printf(__('Showing items from: <strong>%s</strong>', 'cdm-explorer'), esc_html($collection[0]->post_title)); ?>
                <a href="<?php echo get_post_type_archive_link('cdm_item'); ?>" class="cdm-clear-filter">
                    <?php _e('Clear filter', 'cdm-explorer'); ?>
                </a>
            </p>
        <?php endif; endif; ?>
        
        <!-- Search Form -->
        <div class="cdm-archive__search">
            <?php echo do_shortcode('[cdm_search]'); ?>
        </div>
    </header>
    
    <?php if (have_posts()) : ?>
        <div class="cdm-items cdm-items--grid" style="--cdm-columns: 4;">
            <?php while (have_posts()) : the_post();
                // Use preview image if set, otherwise fall back to CDM image
                $preview_image = get_post_meta(get_the_ID(), '_cdm_preview_image', true);
                $image_url = $preview_image ?: get_post_meta(get_the_ID(), '_cdm_image_url', true);
            ?>
                <article class="cdm-item-card">
                    <?php if ($image_url) : ?>
                        <div class="cdm-item-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="cdm-item-card__content">
                        <h3 class="cdm-item-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <nav class="cdm-pagination">
            <?php the_posts_pagination([
                'mid_size' => 2,
                'prev_text' => '← ' . __('Previous', 'cdm-explorer'),
                'next_text' => __('Next', 'cdm-explorer') . ' →',
            ]); ?>
        </nav>
    <?php else : ?>
        <p class="cdm-no-results"><?php _e('No items found.', 'cdm-explorer'); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();

