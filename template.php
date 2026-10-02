<?php
/**
 * Our Companies template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Retrieve ACF fields
$background_color = get_field('background_color');
$section_title = get_field('section_title');
$section_title_alignment = get_field('section_title_alignment');
$intro_text = get_field('intro_text');

// Generate block ID and class
$block_id_attr = esc_attr($block['id']);
$block_id = $block['anchor'] ?? 'acf-block-' . $block['id'];
$block_classes = 'our-companies bg-' . esc_attr($background_color) . ' ' . (!empty($block['className']) ? esc_attr($block['className']) : '');

$query = new WP_Query([
    'post_type' => 'company',
    'post_status' => 'publish',
    'posts_per_page' => -1, // Use -1 to retrieve all posts
    'orderby' => 'menu_order', // Sorts by custom order
    'order' => 'ASC', // A to Z order
    'no_found_rows' => true, // skip pagination count
    'update_post_term_cache' => false,
    'update_post_meta_cache' => true,
    'ignore_sticky_posts' => true,
]);

$activeCompany = 0;

if ($query->have_posts()) {
    $companies = [];

    while ($query->have_posts()) {
        $query->the_post();

        $postId = get_the_ID();
        $thumb_id = get_post_thumbnail_id($postId);
        if (!$thumb_id) {
            continue;
        }
        
        $titleId = 'our-companies-logo-' . $block_id_attr . '-' . $postId;
        $contentId = 'our-companies-content-' . $block_id_attr . '-' . $postId;

        $companies[] = [
            'id' => $postId,
            'title' => get_the_title(),
            'thumbnail' => wp_get_attachment_image($thumb_id, 'medium_large'),
            'description' => get_field('description', $postId),
            'link' => get_permalink($postId),
            'titleId' => $titleId,
            'contentId' => $contentId,
        ];
    }
    wp_reset_postdata();

	if (!empty($companies)) {
		$activeCompany = $companies[0]['id'];
	}
    ?>
    <div id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($block_classes); ?>">
        <div class="our-companies-liner">
            <div class="our-companies-intro">
                <h2 class="<?php echo esc_attr($section_title_alignment); ?>"><?php echo esc_html($section_title); ?></h2>
                <?php if (!empty($intro_text)) { ?>
                    <?php echo wp_kses_post($intro_text); ?>
                <?php } ?>
            </div>
            <div class="our-companies-columns">
                <div class="our-companies__cards">
                    <?php foreach($companies as $company) { ?>
                        <div class="our-companies-card">
                            <div id="<?php echo esc_attr($company['titleId']); ?>" class="our-companies-card__logo">
                                <button type="button" class="company-logo-button <?php if($activeCompany === $company['id']) echo 'show'; ?>" data-target="#<?php echo esc_attr($company['contentId']); ?>" aria-expanded="<?php echo ($activeCompany === $company['id']) ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($company['contentId']); ?>">
                                    <span class="company-logo-image"><?php echo $company['thumbnail']; ?></span>
                                    <span class="company-logo-name"><?php echo esc_html($company['title']); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div id="<?php echo esc_attr($block_id . '-slider'); ?>" class="our-companies__content-blocks">
                    <?php foreach($companies as $company) { ?>
                        <div id="<?php echo esc_attr($company['contentId']); ?>" class="our-companies-content <?php if($activeCompany === $company['id']) echo 'show'; ?>" aria-labelledby="<?php echo esc_attr($company['titleId']); ?>">
                            <div class="our-companies-content__logo">
                                <span class="company-logo-image"><?php echo $company['thumbnail']; ?></span>
                            </div>
                            <h3><?php echo esc_html($company['title']); ?></h3>
                            <p class="company-content"><?php echo nl2br(esc_html($company['description'])); ?></p>
                            <div class="company-button"><a href="<?php echo esc_url($company['link']); ?>" class="btn-primary">Learn More</a></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php if(!$is_preview) { ?>
            <script type="text/javascript">
                // enableSlider() and setFocus() are provided by the site's shared JavaScript.
                jQuery(document).ready(function($) {
                    const $slider = $('#<?php echo esc_attr($block_id . '-slider'); ?>');
                    const breakpoint = 768; // Mobile breakpoint
                    
                    // Initial check
                    enableSlider($slider, breakpoint, 1, []);

                    // Check on resize
                    $(window).resize(function() {
                        enableSlider($slider, breakpoint, 1, []);
                    });

                    // Handle the click events on desktop
                    $('#<?php echo esc_attr($block_id); ?> .company-logo-button').on('click', function() {
                        $('#<?php echo esc_attr($block_id); ?> .company-logo-button').removeClass('show').attr('aria-expanded', 'false');
                        $('#<?php echo esc_attr($block_id); ?> .our-companies-content').removeClass('show');

                        const companyContentElementId = $(this).data('target');

                        $(this).addClass('show').attr('aria-expanded', 'true');
                        $(companyContentElementId).addClass('show');
                        setFocus($('.our-companies-content', $(companyContentElementId)));
                    });
                });
            </script>
        <?php } ?>
    </div>
    <?php
}
