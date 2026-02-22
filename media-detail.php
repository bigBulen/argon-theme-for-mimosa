<?php
/**
 * 作品详情页 /acgn/{slug}
 */
if (!defined('ABSPATH')) exit;

global $apex_media_list;
$slug = get_query_var('apex_media_slug');
$media = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->media_item_get_by_slug($slug) : null;

if (!$media) {
    status_header(404);
    nocache_headers();
    include get_query_template('404');
    exit;
}

$tags = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->media_item_get_tags($media['id']) : [];
$score_10 = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->resolve_score_10_from_row($media) : 0;
$grade = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->get_grade_from_score_10($score_10) : ['label' => '', 'desc' => ''];
$type_label = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->map_media_type_to_label($media['type'] ?? '') : ($media['type'] ?? '');
$finished = (!empty($media['finished_at']) && $media['finished_at'] !== '0000-00-00 00:00:00') ? substr($media['finished_at'], 0, 10) : '';
$season_text = $media['season_text'] ?? '';
$review_html = wpautop(wp_kses_post($media['review'] ?? ''));
$cover = '';
$att_id = isset($media['cover_attachment_id']) ? intval($media['cover_attachment_id']) : 0;
if ($att_id > 0) {
    $cover = wp_get_attachment_image_url($att_id, 'full');
}
if (empty($cover) && !empty($media['cover_source_url'])) {
    $cover = $media['cover_source_url'];
}
$bgm_url = $media['bgm_url'] ?? '';
$type_flag = ($media['type'] ?? '') === 'galgame' ? 'GAL' : 'ANIME';
$finished_label = ($media['type'] ?? '') === 'galgame' ? '通关时间' : '完成时间';
$season_label = ($media['type'] ?? '') === 'galgame' ? '发售' : '放送';
$hero_style = $cover ? '--media-hero:url(' . esc_url($cover) . ');' : '';
$hero_class = $cover ? 'media-detail__hero card shadow-sm has-bg' : 'media-detail__hero card shadow-sm';
$year = $media['year'] ?? '';
$quarter_raw = $media['quarter'] ?? '';
$quarter_map = [
    'winter' => '冬季',
    'spring' => '春季',
    'summer' => '夏季',
    'autumn' => '秋季',
    'fall' => '秋季',
];
$quarter = $quarter_map[strtolower($quarter_raw)] ?? $quarter_raw;
$status = $media['status'] ?? '';
$status_map = [
    'watched' => '已完成',
    'watching' => '进行中',
    'want' => '想看',
];
$status_label = $status_map[$status] ?? '';
$created_at = (!empty($media['created_at']) && $media['created_at'] !== '0000-00-00 00:00:00') ? substr($media['created_at'], 0, 10) : '';
$updated_at = (!empty($media['updated_at']) && $media['updated_at'] !== '0000-00-00 00:00:00') ? substr($media['updated_at'], 0, 10) : '';
$show_on_home_feed = !empty($media['show_on_home_feed']);

$score_tone = 'gray';
if ($score_10 >= 9.5) {
    $score_tone = 'purple';
} elseif ($score_10 >= 9) {
    $score_tone = 'gold';
} elseif ($score_10 >= 8.5) {
    $score_tone = 'blue';
} elseif ($score_10 >= 7.5) {
    $score_tone = 'green';
} elseif ($score_10 >= 6.5) {
    $score_tone = 'teal';
} elseif ($score_10 >= 5.5) {
    $score_tone = 'amber';
} elseif ($score_10 > 0) {
    $score_tone = 'rose';
}

// 评论承载页：按用户要求固定为 ID 4974
$comment_host_id = 4974;
if (get_post_status($comment_host_id) !== 'publish') {
    $comment_host_id = 0;
}

get_header();
?>

<div class="container media-detail-page">
    <div class="<?php echo esc_attr($hero_class); ?>" <?php echo $hero_style ? 'style="' . esc_attr($hero_style) . ' border-radius: var(--card-radius);"' : ''; ?>>
        <div class="media-detail__content-grid">
            <div class="media-detail__cover">
                <?php if (!empty($cover)): ?>
                    <img loading="lazy" src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($media['title'] ?? ''); ?>">
                <?php else: ?>
                    <div class="media-detail__cover-placeholder">暂无封面</div>
                <?php endif; ?>
            </div>
            <div class="media-detail__info">
                <div class="media-detail__topline">
                    <span class="badge badge-soft-primary"><?php echo esc_html($type_flag); ?></span>
                    <?php if (!empty($status_label)): ?>
                        <span class="media-detail__status"><?php echo esc_html($status_label); ?></span>
                    <?php endif; ?>
                    <?php if ($show_on_home_feed): ?>
                        <!-- <span class="media-detail__home-flag">首页展示</span> -->
                    <?php endif; ?>
                    <?php if (!empty($finished)): ?>
                        <span class="media-detail__pill"><?php echo esc_html($finished_label . '：' . $finished); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($season_text)): ?>
                        <span class="media-detail__pill"><?php echo esc_html($season_label . '：' . $season_text); ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="media-detail__title"><?php echo esc_html($media['title'] ?? ''); ?></h1>
                <?php if (!empty($media['original_title'])): ?>
                    <p class="media-detail__subtitle"><?php echo esc_html($media['original_title']); ?></p>
                <?php endif; ?>
                <div class="media-detail__meta-inline">
                    <?php if (!empty($year)): ?>
                        <span class="media-detail__meta-chip">年份：<?php echo esc_html($year); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($quarter)): ?>
                        <span class="media-detail__meta-chip">季度：<?php echo esc_html($quarter); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($bgm_url)): ?>
                        <a class="media-detail__meta-chip media-detail__meta-chip--link no-pjax" href="<?php echo esc_url($bgm_url); ?>" target="_blank" rel="noopener">作品信息</a>
                    <?php endif; ?>
                </div>
                <div class="media-detail__score-block media-detail__score-block--<?php echo esc_attr($score_tone); ?>">
                    <?php if ($score_10 > 0): ?>
                        <span class="media-detail__score-main"><?php echo esc_html(number_format($score_10, 1)); ?><small class="media-detail__score-suffix">/10</small></span>
                        <div class="media-detail__score-subline">
                            <?php if (!empty($grade['label'])): ?>
                                <span class="media-detail__score-chip"><?php echo esc_html($grade['label']); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="media-detail__score-main muted">暂无评分</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($grade['desc'])): ?>
                    <p class="media-detail__grade-desc"><?php echo esc_html($grade['desc']); ?></p>
                <?php endif; ?>

                <div class="media-detail__meta-grid">
                    <div class="media-detail__meta-item">
                        <span class="media-detail__meta-label">类型</span>
                        <span><?php echo esc_html($type_label ?: $type_flag); ?></span>
                    </div>
                    <?php if (!empty($season_text)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label"><?php echo esc_html($season_label); ?></span>
                            <span><?php echo esc_html($season_text); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($finished)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label"><?php echo esc_html($finished_label); ?></span>
                            <span><?php echo esc_html($finished); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($status_label)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label">状态</span>
                            <span><?php echo esc_html($status_label); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($year)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label">年份</span>
                            <span><?php echo esc_html($year); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($quarter)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label">季度</span>
                            <span><?php echo esc_html($quarter); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($created_at)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label">条目添加</span>
                            <span><?php echo esc_html($created_at); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($updated_at)): ?>
                        <div class="media-detail__meta-item">
                            <span class="media-detail__meta-label">最后更新</span>
                            <span><?php echo esc_html($updated_at); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tags)): ?>
                    <div class="media-detail__tags">
                        <?php foreach ($tags as $tag): ?>
                            <span class="media-detail__tag"><?php echo esc_html($tag['name']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                $list_url = home_url('/anime/');
                if (($media['type'] ?? '') === 'galgame') {
                    $list_url = home_url('/galgame/');
                }
                ?>
                <div class="media-detail__links">
                    <a class="btn btn-primary btn-sm no-pjax" href="<?php echo esc_url(home_url('/')); ?>">返回首页</a>
                    <a class="media-detail__btn-list btn btn-sm no-pjax" href="<?php echo esc_url($list_url); ?>">作品列表</a>
                    <?php if (!empty($bgm_url)): ?>
                        <a class="btn btn-outline-primary btn-sm no-pjax" href="<?php echo esc_url($bgm_url); ?>" target="_blank" rel="noopener">查看条目</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> 
    
    <div class="media-detail__body card shadow-sm " style="border-radius: var(--card-radius);">
        <h2 class="media-detail__section-title">评价</h2>
        <div class="media-detail__content">
            <?php echo $review_html ?: '<p>暂无评价。</p>'; ?>
<script>
// 详情页禁用 PJAX：移除全局 pjax 绑定并在捕获阶段拦截点击
(function(){
  function hardDisablePjax(){
    if (window.$ && $.pjax) {
      $(document).off('click.pjax');
    }
  }
  document.addEventListener('DOMContentLoaded', function(){
    hardDisablePjax();
    document.addEventListener('click', function(e){
      var a = e.target.closest('.media-detail-page a');
      if (!a || !a.href) return;
      e.stopImmediatePropagation();
      e.preventDefault();
      window.location.href = a.href;
    }, true);
  });
})();
</script>
        </div>
    </div>
    
    <br><br>

    <?php if ($comment_host_id > 0): ?>
        <div class="media-detail__comments">
            <?php
            global $post;
            $apex_media_prev_post = $post;
            $comment_host_post = get_post($comment_host_id);

            if ($comment_host_post) {
                global $withcomments;
                $post = $comment_host_post;
                setup_postdata($post);
                $withcomments = true;
                comments_template();
                $withcomments = false;
                wp_reset_postdata();
            } else {
                echo '<div class="card shadow-sm"><div class="card-body"><p class="mb-0">评论容器页面不存在或未发布。</p></div></div>';
            }

            $post = $apex_media_prev_post;
            ?>
        </div>
    <?php else: ?>
        <div class="media-detail__comments card shadow-sm">
            <div class="card-body">
                <p class="mb-0">要启用评论，请创建一个 slug 为 <code>acgn-comments</code> 的页面并开启评论，或在后台设置选项 <code>apex_media_comment_post_id</code> 指向任意开启评论的页面。</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
