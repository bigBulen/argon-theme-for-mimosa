<?php get_header(); ?>

<div class="page-information-card-container"></div>

<?php get_sidebar(); ?>

<?php
    $collapse_mode = isset($_GET['apex_media_collapse_mode']) ? sanitize_text_field($_GET['apex_media_collapse_mode']) : get_option('apex_media_collapse_mode', 'adjacent');
    if (!in_array($collapse_mode, ['adjacent','all'], true)) {
        $collapse_mode = 'adjacent';
    }
    $collapse_all = ($collapse_mode === 'all');
?>

<div id="primary" class="content-area" data-collapse-mode="<?php echo esc_attr($collapse_mode); ?>">
	<main id="main" class="site-main article-list article-list-home" role="main">
    <?php
        $feed = argon_get_home_feed_combined();
        $pending_media = [];
        $group_index = 0;
        $first_media_rendered = false;
        $collapse_all_toggle_rendered = false;
        $collapse_all_group_id = 'apex-media-collapse-all-' . (isset($feed['paged']) ? intval($feed['paged']) : 1);
        $media_total_on_page = 0;
        if ($collapse_all && !empty($feed['items'])) {
            foreach ($feed['items'] as $it) {
                if (!empty($it['kind']) && $it['kind'] === 'media') {
                    $media_total_on_page++;
                }
            }
        }

        $render_media = function($media_item) {
            set_query_var('apex_media_item', $media_item);
            get_template_part('template-parts/content-media-preview');
        };

        $flush_media_adjacent = function() use (&$pending_media, &$render_media, &$group_index) {
            if (empty($pending_media)) return;
            if (count($pending_media) >= 2) {
                $group_id = 'apex-media-collapse-' . $group_index++;
                $first = array_shift($pending_media);
                $render_media($first);
                $collapsed_count = count($pending_media);
                ?>
                <article class="media-feed-card media-feed-collapse-placeholder card shadow-sm" data-collapse-target="<?php echo esc_attr($group_id); ?>">
                    <div class="media-feed-collapse-placeholder__inner">
                        <div class="media-feed-collapse-placeholder__text">
                            <strong>有 <?php echo intval($collapsed_count); ?> 个连续内容</strong>
                        </div>
                        <button class="media-feed-collapse-placeholder__btn" type="button" data-collapse-target="<?php echo esc_attr($group_id); ?>">展开</button>
                    </div>
                </article>
                <div class="media-feed-collapsed-list" id="<?php echo esc_attr($group_id); ?>">
                    <?php foreach ($pending_media as $media) { $render_media($media); } ?>
                </div>
                <?php
            } else {
                foreach ($pending_media as $media) { $render_media($media); }
            }
            $pending_media = [];
        };

        if (!empty($feed['items'])) :
            foreach ($feed['items'] as $item) :
                if ($item['kind'] === 'media') {
                    if ($collapse_all) {
                        if (!$first_media_rendered) {
                            $render_media($item['media']);
                            $first_media_rendered = true;
                            if ($media_total_on_page > 1 && !$collapse_all_toggle_rendered) {
                                $collapsed_count = $media_total_on_page - 1;
                                ?>
                                <article class="media-feed-card media-feed-collapse-placeholder card shadow-sm" data-collapse-group="<?php echo esc_attr($collapse_all_group_id); ?>" style="border-radius: var(--card-radius);">
                                    <div class="media-feed-collapse-placeholder__inner">
                                        <div class="media-feed-collapse-placeholder__text">
                                            <strong>*本页已折叠 <?php echo intval($collapsed_count); ?> 个条目</strong>
                                        </div>
                                        <button class="media-feed-collapse-placeholder__btn" type="button" data-collapse-group="<?php echo esc_attr($collapse_all_group_id); ?>">展开</button>
                                    </div>
                                </article>
                                <?php
                                $collapse_all_toggle_rendered = true;
                            }
                        } else {
                            ?>
                            <div class="media-feed-collapsed-item" data-collapse-group="<?php echo esc_attr($collapse_all_group_id); ?>">
                                <?php $render_media($item['media']); ?>
                            </div>
                            <?php
                        }
                    } else {
                        $pending_media[] = $item['media'];
                    }
                    continue;
                }

                if (!$collapse_all && !empty($pending_media)) { $flush_media_adjacent(); }

                $post = $item['post'];
                if (!($post instanceof WP_Post)) {
                    continue;
                }
                setup_postdata($post);
                $post_type = get_post_type($post);
                if ($post_type == 'shuoshuo') {
                    get_template_part('template-parts/content-shuoshuo-preview');
                } elseif ($post_type == 'site_notice') {
                    get_template_part('template-parts/content-site_notice');
                } else {
                    get_template_part('template-parts/content-preview', get_option('argon_article_list_layout', '1'));
                }
            endforeach;

            if (!$collapse_all) {
                $flush_media_adjacent();
            }

            wp_reset_postdata();
            echo argon_render_home_feed_pagination($feed['total'], $feed['per_page'], $feed['paged']);
        endif;
    ?>
    <script>
    (function() {
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-collapse-target],[data-collapse-group]');
            if (!btn) return;
            var targetId = btn.getAttribute('data-collapse-target');
            if (targetId) {
                var list = document.getElementById(targetId);
                if (!list) return;
                list.classList.add('show');
            }
            var groupId = btn.getAttribute('data-collapse-group');
            if (groupId) {
                var items = document.querySelectorAll('[data-collapse-group="' + groupId + '"]');
                items.forEach(function(el){ el.classList.add('show'); });
            }
            var placeholder = btn.closest('.media-feed-collapse-placeholder');
            if (placeholder) {
                placeholder.remove();
            }
        });
    })();
    </script>

<?php get_footer(); ?>


