<?php
/**
 * Site Notices (GitHub Issue style) - CPT + admin meta + frontend rendering & time-matched insertion
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Register Custom Post Type: site_notice
 */
function apex_register_site_notice_cpt() {
    $labels = array(
        'name'               => __('站点提示', 'argon'),
        'singular_name'      => __('站点提示', 'argon'),
        'add_new'            => __('新增提示', 'argon'),
        'add_new_item'       => __('新增提示', 'argon'),
        'edit_item'          => __('编辑提示', 'argon'),
        'new_item'           => __('新提示', 'argon'),
        'view_item'          => __('查看提示', 'argon'),
        'search_items'       => __('搜索提示', 'argon'),
        'not_found'          => __('暂无提示', 'argon'),
        'not_found_in_trash' => __('没有已遗弃的提示', 'argon'),
        'menu_name'          => __('站点提示', 'argon'),
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => false,
        'rewrite'            => false,
        'exclude_from_search' => true, // 即使 public, 也不希望它出现在搜索结果里
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-tag',
        'supports'           => array('title', 'editor', 'author', 'excerpt'),
    );
    register_post_type('site_notice', $args);
}
add_action('init', 'apex_register_site_notice_cpt');

/**
 * 如果有人试图直接访问单个 site_notice 页面, 将他们重定向到首页.
 */
function apex_redirect_single_site_notice() {
    if (is_singular('site_notice')) {
        wp_redirect(home_url(), 301);
        exit;
    }
}
add_action('template_redirect', 'apex_redirect_single_site_notice');

/**
 * Admin Meta Box: type, username, avatar, link, date override
 */
function apex_site_notice_add_meta_boxes() {
    add_meta_box(
        'apex_site_notice_meta',
        __('提示属性', 'argon'),
        'apex_site_notice_meta_box_render',
        'site_notice',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'apex_site_notice_add_meta_boxes');

function apex_site_notice_meta_box_render($post) {
    wp_nonce_field('apex_site_notice_save_meta', 'apex_site_notice_meta_nonce');
    $type   = get_post_meta($post->ID, 'site_notice_type', true);
    $date   = get_post_meta($post->ID, 'site_notice_date_override', true); // YYYY-MM-DD
    ?>
    <p><label for="site_notice_type"><?php _e('类型', 'argon'); ?></label>
        <select id="site_notice_type" name="site_notice_type" style="width:100%">
            <?php
            $options = array(
                'fixed' => 'Fixed',
                'added' => 'Added',
                'bug'   => 'New bug',
                'other' => 'Other',
                'closed'=> 'Closed',
                'completed' => 'Completed',
            );
            foreach ($options as $val => $label) {
                echo '<option value="'.esc_attr($val).'" '.selected($type, $val, false).'>'.esc_html($label).'</option>';
            }
            ?>
        </select>
    </p>
    <p><label for="site_notice_date_override"><?php _e('覆盖日期（YYYY-MM-DD，可选）', 'argon'); ?></label>
        <input type="text" id="site_notice_date_override" name="site_notice_date_override" value="<?php echo esc_attr($date); ?>" style="width:100%" placeholder="2025-10-01">
        <small><?php _e('不填则使用“发布时间”。用户名、头像、链接将自动获取发布者的信息。', 'argon'); ?></small>
    </p>
    <?php
}

function apex_site_notice_save_meta($post_id) {
    if (!isset($_POST['apex_site_notice_meta_nonce']) ||
        !wp_verify_nonce($_POST['apex_site_notice_meta_nonce'], 'apex_site_notice_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (get_post_type($post_id) !== 'site_notice') { return; }
    $fields = array(
        'site_notice_type' => 'sanitize_text_field',
        'site_notice_date_override' => 'sanitize_text_field',
    );
    foreach ($fields as $key => $sanitize) {
        if (isset($_POST[$key])) {
            $val = call_user_func($sanitize, wp_unslash($_POST[$key]));
            update_post_meta($post_id, $key, $val);
        }
    }
}
add_action('save_post', 'apex_site_notice_save_meta');

/**
 * Helper: format and render notice card
 */
function apex_site_notice_get_date_ts($post) {
    $override = get_post_meta($post->ID, 'site_notice_date_override', true);
    if (!empty($override)) {
        $ts = strtotime($override . ' 00:00:00');
        if ($ts) return $ts;
    }
    return get_post_timestamp($post);
}

function apex_site_notice_type_to_class($type) {
    switch ($type) {
        case 'fixed': return 'gh-notice--success';
        case 'added': return 'gh-notice--info';
        case 'bug':   return 'gh-notice--warning';
        case 'closed':return 'gh-notice--neutral';
        case 'completed': return 'gh-notice--success';
        default: return 'gh-notice--neutral';
    }
}

function apex_render_site_notice_card($post) {
    $type   = get_post_meta($post->ID, 'site_notice_type', true);
    $dateTs = apex_site_notice_get_date_ts($post);
    $dateDisplay = date('Y.m.d', $dateTs);

    // 自动获取作者信息
    $author_id    = $post->post_author;
    $user_display = get_the_author_meta('display_name', $author_id);
    $avatar_url   = get_avatar_url($author_id);
    $author_link  = get_author_posts_url($author_id);

    $class = 'gh-like-notice ' . apex_site_notice_type_to_class($type);
    $title = get_the_title($post);
    $desc  = has_excerpt($post) ? get_the_excerpt($post) : wp_strip_all_tags($post->post_content);
    $desc  = wp_trim_words($desc, 120, '…');

    // 文案：类似 “mikumifa closed this as completed on Aug 31”
    // 我们按需求混排：“{Title} on {YYYY.MM.DD}：{中文描述}”
    $main_content = esc_html($title) . ' on ' . '<strong>' . esc_html($dateDisplay) . '</strong>' . '：' . esc_html($desc);

    ?>
    <article class="post card shadow-sm border-0 <?php echo esc_attr($class); ?>" style="border-radius: var(--card-radius);">
        <div class="gh-notice-inner">
            <?php if (!empty($avatar_url)) { ?>
                <a href="<?php echo esc_url($author_link); ?>" target="_self" rel="noopener author">
                    <img class="gh-notice-avatar" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user_display); ?>" loading="lazy"/>
                </a>
            <?php } else { ?>
                <span class="gh-notice-icon" aria-hidden="true"></span>
            <?php } ?>
            <div class="gh-notice-text">
                <?php if (!empty($user_display)) { ?>
                    <a href="<?php echo esc_url($author_link); ?>" target="_self" rel="noopener author" class="gh-notice-user"><?php echo esc_html($user_display); ?></a>
                <?php } ?>
                <span class="gh-notice-content"><?php echo $main_content; ?></span>
            </div>
        </div>
    </article>
    <?php
}

/**
 * Add site notices to the main homepage query so they appear chronologically.
 */
function apex_add_site_notices_to_home_query($query) {
    // Only modify the main query on the homepage (including paginated pages)
    if ($query->is_home() && $query->is_main_query() && !$query->is_singular()) {
        // 直接设置 post_type, 确保我们的类型被包含
        $query->set('post_type', array('post', 'shuoshuo', 'site_notice'));
    }
}
add_action('pre_get_posts', 'apex_add_site_notices_to_home_query', 99);

/**
 * Enqueue CSS (use theme style.css already loaded, but provide a small file if needed)
 * For Argon, style.css is loaded; we rely on CSS appended there. This hook is kept for flexibility.
 */
function apex_site_notice_enqueue_assets() {
    wp_enqueue_style(
        'apex-site-notices',
        get_template_directory_uri() . '/assets/css/apex-site-notices.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/apex-site-notices.css')
    );
}
add_action('wp_enqueue_scripts', 'apex_site_notice_enqueue_assets');