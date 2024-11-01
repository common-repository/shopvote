<?php
namespace Shopvote;

class Settings {
    const KEY = 'shopvote_settings';

    const OLD_CONFIG_TABLE = 'shopvote_user';

    private static $instance = null;

    private $settings = [
        'user_shop'        => '',
        'token'            => '',
        'badge_visible'    => false,
        'badge_type'       => 1,
        'badge_position_h' => 'right',
        'badge_position_v' => 'bottom',
        'badge_distance_h' => 10,
        'badge_distance_v' => 25,
        'product_reviews_enabled' => false,
        'shop_reviews_enabled'    => false,
    ];

    private function __construct() {
        $settings = get_option(self::KEY);
        if (is_array($settings)) {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            return;
        }
        $this->migrate();
    }

    private static function instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function migrate() {
        global $wpdb;
        $tableName = $wpdb->prefix . self::OLD_CONFIG_TABLE;

        if ($wpdb->get_var("SHOW TABLES LIKE '".$tableName."'") != $tableName) {
            // Nothing to migrate.
            return;
        }

        $oldData = $wpdb->get_row( "SELECT * FROM ".$tableName." LIMIT 1", 'ARRAY_A');
        if (!empty($oldData)) {
            $map = [
                'badge_visible' => 'badge',
                'product_reviews_enabled' => 'reviews',
                'shop_reviews_enabled' => 'request',
                'badge_distance_h' => 'xdistance',
                'badge_distance_v' => 'ydistance',
            ];
            foreach ($this->settings as $key => $value) {
                $tKey = $key;
                if (isset($map[$key])) {
                    $tKey = $map[$key];
                }
                if (isset($oldData[$tKey])) {
                    if (is_int($value)) {
                        $this->settings[$key] = (int)$oldData[$tKey];
                    } elseif (is_bool($value)) {
                        $this->settings[$key] = $oldData[$tKey] === 'visible';
                    } else {
                        $this->settings[$key] = (string)$oldData[$tKey];
                    }
                }
            }
            $this->persist();
            if (isset($oldData['badgePosition'])) {
                $this->setBadgePosition($oldData['badgePosition']);
            }
        }

        $wpdb->query('DROP TABLE '.$tableName.';');
    }

    private function persist() {
        update_option(self::KEY, $this->settings);
    }

    private function get($key) {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    private function set($key, $value, $persist = false) {
        if (isset($this->settings[$key]) && (gettype($this->settings[$key]) == gettype($value))) {
            $this->settings[$key] = $value;
            if ($persist) {
                $this->persist();
            }
        }
    }

    public static function clearAll() {
        delete_option(self::KEY);
    }

    /**
     * @return bool
     */
    public static function isSetup() {
        return !empty(self::getUserShop()) && !empty(self::getToken());
    }

    /**
     * @return string
     */
    public static function getUserShop() {
        return self::instance()->get('user_shop');
    }

    /**
     * @param string $userShop
     * @return void
     */
    public static function setUserShop($userShop) {
        self::instance()->set('user_shop', $userShop, true);
    }

    /**
     * @return string
     */
    public static function getToken() {
        return self::instance()->get('token');
    }

    /**
     * @param string $token
     * @return void
     */
    public static function setToken($token) {
        self::instance()->set('token', $token, true);
    }

    /**
     * @return bool
     */
    public static function isBadgeVisible() {
        return self::instance()->get('badge_visible');
    }

    /**
     * @return int
     */
    public static function getBadgeType() {
        return self::instance()->get('badge_type');
    }

    /**
     * @param bool $visible
     * @return void
     */
    public static function setBadgeVisibility($visible) {
        self::instance()->set('badge_visible', $visible, true);
    }

    /**
     * @return string
     */
    public static function getBadgePositionV() {
        return self::instance()->get('badge_position_v');
    }

    /**
     * @param string $position
     * @return void
     */
    public static function setBadgePositionV($position) {
        if (is_string($position) && in_array($position, ['top', 'bottom'])) {
            self::instance()->set('badge_position_v', $position, true);
        }
    }

    /**
     * @return string
     */
    public static function getBadgePositionH() {
        return self::instance()->get('badge_position_h');
    }

    /**
     * @param string $position
     * @return void
     */
    public static function setBadgePositionH($position) {
        if (is_string($position) && in_array($position, ['left', 'right'])) {
            self::instance()->set('badge_position_h', $position, true);
        }
    }

    /**
     * @return string
     */
    public static function getBadgePosition() {
        return self::getBadgePositionV().'/'.self::getBadgePositionH();
    }

    /**
     * @param string $position
     * @return void
     */
    public static function setBadgePosition($position) {
        $pa = explode('/', $position);
        if (isset($pa[0])) {
            self::setBadgePositionV($pa[0]);
        }
        if (isset($pa[1])) {
            self::setBadgePositionH($pa[1]);
        }
    }

    /**
     * @return int
     */
    public static function getBadgeDistanceV() {
        return self::instance()->get('badge_distance_v');
    }

    /**
     * @param int $distance
     * @return void
     */
    public static function setBadgeDistanceV($distance) {
        self::instance()->set('badge_distance_v', $distance, true);
    }

    /**
     * @return int
     */
    public static function getBadgeDistanceH() {
        return self::instance()->get('badge_distance_h');
    }

    /**
     * @param int $distance
     * @return void
     */
    public static function setBadgeDistanceH($distance) {
        self::instance()->set('badge_distance_h', $distance, true);
    }

    /**
     * @return bool
     */
    public static function isProductReviewsEnabled() {
        return self::instance()->get('product_reviews_enabled');
    }

    /**
     * @param bool $visible
     * @return void
     */
    public static function setProductReviewsEnabled($visible) {
        self::instance()->set('product_reviews_enabled', $visible, true);
    }

    /**
     * @return bool
     */
    public static function isShopReviewsEnabled() {
        return self::instance()->get('shop_reviews_enabled');
    }

    /**
     * @param bool $visible
     * @return void
     */
    public static function setShopReviewsEnabled($visible) {
        self::instance()->set('shop_reviews_enabled', $visible, true);
    }

    /**
     * @return string
     */
    public static function getLanguage() {
        return 'DE';
    }

}
