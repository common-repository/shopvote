<?php
namespace Shopvote;

class FrontendResourcesMananger {

    private static function registerScripts() {
        if (Settings::isBadgeVisible()) {
            add_action('wp_head', function () {
                echo '<script id="sv-settings">const shopvote_settings = '.json_encode([
                        'user_shop'        => Settings::getUserShop(),
                        'badge_visible'    => true,
                        'badge_type'       => Settings::getBadgeType(),
                        'badge_position_h' => Settings::getBadgePositionH(),
                        'badge_position_v' => Settings::getBadgePositionV(),
                        'badge_distance_h' => Settings::getBadgeDistanceH(),
                        'badge_distance_v' => Settings::getBadgeDistanceV(),
                    ]).';</script>';
            });
            wp_deregister_script('shopvote_badge');
            wp_register_script('shopvote_badge', 'https://widgets.shopvote.de/js/reputation-badge-v2.min.js', [], null, true);
            wp_enqueue_script('shopvote_badge');
            wp_deregister_script('shopvote_badge_call');
            wp_register_script('shopvote_badge_call', plugin_dir_url(__DIR__) . 'assets/js/badge.js', [], \ShopvotePlugin::VERSION, true);
            wp_enqueue_script('shopvote_badge_call');
        }

        if (Settings::isShopReviewsEnabled()) {
            // Registering without enqueueing. Will be enqueued only when needed.
            wp_deregister_script('shopvote_order_popup');
            wp_register_script('shopvote_order_popup', plugin_dir_url(__DIR__) . 'assets/js/srt-v4.js', [], \ShopvotePlugin::VERSION, true);
        }

        if (Settings::isProductReviewsEnabled()) {
            add_action('wp_head', function () {
                echo '<script id="sv-ajaxurl">const shopvote_ajaxurl = '.json_encode(esc_url(admin_url('admin-ajax.php'))).';</script>';
            });
            wp_deregister_script('shopvote_fetch_product_reviews');
            wp_register_script('shopvote_fetch_product_reviews', plugin_dir_url( __DIR__ ) . 'assets/js/reviews.js', [], \ShopvotePlugin::VERSION, true);
        }

    }

    private static function registerProductReviewDownload() {
        if (!\Shopvote\Settings::isProductReviewsEnabled()) {
            return;
        }
        add_action('woocommerce_before_single_product', function () {
            /** @var WC_Product_Simple $product */
            global $product;
            wp_enqueue_script('shopvote_fetch_product_reviews');

            echo sprintf(
                '<script>window.addEventListener(\'load\', function() {'
                .'shopvote_fetch_reviews(%s);'
                .'}, false);</script>',
                json_encode($product->get_sku())
            );
        });
    }

    private static function registerShopReviewPopup() {
        if (!\Shopvote\Settings::isShopReviewsEnabled()) {
            return;
        }
        add_action('woocommerce_thankyou', function ($order_id) {
            /** @var WC_Order $order */
            $order = wc_get_order($order_id);
            $products = [];
            foreach ($order->get_items() as $item) {
                /** @var WC_Order_Item_Product $item */
                $product = $item->get_product();
                $image = $product->get_image_id();
                $pData = [
                    'ID' => $product->get_id(),
                    'SKU' => $product->get_sku(),
                    'Product' => $product->get_name(),
                    'URL' => $product->get_permalink(),
                    'ImageURL' => $image ? wp_get_attachment_url($image) : '',
                ];
                // Check if the GTIN provided by German Market is available.
                if ($gtin = $product->get_meta('_ts_gtin')) {
                    $pData['GTIN'] = $gtin;
                }
                $products[] = $pData;
            }

            wp_enqueue_script('shopvote_order_popup');

            echo sprintf(
                '<div id="srt-customer-data" style="display:none">'
                .'<span id="srt-customer-email">%s</span>'
                .'<span id="srt-customer-reference">%d</span>'
                .'</div>',
                esc_html($order->get_billing_email()),
                $order->get_id()
            );
            echo sprintf(
                '<script>'
                .'const shopvote_order_products = %s;'
                .'window.addEventListener(\'load\', function() {'
                .'loadSRT(%s, \'https:\' === document.location.protocol ? \'https\' : \'http\');'
                .'}, false);'.
                '</script>',
                json_encode($products),
                json_encode(\Shopvote\Settings::getToken())
            );
        });
    }

    public static function register() {
        add_action('wp_enqueue_scripts', function () {
            self::registerScripts();
        });

        self::registerProductReviewDownload();
        self::registerShopReviewPopup();
    }
}
