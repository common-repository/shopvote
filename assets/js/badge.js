window.addEventListener('DOMContentLoaded',function () {
    if ((typeof shopvote_settings !== 'object')
        || (shopvote_settings.badge_visible !== true)
        || (typeof shopvote_settings.user_shop !== 'string')
        || !(shopvote_settings.user_shop.length > 0)
        || (typeof shopvote_settings.badge_type !== 'number')
        || (typeof shopvote_settings.badge_position_h !== 'string')
        || (typeof shopvote_settings.badge_position_v !== 'string')
        || (typeof shopvote_settings.badge_distance_h !== 'number')
        || (typeof shopvote_settings.badge_distance_v !== 'number')
    ) {
        return;
    }
    createRBadge(
        shopvote_settings.user_shop,
        shopvote_settings.badge_type,
        ('https:' === document.location.protocol ? 'https' : 'http'),
        shopvote_settings.badge_distance_h,
        shopvote_settings.badge_distance_v,
        shopvote_settings.badge_position_h,
        shopvote_settings.badge_position_v
    );
});
