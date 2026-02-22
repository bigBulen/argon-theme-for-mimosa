<?php
/*
Template Name: 时间轴日历
*/

get_header(); ?>

<section class="banner" style="background-image: url(<?php echo get_option('argon_banner_background_image'); ?>);">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="banner-text">
                    <h1 class="banner-title"><?php the_title(); ?></h1>
                    <p class="banner-subtitle">游戏发售日期 · 展会时间 · 重要事件</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <article class="post post-full card bg-white shadow-sm border-0">
                <div class="post-content">
                    <?php
                    // 显示页面内容（如果有的话）
                    if (have_posts()) :
                        while (have_posts()) : the_post();
                            if (get_the_content()) {
                                echo '<div class="timeline-page-intro">';
                                the_content();
                                echo '</div>';
                            }
                        endwhile;
                    endif;
                    
                    // 显示时间轴日历
                    echo do_shortcode('[argon_timeline]');
                    ?>
                </div>
            </article>
        </div>
    </div>
</div>

<style>
.timeline-page-intro {
    padding: 20px;
    margin-bottom: 30px;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.timeline-page-intro p {
    margin-bottom: 10px;
    color: #5a6c7d;
    line-height: 1.6;
}

.banner-subtitle {
    opacity: 0.8;
    font-size: 16px;
    margin-top: 10px;
}
</style>

<?php get_footer(); ?>