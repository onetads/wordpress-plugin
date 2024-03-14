<?php

namespace Ras\Utilities;


use WC_Product;
use WP_Block_Patterns_Registry;
use WP_Post;

class ProductUtil
{
    private int $product_id;
    private bool $is_block_theme;

    private array $templateFromView = [
        'single-product' => 'single-product.php',
        'category' => 'taxonomy-product_cat.php',
        'home' => 'archive-product.php',
    ];

    private const DEFAULT_TEMPLATE = 'archive-product.php';
    private const NON_BLOCK_TEMPLATE_PART_SLUG = 'content';
    private const NON_BLOCK_TEMPLATE_PART_NAME = 'product';

    public function __construct(int $product_id)
    {
        $this->product_id = $product_id;
        $this->is_block_theme = wp_is_block_theme();
    }


    /**
     * @return bool
     */
    public function is_product_in_stock(): bool
    {
        /** @var WC_Product $product */
        $product = wc_get_product($this->product_id);

        if (!$product) {
            return false;
        }

        if (!$product->is_in_stock()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $view
     */
    public function get_product_html(
        string $view
    )
    {
        global $post;

        $post = get_post($this->product_id);

        $this->is_block_theme ?
            $this->render_content_for_block_template($view, $post)
            : $this->render_content_for_non_block_template($post);
    }

    /**
     * @param string $view
     * @param WP_Post $post
     * @return void
     */
    private function render_content_for_block_template(
        string  $view,
        WP_Post $post
    ): void
    {
        global $_wp_current_template_content;

        $template = $this->templateFromView[$view] ?? self::DEFAULT_TEMPLATE;
        locate_block_template('', $view, [$template]);

        $blocks = parse_blocks($_wp_current_template_content);
        $postTemplateBlock = $this->find_core_post_template_block($blocks)
            ?? $this->find_core_post_template_block($this->get_related_products_block_from_pattern());

        $block_content = '';

        foreach ($postTemplateBlock['innerBlocks'] as $block) {
            $block_content .= render_block($block);
        }

        echo self::wrap_block_into_list($post, $block_content);

        die();
    }

    /**
     * @param WP_Post $post
     * @return void
     */
    private function render_content_for_non_block_template(
        WP_Post $post
    ): void
    {
        setup_postdata($post);

        wc_get_template_part(self::NON_BLOCK_TEMPLATE_PART_SLUG, self::NON_BLOCK_TEMPLATE_PART_NAME);

        wp_reset_postdata();
        die();
    }

    /**
     * @param $blocks
     * @return array|null
     */
    private function find_core_post_template_block($blocks)
    {
        $blockName = 'core/post-template';
        $postTemplateBlock = null;

        foreach ($blocks as $block) {
            if ($block['blockName'] == $blockName) {
                $postTemplateBlock = $block;
            }

            if ($postTemplateBlock) {
                break;
            }

            if (!empty($block['innerBlocks'])) {
                $postTemplateBlock = $this->find_core_post_template_block($block['innerBlocks']);
            }
        }

        return $postTemplateBlock;
    }

    /**
     * @return array
     */
    private function get_related_products_block_from_pattern(): array
    {
        $registry = WP_Block_Patterns_Registry::get_instance();

        $pattern = $registry->get_registered('woocommerce-blocks/related-products');
        $content = $pattern['content'];

        return parse_blocks($content);
    }

    /**
     * @param WP_Post $post
     * @param string $block_content
     * @return string
     */
    private static function wrap_block_into_list(
        WP_Post $post,
        string  $block_content
    ): string
    {
        $enhanced_pagination = false;

        // Wrap the render inner blocks in a `li` element with the appropriate post classes.
        $post_classes = implode(' ', get_post_class('wp-block-post'));

        $inner_block_directives = $enhanced_pagination ? ' data-wp-key="post-template-item-' . $post->ID . '"' : '';

        return '<li' . $inner_block_directives . ' class="' . esc_attr($post_classes) . '">' . $block_content . '</li>';
    }
}