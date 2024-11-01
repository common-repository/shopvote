<?php
require_once plugin_dir_path( __DIR__ ) . '/includes/Settings.php';

use Shopvote\Settings;

$shopvote_base_url = plugin_dir_url(__DIR__);
?>

<div class="sv-container" id="main-page">
    <div class="sv-row sv-justify-content-center">
        <div class="sv-col-md-8">
            <div class="sv-row">
                <div class="sv-col sv-text-center sv-d-flex">
                    <img src="<?php echo $shopvote_base_url; ?>/assets/images/woocommerce-logo.png" alt="WooCommerce" class="sv-img-fluid sv-mx-auto sv-my-auto">
                </div>
                <div class="sv-col sv-text-center sv-d-flex">
                    <span class="sv-h1 sv-mx-auto sv-my-auto">+</span>
                </div>
                <div class="sv-col sv-text-center sv-d-flex">
                    <img src="<?php echo $shopvote_base_url; ?>/assets/images/bg-golden-seal.png" alt="SHOPVOTE" class="sv-img-fluid sv-mx-auto sv-my-auto">
                </div>
            </div>
            <div class="sv-row">
                <div class="sv-col-sm-12">
                    <h1><?php esc_html_e('Configuration', 'shopvote');?></h1>
                </div>
            </div>
            <form action="<?php esc_attr_e(admin_url('admin-ajax.php')); ?>" method="post" id="shopvoteForm">
                <input type="hidden" name="action" value="shopvote_save_settings" />
                <div class="sv-row">
                    <div class="sv-col-sm-12">
                        <div class="sv-card">
                            <div class="sv-card-body">
                                <h2><?php esc_html_e('SHOPVOTE badge', 'shopvote'); ?></h2>
                                <div class="sv-row sv-form-group">
                                    <div class="sv-col-sm-12">
                                        <div class="sv-custom-control sv-custom-switch">
                                            <input name="badge_visibility" type="hidden" value="hidden">
                                            <input <?php if (Settings::isBadgeVisible()) { ?> checked="checked" <?php } ?> name="badge_visibility" data-toggle="collapse" data-toggle-class="sv-show" data-target="#badgePosition" type="checkbox" class="sv-custom-control-input" id="showBadge" value="visible">
                                            <label class="sv-custom-control-label" for="showBadge"><?php esc_html_e('Show SHOPVOTE badge', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="sv-card-body sv-collapse <?php if (Settings::isBadgeVisible()) { ?> sv-show <?php } ?>" id="badgePosition">
                                <h3><?php esc_html_e('Badge position', 'shopvote'); ?></h3>
                                <div class="sv-row sv-form-group">
                                    <div class="sv-col-sm-12">
                                        <p><?php esc_html_e('Specify the position where the badge is going to be displayed. (Default: right / bottom)', 'shopvote'); ?></p>
                                    </div>
                                </div>
                                <div class="sv-row sv-form-group">
                                    <div class="sv-col-sm-3">
                                        <div class="sv-custom-control sv-custom-radio sv-custom-control-inline">
                                            <input <?php if (Settings::getBadgePosition() === 'bottom/right') { ?> checked="checked" <?php } ?> value="bottom/right" type="radio" id="badgePosition1" name="badge_position" class="sv-custom-control-input">
                                            <label class="sv-custom-control-label sv-position-label" for="badgePosition1"><?php esc_html_e('right / bottom', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                    <div class="sv-col-sm-3">
                                        <div class="sv-custom-control sv-custom-radio sv-custom-control-inline">
                                            <input <?php if (Settings::getBadgePosition() === 'top/right') { ?> checked="checked" <?php } ?> value="top/right" type="radio" id="badgePosition2" name="badge_position" class="sv-custom-control-input">
                                            <label class="sv-custom-control-label sv-position-label" for="badgePosition2"><?php esc_html_e('right / top', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                    <div class="sv-col-sm-3">
                                        <div class="sv-custom-control sv-custom-radio sv-custom-control-inline">
                                            <input <?php if (Settings::getBadgePosition() === 'bottom/left') { ?> checked="checked" <?php } ?> value="bottom/left" type="radio" id="badgePosition3" name="badge_position" class="sv-custom-control-input">
                                            <label class="sv-custom-control-label sv-position-label" for="badgePosition3"><?php esc_html_e('left / bottom', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                    <div class="sv-col-sm-3">
                                        <div class="sv-custom-control sv-custom-radio sv-custom-control-inline">
                                            <input <?php if (Settings::getBadgePosition() === 'top/left') { ?> checked="checked" <?php } ?> value="top/left" type="radio" id="badgePosition4" name="badge_position" class="sv-custom-control-input">
                                            <label class="sv-custom-control-label sv-position-label" for="badgePosition4"><?php esc_html_e('left / top', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="sv-row sv-form-group" style="margin-top: 2rem">
                                    <label class="sv-col-sm-4"><?php esc_html_e('Distance top / bottom', 'shopvote'); ?></label>
                                    <div class="sv-col-sm-8">
                                        <div class="sv-input-group sv-mb-spacing">
                                            <input name="badge_distance_v" type="number" value="<?php esc_attr_e(Settings::getBadgeDistanceV()); ?>" class="sv-form-control">
                                            <div class="sv-input-group-append">
                                                <span class="sv-input-group-text"><?php esc_html_e('Pixel', 'shopvote'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sv-row sv-form-group">
                                    <label class="sv-col-sm-4"><?php esc_html_e('Distance left / right', 'shopvote'); ?></label>
                                    <div class="sv-col-sm-8">
                                        <div class="sv-input-group sv-mb-spacing">
                                            <input name="badge_distance_h" type="number" value="<?php esc_attr_e(Settings::getBadgeDistanceH()); ?>" class="sv-form-control">
                                            <div class="sv-input-group-append">
                                                <span class="sv-input-group-text"><?php esc_html_e('Pixel', 'shopvote'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sv-row">
                    <div class="sv-col-sm-12">
                        <div class="sv-card">
                            <div class="sv-card-body">
                                <h2><?php esc_html_e('Automatic rating request', 'shopvote');?></h2>
                                <p><?php esc_html_e('Enable this option to automatically ask your customers for reviews in a legally secure way.', 'shopvote'); ?></p>
                                <div class="sv-row sv-form-group">
                                    <div class="sv-col-sm-12">
                                        <div class="sv-custom-control sv-custom-switch">
                                            <input name="shop_reviews_enable" type="hidden" value="disabled">
                                            <input <?php if (Settings::isShopReviewsEnabled()) { ?> checked="checked" <?php } ?> name="shop_reviews_enable" type="checkbox" class="sv-custom-control-input" id="showRequest" value="enabled">
                                            <label class="sv-custom-control-label" for="showRequest"><?php esc_html_e('Send SHOPVOTE rating requests', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sv-row">
                    <div class="sv-col-sm-12">
                        <div class="sv-card">
                            <div class="sv-card-body">
                                <h2><?php esc_html_e('Collect product reviews', 'shopvote'); ?></h2>
                                <p><?php esc_html_e('Enable this option to receive additional product reviews from your customers.', 'shopvote'); ?></p>
                                <div class="sv-row sv-form-group">
                                    <div class="sv-col-sm-12">
                                        <div class="sv-custom-control sv-custom-switch">
                                            <input name="product_reviews_enable" type="hidden" value="disabled">
                                            <input <?php if (Settings::isProductReviewsEnabled()) { ?> checked="checked" <?php } ?>  name="product_reviews_enable" type="checkbox" class="sv-custom-control-input" id="showReview" value="enabled">
                                            <label class="sv-custom-control-label" for="showReview"><?php esc_html_e('Collect and display SHOPVOTE product reviews', 'shopvote'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        let $shopVoteForm = jQuery('#shopvoteForm'),
            fnSaveSettings = function () {
                jQuery.ajax({
                    url: $shopVoteForm.attr('action'),
                    type: 'POST',
                    dataType: 'json',
                    data: $shopVoteForm.serialize(),
                    success: function(status, data) {
                    },
                    error: function(error) {
                    }
                });
            };

        jQuery('input', $shopVoteForm).on('change', fnSaveSettings);
        jQuery('input.distance', $shopVoteForm).on('input', fnSaveSettings);
    });

    jQuery(document).on('click', '[data-toggle="collapse"]', function (event) {
        // preventDefault only for <a> elements (which change the URL) not inside the collapsible element
        if (event.currentTarget.tagName === 'A') {
            event.preventDefault();
        }

        let $trigger = jQuery(this),
            selectors = [].slice.call(document.querySelectorAll(this.getAttribute('data-target')));
        jQuery(selectors).each(function () {
            let $target = jQuery(this);
            $target.toggleClass($trigger.data('toggle-class'));
        });
    });
</script>
