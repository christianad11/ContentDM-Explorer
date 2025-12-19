<?php
/**
 * Collection Archive Template
 * 
 * @package CDM_Explorer
 */

get_header();
?>

<div class="cdm-archive cdm-archive--collections">
    <header class="cdm-archive__header">
        <h1 class="cdm-archive__title"><?php _e('Digital Collections', 'cdm-explorer'); ?></h1>
        <p class="cdm-archive__description">
            <?php _e('Browse our digital archive collections imported from ContentDM.', 'cdm-explorer'); ?>
        </p>
    </header>
    
    <?php if (have_posts()) : ?>
        <div class="cdm-collections cdm-collections--card" style="--cdm-columns: 3;">
            <?php while (have_posts()) : the_post(); 
                $alias = get_post_meta(get_the_ID(), '_cdm_alias', true);
                $item_count = get_post_meta(get_the_ID(), '_cdm_item_count', true);
                $item_count = is_numeric($item_count) ? (int) $item_count : 0;
                $collection_url = get_post_meta(get_the_ID(), '_cdm_collection_url', true);
                // Use preview image if set, otherwise fall back to featured image
                $preview_image = get_post_meta(get_the_ID(), '_cdm_preview_image', true);
            ?>
                <article class="cdm-collection-card">
                    <?php if ($preview_image || has_post_thumbnail()) : ?>
                        <div class="cdm-collection-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php if ($preview_image) : ?>
                                    <img src="<?php echo esc_url($preview_image); ?>" alt="<?php the_title_attribute(); ?>">
                                <?php else : ?>
                                    <?php the_post_thumbnail('medium'); ?>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="cdm-collection-card__content">
                        <h2 class="cdm-collection-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="cdm-collection-card__meta">
                            <span class="cdm-collection-card__count">
                                <?php printf(_n('%s item', '%s items', $item_count, 'cdm-explorer'), number_format_i18n($item_count)); ?>
                            </span>
                        </div>
                        <div class="cdm-collection-card__excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        <div class="cdm-collection-card__actions">
                            <a href="<?php the_permalink(); ?>" class="cdm-btn cdm-btn--primary">
                                <?php _e('View Collection', 'cdm-explorer'); ?>
                            </a>
                        </div>
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
        <p class="cdm-no-results"><?php _e('No collections found.', 'cdm-explorer'); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();

