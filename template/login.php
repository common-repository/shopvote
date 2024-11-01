<?php
$shopvote_plugin_dir = plugin_dir_path( __DIR__ );

require_once $shopvote_plugin_dir . 'includes/ShopVoteApi.php';

$errorMessage = '';
if (!empty($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $errors = [];

    if (empty($username)) {
        $errors['username'] = __('The username is required.', 'shopvote');
    }

    if (empty($password)) {
        $errors['password'] = __('The password is required.', 'shopvote');
    }

    if (empty($errors)) {
        try {
            (new Shopvote\ShopVoteApi())->authShopVoteUser($username, $password);
            if (wp_redirect(admin_url('options-general.php?page=shopvote'))) {
                exit;
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    } else {
        $errorMessage = implode(' ', $errors);
    }

}

$shopvote_base_url = plugin_dir_url(__DIR__);
?>
<div class="sv-container" id="main-page">
    <div class="sv-row sv-justify-content-center">
        <div class="sv-col-md-8">
            <div class="sv-row" style="margin-bottom:30px;">
                <div class="sv-col sv-text-center sv-d-flex">
                    <img src="<?php echo $shopvote_base_url ?>/assets/images/woocommerce-logo.png" alt="WooCommerce" class="sv-img-fluid sv-mx-auto sv-my-auto">
                </div>
                <div class="sv-col sv-text-center sv-d-flex">
                    <span class="sv-h1 sv-mx-auto sv-my-auto">+</span>
                </div>
                <div class="sv-col sv-text-center sv-d-flex">
                    <img src="<?php echo $shopvote_base_url ?>/assets/images/bg-golden-seal.png" alt="SHOPVOTE" class="sv-img-fluid sv-mx-auto sv-my-auto">
                </div>
            </div>
            <div class="sv-row" style="margin-bottom: 30px;">
                <h1><?php esc_html_e('SHOPVOTE-Login', 'shopvote'); ?></h1>
                <p><?php esc_html_e('Enter your SHOPVOTE credentials (username and password) that you have received for the SHOPVOTE merchant area here. After logging in, you can configure the plugin.', 'shopvote'); ?></p>
            </div>
            <div class="messages" style="margin-top: 10px;">
                <?php if (!empty($errorMessage)) { ?>
                    <div class="notice notice-error is-dismissible sv-notice"><p><?php esc_html_e($errorMessage); ?></p></div>
                <?php } ?>
            </div>
            <div id="shop_vote_login">
                <form action="#" method="POST">
                    <div class="sv-form-group sv-row">
                        <label class="sv-col-sm-4 sv-col-form-label" for="InputUsername1"><?php esc_html_e('SHOPVOTE Username', 'shopvote'); ?></label>
                        <div class="sv-col-sm-8">
                            <input name="username" type="text" class="sv-form-control" id="InputUsername1" aria-describedby="usernameHelp" placeholder="<?php esc_attr_e('Example', 'shopvote'); ?>: 12345abcd" required>
                        </div>
                    </div>
                    <div class="sv-form-group sv-row">
                        <label class="sv-col-sm-4 sv-col-form-label" for="InputPassword1"><?php esc_html_e('SHOPVOTE Password', 'shopvote');?></label>
                        <div class="sv-col-sm-8">
                            <input name="password" type="password" class="sv-form-control" id="InputPassword1" placeholder="<?php esc_attr_e('Password', 'shopvote'); ?>" required>
                        </div>
                    </div>
                    <div class="sv-row" style="margin-top:40px;">
                        <div class="sv-col-xs-12 sv-col-sm-7 create-account">
                            <p><?php esc_html_e('Don\'t have a SHOPVOTE account yet?', 'shopvote');?><br><a href="https://www.shopvote.de/shop-kostenlos-eintragen" target="_blank"><?php esc_html_e('Register now for free', 'shopvote'); ?><!--Jetzt kostenlos registrieren--></a>.</p>
                        </div>
                        <div class="sv-col-xs-12 sv-col-sm-5">
                            <button type="submit" class="sv-btn sv-btn-primary shopvote-login sv-btn-block"><?php esc_html_e('Connect to SHOPVOTE', 'shopvote'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
