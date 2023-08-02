<?php
/*
Plugin Name: Custom Variation Display
Version: 1.0.0
Author: Shahnur Alam
*/

// Initialize the plugin and hook into WooCommerce
function custom_variation_display_init() {
    // Check if WooCommerce is active
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('woocommerce_before_add_to_cart_form', 'custom_variation_display_display');
        add_action('wp_enqueue_scripts', 'custom_variation_display_enqueue_scripts');
        add_filter('woocommerce_settings_tabs_array', 'custom_variation_display_add_settings_tab', 50);
        add_action('woocommerce_settings_custom_variation_display', 'custom_variation_display_settings_content');
        add_action('woocommerce_update_options_custom_variation_display', 'custom_variation_display_save_settings');
    }
}
add_action('plugins_loaded', 'custom_variation_display_init');

// Function to display the custom variation display
function custom_variation_display_display() {
    global $product;

    // Get the product variations
    $variations = $product->get_available_variations();
    
    // Check if the custom variation display feature is enabled in WooCommerce settings
    $custom_display_enabled = get_option('custom_variation_display_enabled', 'yes');

    if ($variations && $custom_display_enabled === 'yes') {
        ?>
        <div class="custom-variation-display">
            <?php
            // Display color swatches
            echo '<div class="color-swatches">';
            foreach ($variations as $variation) {
                $color = $variation['attributes']['attribute_pa_color'];
                $image = $variation['image']['url'];
                echo '<button class="color-swatch" data-color="' . esc_attr($color) . '"><img src="' . esc_url($image) . '" alt="' . esc_attr($color) . '"></button>';
            }
            echo '</div>';

            // Display size selection
            echo '<div class="size-selection">';
            woocommerce_form_field('attribute_pa_size', array(
                'type' => 'select',
                'class' => array('form-row-wide'),
                'options' => wc_get_product_terms($product->get_id(), 'pa_size', array('fields' => 'names')),
                'label' => __('Select Size', 'woocommerce'),
            ), '');
            echo '</div>';

            // Display dynamic price
            echo '<div class="product-price"></div>';
            ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Handle color swatch click event
            $('.color-swatch').on('click', function() {
                var color = $(this).data('color');
                var variation = findVariation(color);
                if (variation) {
                    // Update the product image
                    $('.woocommerce-product-gallery__image img').attr('src', variation.image.url);
                    // Update the dynamic price
                    $('.product-price').text(variation.display_price);
                }
            });

            // Handle size selection change event
            $('[name=attribute_pa_size]').on('change', function() {
                // Highlight the selected size visually
                $('[name=attribute_pa_size]').removeClass('selected');
                $(this).addClass('selected');
            });

            // Helper function to find the selected variation by color
            function findVariation(color) {
                return <?php echo json_encode($variations); ?>.find(function(variation) {
                    return variation.attributes.attribute_pa_color === color;
                });
            }
        });
        </script>
        <?php
    }
}

// Enqueue necessary scripts and styles
function custom_variation_display_enqueue_scripts() {
    wp_enqueue_style('custom-variation-display-style', plugin_dir_url(__FILE__) . 'custom-variation-display.css');
}

// Add custom settings to WooCommerce settings page
function custom_variation_display_add_settings_tab($tabs) {
    $tabs['custom_variation_display'] = __('Custom Variation Display', 'woocommerce');
    return $tabs;
}

// Render the custom settings content
function custom_variation_display_settings_content() {
    woocommerce_admin_fields(array(
        'section_title' => array(
            'name' => __('Custom Variation Display Settings', 'woocommerce'),
            'type' => 'title',
            'desc' => '',
            'id'   => 'wc_settings_custom_variation_display_section_title'
        ),
        'custom_variation_display_enabled' => array(
            'name' => __('Enable Custom Variation Display', 'woocommerce'),
            'type' => 'checkbox',
            'desc' => __('Check this box to enable the custom variation display feature.', 'woocommerce'),
            'id'   => 'wc_settings_custom_variation_display_enabled'
        ),
        'section_end' => array(
            'type' => 'sectionend',
            'id' => 'wc_settings_custom_variation_display_section_end'
        )
    ));
}

// Save custom settings
function custom_variation_display_save_settings() {
    woocommerce_update_options(array(
        'wc_settings_custom_variation_display_enabled'
    ));
}
