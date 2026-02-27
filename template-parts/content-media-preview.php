<?php
/*
* 这是 真·acgn作品条目的预览卡片，只是一个卡片
* 被用在主页和文章的短代码
*/

if (!defined('ABSPATH')) exit;

global $apex_media_list;
$media_item = get_query_var('apex_media_item');
if (!$media_item || !is_array($media_item)) {
    return;
}

$title = $media_item['title'] ?? '';
$cover = '';
$att_id = isset($media_item['cover_attachment_id']) ? intval($media_item['cover_attachment_id']) : 0;
if ($att_id > 0) {
    $cover = wp_get_attachment_image_url($att_id, 'large');
}
if (empty($cover) && !empty($media_item['cover_source_url'])) {
    $cover = $media_item['cover_source_url'];
}

$detail_url = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->get_media_detail_url($media_item) : '#';
$score_10 = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->resolve_score_10_from_row($media_item) : 0;
$grade = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->get_grade_from_score_10($score_10) : ['label' => '', 'desc' => ''];
$type_flag = ($media_item['type'] ?? '') === 'galgame' ? 'GAL' : 'ANIME';
$type_label = ($apex_media_list instanceof Apex_Media_List) ? $apex_media_list->map_media_type_to_label($media_item['type'] ?? '') : ($media_item['type'] ?? '');
$season_text = $media_item['season_text'] ?? '';
$finished = (!empty($media_item['finished_at']) && $media_item['finished_at'] !== '0000-00-00 00:00:00') ? substr($media_item['finished_at'], 0, 10) : '';
$review_text = wp_strip_all_tags($media_item['review'] ?? '');
if ($review_text === '' && !empty($media_item['legacy_post_id'])) {
    $legacy_review = get_post_meta(intval($media_item['legacy_post_id']), '_apex_review', true);
    if (!empty($legacy_review)) {
        $review_text = wp_strip_all_tags($legacy_review);
    }
}
$review_excerpt = $review_text;
if (function_exists('mb_strlen') && mb_strlen($review_text) > 120) {
    $review_excerpt = mb_substr($review_text, 0, 120) . '…';
} elseif (strlen($review_text) > 120) {
    $review_excerpt = substr($review_text, 0, 120) . '…';
}
$finished_label = ($media_item['type'] ?? '') === 'galgame' ? '通关时间' : '完成时间';
$season_label = ($media_item['type'] ?? '') === 'galgame' ? '发售' : '放送';
$home_flag = !empty($media_item['show_on_home_feed']);


$status = $media_item['status'] ?? ''; // watched / watching / want

$status_label = '';
$status_class = '';

if (($media_item['type'] ?? '') === 'galgame') {
    if ($status === 'watching') {
        $status_label = '进行中';
        $status_class = 'status-doing';
    } elseif ($status === 'watched') {
        $status_label = '已通关';
        $status_class = 'status-done';
    } elseif ($status === 'want') {
        $status_label = '想玩';
        $status_class = 'status-want';
    }
} else { // anime
    if ($status === 'watching') {
        $status_label = '进行中';
        $status_class = 'status-doing';
    } elseif ($status === 'watched') {
        $status_label = '已看完';
        $status_class = 'status-done';
    } elseif ($status === 'want') {
        $status_label = '想看';
        $status_class = 'status-want';
    }
}



$score_tone = 'gray';
if ($score_10 >= 9) {
    $score_tone = 'gold';
} elseif ($score_10 >= 8) {
    $score_tone = 'blue';
} elseif ($score_10 >= 7) {
    $score_tone = 'green';
}?>

<article class="media-feed-card card shadow-sm" style="border-radius: var(--card-radius);">
    <div class="media-feed-card__inner">
        <div class="media-feed-card__accent"></div>
        <div class="media-feed-card__cover">
            <a href="<?php echo esc_url($detail_url); ?>" aria-label="<?php echo esc_attr($title); ?>">
                <?php if (!empty($cover)): ?>
                    <img loading="lazy" src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($title); ?>">
                <?php else: ?>
                    <div class="media-feed-card__cover-placeholder">暂无封面</div>
                <?php endif; ?>
            </a>
        </div>
        <div class="media-feed-card__info">
            <div class="media-feed-card__topline">

        <?php if (!empty($status_label)): ?>
            <span class="media-feed-card__meta-pill media-feed-card__status <?php echo esc_attr($status_class); ?>">
                <?php echo esc_html($status_label); ?>
            </span>
        <?php endif; ?>

                <?php if ($home_flag): ?>
                    <!-- <span class="media-feed-card__meta-pill media-feed-card__meta-pill--highlight">首页展示</span> -->
                <?php endif; ?>
                <?php if (!empty($season_text)): ?>
                    <span class="media-feed-card__meta-pill"><?php echo esc_html($season_label . '：' . $season_text); ?></span>
                <?php endif; ?>
                <?php if (!empty($finished)): ?>
                    <span class="media-feed-card__meta-pill"><?php echo esc_html($finished_label . '：' . $finished); ?></span>
                <?php endif; ?>
            </div>
            <div class="media-feed-card__title-row">
                <h2 class="media-feed-card__title"><a href="<?php echo esc_url($detail_url); ?>"><?php echo esc_html($title); ?></a></h2>

                <div class="media-feed-card__score-badge media-feed-card__score-badge--<?php echo esc_attr($score_tone); ?>">
                    <?php if ($score_10 > 0): ?>
                        <span class="media-feed-card__score-main">
                            <?php echo esc_html(number_format($score_10, 1)); ?>
                            <small class="media-feed-card__score-suffix">/10</small>
                        </span>
                    <?php else: ?>
                        <span class="media-feed-card__score-main media-feed-card__score-empty">暂无评分</span>
                    <?php endif; ?>
                </div>


            </div>
            <div class="media-feed-card__meta-line">
                <span class="media-feed-card__type-text"><?php echo esc_html($type_label ?: $type_flag); ?></span>
                <?php if (!empty($grade['label'])): ?>
                    <span class="media-feed-card__grade"><?php echo esc_html($grade['label']); ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($review_excerpt)): ?>
                <p class="media-feed-card__review line-clamp-3"><?php echo esc_html($review_excerpt); ?></p>
            <?php endif; ?>
            <div class="media-feed-card__footer">
                <a class="media-feed-card__btn" href="<?php echo esc_url($detail_url); ?>">完整信息</a>
            </div>
        </div>
    </div>
</article>
