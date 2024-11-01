<?php
namespace Shopvote;

use Exception;
use ShopvotePlugin;

require_once __DIR__ . '/Settings.php';

/*
 * ToDo: Proper error management and validation.
 */
class ShopVoteApi {
    const TIMEOUT_CONNECT_MS = 1000;
    const TIMEOUT_RECEIVE_MS = 1500;

    private $apiKey       = 'dcba6fe3e6ae3dc2c5a65a3c489d7053487c432m0abe2429f17844b04d2c7b8f';
    private $apiSecret    = 'ded0ab1e9d96a43070104f8ae6f930183f5O9810ega95a981a1b33a398a9b7ef';
    private $apiTokenUrl  = 'https://api.shopvote.de/auth';
    private $apiLoginUrl  = 'https://api.shopvote.de/external/login';
    private $apiReviewUrl = 'https://api.shopvote.de/product-reviews/v2/reviews?sku=:sku';
    private $userAgent = '';

    private function getUserAgent() {
        if (!empty($this->userAgent)) {
            return $this->userAgent;
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $this->userAgent = 'App.';

        if (($shopId = Settings::getUserShop()) > 0) {
            $this->userAgent .= $shopId.'.';
        }

        $this->userAgent .= 'SB3 SHOPVOTE/'.ShopvotePlugin::VERSION.' Wordpress/'.get_bloginfo('version');

        // get the plugins data
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $woo = get_plugin_data(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php');
            $this->userAgent .= ' WooCommerce/'.$woo['Version'];
        }
        return $this->userAgent;
    }

    private function throwCurlError($errorCode, $errorMessage) {
        throw new \RuntimeException(sprintf(
            // translators: %1$d represents the CURL error code, %2$s the CURL error message.
            __('A connection to the SHOPVOTE server could not be established. (Code: %1$d; Message: %2$s)', 'shopvote'),
            $errorCode,
            $errorMessage
        ));
    }

    private function throwInvalidDataError() {
        throw new \RuntimeException(__(
            'Invalid data was received from the SHOPVOTE-server. Please contact SHOPVOTE support.',
            'shopvote'
        ));
    }

    public function receiveBearerToken() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiTokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::TIMEOUT_RECEIVE_MS);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT_CONNECT_MS);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
        $headers = [
            'apikey: '.$this->apiKey,
            'apisecret: '.$this->apiSecret,
            'origin: '.home_url(),
        ];
        if ($token = Settings::getToken()) {
            $headers[] = 'shopapikey: '.$token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $errorCode = curl_errno($ch);
        $errorMessage = curl_error($ch);
        curl_close($ch);

        if ($errorCode > 0) {
            $this->throwCurlError($errorCode, $errorMessage);
        }

        $data = json_decode($result, true);
        if (isset($data['Code'])
            && $data['Code'] === 200
            && isset($data['Token'])
        ) {
            return $data['Token'];
        }

        $this->throwInvalidDataError();
    }

    public function authShopVoteUser($username, $password) {
        $bearerToken = $this->receiveBearerToken();
        if ($bearerToken === null) {
            throw new \RuntimeException('');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiLoginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::TIMEOUT_RECEIVE_MS);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT_CONNECT_MS);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'token: Bearer '.$bearerToken,
            'username: '.$username,
            'password: '.md5($password)
        ]);

        $result = curl_exec($ch);
        $errorCode = curl_errno($ch);
        $errorMessage = curl_error($ch);
        curl_close($ch);

        if ($errorCode > 0) {
            $this->throwCurlError($errorCode, $errorMessage);
        }
        $data = json_decode($result, true);

        if ($data['Code'] !== 200) {
            $this->throwInvalidDataError();
        }

        $tokenParts = explode('.', $data['Token']);
        $tokenPayload = base64_decode($tokenParts[1]);
        $tokenPayload = json_decode($tokenPayload, true);

        Settings::setUserShop($tokenPayload['shopid']);
        Settings::setToken($tokenPayload['apikey']);
    }

    public function fetchReviewsForSku($sku) {
        $bearerToken = $this->receiveBearerToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, strtr($this->apiReviewUrl, [':sku' => urlencode($sku)]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, self::TIMEOUT_RECEIVE_MS);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT_CONNECT_MS);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'token: Bearer '.$bearerToken,
            'authorization: Bearer '.$bearerToken
        ]);

        $result = curl_exec($ch);
        $errorCode = curl_errno($ch);
        $errorMessage = curl_error($ch);
        curl_close($ch);

        if ($errorCode > 0) {
            $this->throwCurlError($errorCode, $errorMessage);
        }

        // ToDo: Validate Response.
        $data = json_decode($result, true);
        return $data;
    }

}
