<?php
/**
 * Shortcodes Handler
 * 
 * @package CDM_Explorer
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDM_Shortcodes {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('cdm_collections', [$this, 'collections_shortcode']);
        add_shortcode('cdm_items', [$this, 'items_shortcode']);
        add_shortcode('cdm_item', [$this, 'single_item_shortcode']);
        add_shortcode('cdm_gallery', [$this, 'gallery_shortcode']);
        add_shortcode('cdm_search', [$this, 'search_shortcode']);
    }
    
    /**
     * [cdm_collections] - Display list of collections
     * 
     * Attributes:
     * - columns: Grid columns (default: 3)
     * - limit: Number of collections (default: -1 for all)
     * - orderby: Order by field (default: title)
     * - order: ASC or DESC (default: ASC)
     * - style: card, list, minimal (default: card)
     */
    public function collections_shortcode($atts) {
        $atts = shortcode_atts([
            'columns' => 3,
            'limit' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'style' => 'card',
        ], $atts, 'cdm_collections');
        
        $query = new WP_Query([
            'post_type' => 'cdm_collection',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ]);
        
        if (!$query->have_posts()) {
            return '<p class="cdm-no-results">' . __('No collections found.', 'cdm-explorer') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="cdm-collections cdm-collections--<?php echo esc_attr($atts['style']); ?>" style="--cdm-columns: <?php echo intval($atts['columns']); ?>;">
            <?php while ($query->have_posts()) : $query->the_post(); 
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
                        <h3 class="cdm-collection-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <div class="cdm-collection-card__meta">
                            <span class="cdm-collection-card__count">
                                <?php printf(_n('%s item', '%s items', $item_count, 'cdm-explorer'), number_format_i18n($item_count)); ?>
                            </span>
                        </div>
                        <?php if ($atts['style'] !== 'minimal') : ?>
                            <div class="cdm-collection-card__excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                        <div class="cdm-collection-card__actions">
                            <a href="<?php the_permalink(); ?>" class="cdm-btn cdm-btn--primary">
                                <?php _e('View Collection', 'cdm-explorer'); ?>
                            </a>
                            <?php if ($collection_url) : ?>
                                <a href="<?php echo esc_url($collection_url); ?>" class="cdm-btn cdm-btn--secondary" target="_blank">
                                    <?php _e('View on CDM', 'cdm-explorer'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * [cdm_items] - Display items grid
     * 
     * Attributes:
     * - collection: Collection alias (optional, shows all if not set)
     * - columns: Grid columns (default: 4)
     * - limit: Number of items (default: 20)
     * - orderby: Order by field (default: date)
     * - order: ASC or DESC (default: DESC)
     * - style: grid, list, masonry (default: grid)
     * - show_image: Show thumbnail (default: true)
     */
    public function items_shortcode($atts) {
        $atts = shortcode_atts([
            'collection' => '',
            'columns' => 4,
            'limit' => 20,
            'orderby' => 'date',
            'order' => 'DESC',
            'style' => 'grid',
            'show_image' => 'true',
        ], $atts, 'cdm_items');
        
        $args = [
            'post_type' => 'cdm_item',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        ];
        
        // Filter by collection
        if (!empty($atts['collection'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'cdm_collection_tax',
                'field' => 'slug',
                'terms' => $atts['collection'],
            ]];
        }
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p class="cdm-no-results">' . __('No items found.', 'cdm-explorer') . '</p>';
        }
        
        $show_image = filter_var($atts['show_image'], FILTER_VALIDATE_BOOLEAN);
        
        ob_start();
        ?>
        <div class="cdm-items cdm-items--<?php echo esc_attr($atts['style']); ?>" style="--cdm-columns: <?php echo intval($atts['columns']); ?>;">
            <?php while ($query->have_posts()) : $query->the_post();
                // Use preview image if set, otherwise fall back to CDM image
                $preview_image = get_post_meta(get_the_ID(), '_cdm_preview_image', true);
                $image_url = $preview_image ?: get_post_meta(get_the_ID(), '_cdm_image_url', true);
                $item_url = get_post_meta(get_the_ID(), '_cdm_item_url', true);
            ?>
                <article class="cdm-item-card">
                    <?php if ($show_image && $image_url) : ?>
                        <div class="cdm-item-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="cdm-item-card__content">
                        <h4 class="cdm-item-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * [cdm_item] - Display single item
     * 
     * Attributes:
     * - id: WordPress post ID or CDM pointer
     * - collection: Collection alias (required if using pointer)
     * - show_image: Show image (default: true)
     * - show_metadata: Show metadata (default: true)
     * - show_link: Show link to CDM (default: true)
     */
    public function single_item_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'collection' => '',
            'show_image' => 'true',
            'show_metadata' => 'true',
            'show_link' => 'true',
        ], $atts, 'cdm_item');
        
        $post = null;
        
        if (is_numeric($atts['id'])) {
            $post = get_post(intval($atts['id']));
        }
        
        // Try to find by pointer
        if (!$post && !empty($atts['collection'])) {
            $posts = get_posts([
                'post_type' => 'cdm_item',
                'meta_query' => [
                    'relation' => 'AND',
                    ['key' => '_cdm_alias', 'value' => $atts['collection']],
                    ['key' => '_cdm_pointer', 'value' => $atts['id']],
                ],
                'posts_per_page' => 1,
            ]);
            $post = $posts[0] ?? null;
        }
        
        if (!$post) {
            return '<p class="cdm-no-results">' . __('Item not found.', 'cdm-explorer') . '</p>';
        }
        
        // Use preview image if set, otherwise fall back to CDM image
        $preview_image = get_post_meta($post->ID, '_cdm_preview_image', true);
        $image_url = $preview_image ?: get_post_meta($post->ID, '_cdm_image_url', true);
        $item_url = get_post_meta($post->ID, '_cdm_item_url', true);
        $metadata = get_post_meta($post->ID, '_cdm_metadata', true);
        
        $show_image = filter_var($atts['show_image'], FILTER_VALIDATE_BOOLEAN);
        $show_metadata = filter_var($atts['show_metadata'], FILTER_VALIDATE_BOOLEAN);
        $show_link = filter_var($atts['show_link'], FILTER_VALIDATE_BOOLEAN);
        
        ob_start();
        ?>
        <div class="cdm-single-item">
            <?php if ($show_image && $image_url) : ?>
                <div class="cdm-single-item__image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($post->post_title); ?>">
                </div>
            <?php endif; ?>
            
            <div class="cdm-single-item__content">
                <h3 class="cdm-single-item__title"><?php echo esc_html($post->post_title); ?></h3>
                
                <?php if ($show_metadata && !empty($post->post_content)) : ?>
                    <div class="cdm-single-item__metadata">
                        <?php echo apply_filters('the_content', $post->post_content); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_link && $item_url) : ?>
                    <div class="cdm-single-item__actions">
                        <a href="<?php echo esc_url($item_url); ?>" class="cdm-btn cdm-btn--primary" target="_blank">
                            <?php _e('View on ContentDM', 'cdm-explorer'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * [cdm_gallery] - Display image gallery
     * 
     * Attributes:
     * - collection: Collection alias
     * - columns: Grid columns (default: 4)
     * - limit: Number of items (default: 20)
     * - lightbox: Enable lightbox (default: true)
     * - captions: Show captions (default: true)
     */
    public function gallery_shortcode($atts) {
        $atts = shortcode_atts([
            'collection' => '',
            'columns' => 4,
            'limit' => 20,
            'lightbox' => 'true',
            'captions' => 'true',
        ], $atts, 'cdm_gallery');
        
        $args = [
            'post_type' => 'cdm_item',
            'posts_per_page' => intval($atts['limit']),
        ];
        
        if (!empty($atts['collection'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'cdm_collection_tax',
                'field' => 'slug',
                'terms' => $atts['collection'],
            ]];
        }
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p class="cdm-no-results">' . __('No items found.', 'cdm-explorer') . '</p>';
        }
        
        $lightbox = filter_var($atts['lightbox'], FILTER_VALIDATE_BOOLEAN);
        $captions = filter_var($atts['captions'], FILTER_VALIDATE_BOOLEAN);
        
        ob_start();
        ?>
        <div class="cdm-gallery<?php echo $lightbox ? ' cdm-gallery--lightbox' : ''; ?>" style="--cdm-columns: <?php echo intval($atts['columns']); ?>;" data-lightbox="<?php echo $lightbox ? 'true' : 'false'; ?>">
            <?php while ($query->have_posts()) : $query->the_post();
                // Use preview image if set, otherwise fall back to CDM image
                $preview_image = get_post_meta(get_the_ID(), '_cdm_preview_image', true);
                $image_url = $preview_image ?: get_post_meta(get_the_ID(), '_cdm_image_url', true);
                if (!$image_url) continue;
            ?>
                <figure class="cdm-gallery__item">
                    <a href="<?php echo esc_url($image_url); ?>" class="cdm-gallery__link" data-title="<?php the_title_attribute(); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                    </a>
                    <?php if ($captions) : ?>
                        <figcaption class="cdm-gallery__caption"><?php the_title(); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * [cdm_search] - Search form for items
     * 
     * Attributes:
     * - placeholder: Search placeholder text
     * - collection: Limit to collection alias
     * - button_text: Submit button text
     */
    public function search_shortcode($atts) {
        $atts = shortcode_atts([
            'placeholder' => __('Search items...', 'cdm-explorer'),
            'collection' => '',
            'button_text' => __('Search', 'cdm-explorer'),
        ], $atts, 'cdm_search');
        
        $search_query = isset($_GET['cdm_search']) ? sanitize_text_field($_GET['cdm_search']) : '';
        
        ob_start();
        ?>
        <div class="cdm-search">
            <form class="cdm-search__form" method="get" action="<?php echo esc_url(get_post_type_archive_link('cdm_item')); ?>">
                <input type="text" name="cdm_search" class="cdm-search__input" 
                       placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                       value="<?php echo esc_attr($search_query); ?>">
                <?php if (!empty($atts['collection'])) : ?>
                    <input type="hidden" name="cdm_collection" value="<?php echo esc_attr($atts['collection']); ?>">
                <?php endif; ?>
                <button type="submit" class="cdm-btn cdm-btn--primary cdm-search__btn">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

