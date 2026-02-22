<?php get_header(); ?>

<div class="page-information-card-container"></div>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'single' );

			if ( is_singular( 'post' ) ) {
				$categories = get_the_category();
				if (!empty($categories) && !is_wp_error($categories)) {
					$series_category = $categories[0];
					$category_chain_links = array();
					$category_chain_ids = array_reverse(get_ancestors($series_category->term_id, 'category'));
					$category_chain_ids[] = $series_category->term_id;
					foreach ($category_chain_ids as $cid) {
						$cat_obj = get_category($cid);
						if ($cat_obj && !is_wp_error($cat_obj)) {
							$category_chain_links[] = '<a href="' . esc_url(get_category_link($cat_obj->term_id)) . '">' . esc_html($cat_obj->name) . '</a>';
						}
					}
					$category_chain_display = implode(' -> ', $category_chain_links);
					$series_posts = get_posts(array(
						'cat' => $series_category->term_id,
						'posts_per_page' => -1,
						'fields' => 'ids',
						'orderby' => 'date',
						// 正序：最早文章为 1，最新文章为 N
						'order' => 'ASC'
					));
					$series_position_text = '';
				if (!empty($series_posts)) {
					$position = array_search(get_the_ID(), $series_posts);
					$series_position_text = ($position !== false ? ($position + 1) : '?') . '/' . count($series_posts);
				}
				echo '<div class="post-navigation card shadow-sm post-series-card">';
				echo '<div class="post-series-top">';
				echo '<div class="post-series-icon"><i class="fa fa-folder-open" aria-hidden="true"></i></div>';
				echo '<div class="post-series-main">';
				echo '<div class="post-series-label">系列文章</div>';
				echo '<div class="post-series-name">' . $category_chain_display . '</div>';
				echo '</div>';
				if (!empty($series_position_text)) {
					echo '<div class="post-series-order"> Contents: ' . esc_html($series_position_text) . '</div>';
				}
				echo '</div>';
				echo '<div class="post-series-license-divider"></div>';
				echo '<div class="post-series-license">';
				echo '<div class="post-series-icon"><i class="fa fa-balance-scale" aria-hidden="true"></i></div>';
				echo '<div class="post-series-main">';
				echo '<div class="post-series-label">License</div>';
				echo '<div class="post-series-license-text">许可协议：<a href="https://creativecommons.org/licenses/by-sa/4.0/deed.zh" target="_blank" rel="noopener">CC BY-SA 4.0</a></div>';
				echo '<div class="post-original-link">原文链接：<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_permalink()) . '</a></div>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}

			if (get_option("argon_show_sharebtn") != 'false') {
				get_template_part( 'template-parts/share' );
			}

			if (comments_open() || get_comments_number()) {
				comments_template();
			}

				if (get_previous_post() || get_next_post()){
					echo '<div class="post-navigation card shadow-sm">';
					if (get_previous_post()){ 
						previous_post_link('<div class="post-navigation-item post-navigation-pre"><span class="page-navigation-extra-text"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>' . __("上一篇", 'argon') . '</span>%link</div>' , '%title');
					}else{
						echo '<div class="post-navigation-item post-navigation-pre"></div>';
					}
					if (get_next_post()){
						next_post_link('<div class="post-navigation-item post-navigation-next"><span class="page-navigation-extra-text">' . __("下一篇", 'argon') . ' <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></span>%link</div>' , '%title');
					}else{
						echo '<div class="post-navigation-item post-navigation-next"></div>';
					}
					echo '</div>';
				}
			}

			$relatedPosts = get_option('argon_related_post', 'disabled');
			if ($relatedPosts != "disabled"){
				global $post;
				$cat_array = array();
				if (strpos($relatedPosts, 'category') !== false){
					$cats = get_the_category($post -> ID);
					if ($cats){
						foreach($cats as $key1 => $cat) {
							$cat_array[$key1] = $cat -> slug;
						}
					}
				}
				$tag_array = array();
				if (strpos($relatedPosts, 'tag') !== false){
					$tags = get_the_tags($post -> ID);
					if ($tags){
						foreach($tags as $key2 => $tag) {
							$tag_array[$key2] = $tag -> slug;
						}
					}
				}	
				$query = new WP_Query(array(
					'posts_per_page' => get_option('argon_related_post_limit' , '10'),
					'order' => get_option('argon_related_post_sort_order', 'DESC'),
					'orderby' => get_option('argon_related_post_sort_orderby', 'date'),
					'meta_key' => 'views',
					'post__not_in' => array($post -> ID),
					'tax_query' => array(
						'relation' => 'OR',
						array(
							'taxonomy' => 'category',
							'field' => 'slug',
							'terms' => $cat_array,
							'include_children' => false
						),
						array(
							'taxonomy' => 'post_tag',
							'field' => 'slug',
							'terms' => $tag_array,
						)
					)
				));
				if ($query -> have_posts()) {
					echo '<div class="related-posts card shadow-sm">
                    <h2 class="post-comment-title" style="margin-top: 1.2rem;margin-left: 1.5rem;margin-right: 1.5rem;">
                    <i class="fa fa-book"></i>
			        <span>' . __("推荐文章", 'argon') . ' · '.$category_chain_display.' 系列</span>
		            </h2>
		            <div style="overflow-x: auto;padding: 1.5rem;padding-top: 0.8rem;padding-bottom: 0.8rem;}">';
					while ($query -> have_posts()) {
						$query -> the_post();
						$hasThumbnail = argon_has_post_thumbnail(get_the_ID());
						echo '<a class="related-post-card" href="' . get_the_permalink() . '">';
						echo '<div class="related-post-card-container' . ($hasThumbnail ? ' has-thumbnail' : '') . '">
							<div class="related-post-title clamp" clamp-line="3">' . get_the_title() . '</div>
							<i class="related-post-arrow fa fa-chevron-right" aria-hidden="true"></i>
							</div>';
						if ($hasThumbnail){
							echo '<img class="related-post-thumbnail lazyload" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAABBJREFUeNpi+P//PwNAgAEACPwC/tuiTRYAAAAASUVORK5CYII=" data-original="' .  argon_get_post_thumbnail(get_the_ID()) . '"/>';
						}
						echo '</a>';
					}
					echo '</div></div>';
					wp_reset_query();
				}
			}

		endwhile;
		?>

<?php get_footer(); ?>
