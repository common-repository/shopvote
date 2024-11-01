<?php
namespace Shopvote;

class AjaxHandler
{

    private static function saveSettings() {
        if (isset($_POST['badge_position'])) {
            Settings::setBadgePosition($_POST['badge_position']);
        }
        if (isset($_POST['badge_visibility'])) {
            Settings::setBadgeVisibility($_POST['badge_visibility'] === 'visible');
        }
        if (isset($_POST['badge_distance_h']) && (($pos = (int)$_POST['badge_distance_h']) > 0)) {
            Settings::setBadgeDistanceH($pos);
        }
        if (isset($_POST['badge_distance_v']) && (($pos = (int)$_POST['badge_distance_v']) > 0)) {
            Settings::setBadgeDistanceV($pos);
        }
        if (isset($_POST['product_reviews_enable'])) {
            Settings::setProductReviewsEnabled($_POST['product_reviews_enable'] === 'enabled');
        }
        if (isset($_POST['shop_reviews_enable'])) {
            Settings::setShopReviewsEnabled($_POST['shop_reviews_enable'] === 'enabled');
        }
        return ['status' => 'success'];
    }

    private static function migrateReviewUids() {
        global $wpdb;

        $r = $wpdb->get_row('SELECT * FROM '.$wpdb->commentmeta.' LIMIT 1', ARRAY_A);

        if (!is_array($r) || !array_key_exists('reviewUID', $r)) {
            return true;
        }
        $qr = $wpdb->query('
            INSERT INTO '.$wpdb->commentmeta.' (comment_id, meta_key, meta_value)
                SELECT comment_id, "shopvote_review_uid", reviewUID
                   FROM '.$wpdb->commentmeta.'
                  WHERE reviewUID IS NOT NULL
                        AND reviewUID NOT IN (SELECT DISTINCT meta_value FROM '.$wpdb->commentmeta.' WHERE meta_key = "shopvote_review_uid")
               GROUP BY comment_id, reviewUID
        ');

        if (is_int($qr)) {
            $wpdb->query('ALTER TABLE '.$wpdb->commentmeta.' DROP `reviewUID`');
            return true;
        }

        return false;
    }

    private static function downloadReviews($sku) {
        global $wpdb;

        require_once __DIR__.'/ShopVoteApi.php';

        try {
            $response = (new ShopVoteApi())->fetchReviewsForSku($sku);
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message ' => $e->getMessage(),
            ];
        }

        if (isset($response['reviews']) && is_array($response['reviews'])) {

            $productId = wc_get_product_id_by_sku($sku);
            if (!self::migrateReviewUids()) {
                return [
                    'status' => 'error',
                    'message' => __('Unable to migrate review data.', 'shopvote'),
                ];
            }

            foreach ($response['reviews'] as $review) {

                $uid = $review['reviewUID'];
                $dbUid = $wpdb->get_var($wpdb->prepare(
                    'SELECT meta_value FROM '.$wpdb->commentmeta.' WHERE meta_key = "shopvote_review_uid" AND meta_value = %s LIMIT 1',
                    $uid
                ));

                if (!$dbUid) {
                    $comment_id = wp_insert_comment([
                        'comment_post_ID'      => $productId,
                        'comment_author'       => $review['author'] . ' (via SHOPVOTE)',
                        'comment_author_email' => 'shopvote@shopvote.com',
                        'comment_author_url'   => '',
                        'comment_content'      => $review['text'],
                        'comment_type'         => 'review',
                        'comment_parent'       => 0,
                        'user_id'              => 0,
                        'comment_author_IP'    => '',
                        'comment_agent'        => '',
                        'comment_date'         => date($review['created']),
                        'comment_approved'     => 1
                    ]);
                    update_comment_meta($comment_id, 'rating', $review['rating_value']);
                    update_comment_meta($comment_id, 'shopvote_review_uid', $uid);
                    update_comment_meta($comment_id, 'verified', 1);
                }
            }
        }

        return [
            'status' => 'success',
            'reviews' => $response
        ];
    }

    public static function register() {
        add_action('wp_ajax_shopvote_save_settings', function() {
            echo json_encode(self::saveSettings());
            wp_die();
        });

        if (Settings::isSetup()) {
            add_action('wp_ajax_shopvote_get_reviews', function() {
                echo json_encode(self::downloadReviews($_POST['sku']));
                wp_die();
            });
            add_action('wp_ajax_nopriv_shopvote_get_reviews', function() {
                echo json_encode(self::downloadReviews($_POST['sku']));
                wp_die();
            });
        }
    }
}
