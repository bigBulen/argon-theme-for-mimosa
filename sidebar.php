<?php if (get_option('argon_page_layout', 'double') == 'single') {
	return;
} ?>
<div id="sidebar_mask"></div>
<!--  
     注意：
	 侧边栏的作者卡片是会自动吸附的，且只会随着id为leftbar_part1的卡片底部到达顶部时自动吸附。
	 因此，如果你想要在侧边栏中添加其他卡片，请确保它们的id不是leftbar_part1。
	 （也就是说，作者简介卡片上方的卡片必须是leftbar_part1，且只能有一个）
	 （原本作者卡片上面是菜单栏卡片，被我改成时间卡片了。）
	                                    ——by Mimosa
 -->
<aside id="leftbar" class="leftbar widget-area" role="complementary">
		<!--  侧边栏1-公告 -->
		<?php if (get_option('argon_sidebar_announcement') != '') { ?>
			<div id="leftbar_announcement" class="card bg-white shadow-sm border-0">
				<div class="leftbar-announcement-body">
				    
					<div class="leftbar-announcement-title"><?php _e('公告', 'argon');?></div>
					<div class="leftbar-announcement-content"><?php echo get_option('argon_sidebar_announcement'); ?></div>
				</div>
			</div>
		<?php } ?>
		<!--  侧边栏2-目录 -->
		<div id="leftbar_banner" class="widget widget_search card bg-white shadow-sm border-0">
			<div class="leftbar-banner card-body">
				<span class="leftbar-banner-title text-white"><?php echo get_option('argon_sidebar_banner_title') == '' ? bloginfo('name') : get_option('argon_sidebar_banner_title'); ?></span>

				<?php  
					$sidebar_subtitle = get_option('argon_sidebar_banner_subtitle'); 
					if ($sidebar_subtitle == "--hitokoto--"){
						$sidebar_subtitle = "<span class='hitokoto'></span>";
					}
				?>
				<?php if ($sidebar_subtitle != '') { /*左侧栏子标题/格言(如果选项中开启)*/?>
					<span class="leftbar-banner-subtitle text-white"><?php echo $sidebar_subtitle; ?></span>
				<?php } /*顶栏标题*/?>

			</div>

			<?php
				/*侧栏上部菜单*/
				class leftbarMenuWalker extends Walker_Nav_Menu{
					public function start_lvl( &$output, $depth = 0, $args = array() ) {
						$indent = str_repeat("\t", $depth);
						$output .= "\n$indent<ul class=\"leftbar-menu-item leftbar-menu-subitem shadow-sm\">\n";
					}
					public function end_lvl( &$output, $depth = 0, $args = array() ) {
						$indent = str_repeat("\t", $depth);
						$output .= "\n$indent</ul>\n";
					}
					public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
						$output .= "\n
						<li class='leftbar-menu-item" . ( $args -> walker -> has_children == 1 ? " leftbar-menu-item-haschildren" : "" ) . ( $object -> current == 1 ? " current" : "" ) . "'>
							<a href='" . $object -> url . "'" . ( $args -> walker -> has_children == 1 ? " no-pjax onclick='return false;'" : "" ) . " target='" . $object -> target . "'>". $object -> title . "</a>";
					}
					public function end_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
						//if ($depth == 0){
							$output .= "</li>";
						//}
					}
				}
				echo "<ul id='leftbar_part1_menu' class='leftbar-menu'>";
				if ( has_nav_menu('leftbar_menu') ){
					wp_nav_menu( array(
						'container'  => '',
						'theme_location'  => 'leftbar_menu',
						'items_wrap'  => '%3$s',
						'depth' => 0,
						'walker' => new leftbarMenuWalker()
					) );
				}
				echo "</ul>";
			?>
			<div class="card-body text-center leftbar-search-button">
				<button id="leftbar_search_container" class="btn btn-secondary btn-lg active btn-sm btn-block border-0" role="button">
					<i class="menu-item-icon fa fa-search mr-0"></i> <?php _e('搜索', 'argon');?>
					<input id="leftbar_search_input" type="text" placeholder="<?php _e('搜索什么...', 'argon');?>" class="form-control form-control-alternative" autocomplete="off">
				</button>
			</div>
		</div><br>



		
		<?php
		// 侧边栏3-纪念日或时钟 (leftbar_part1)
		?>
		<div id="time_canlendar" class="widget widget_search card bg-white shadow-sm border-0 p-3 mb-4">
			<?php
		global $todayData,
			$birthday_name_cn, $birthday_name_jp,
			$birthday_quote, $birthday_description,
			$custom_bg_url, $suffix, $tittle;

		if ( $todayData ) : ?>
			<!-- 生日庆祝卡片 -->
			<div class="happy_birthday_card" style="font-weight: bold; color: #ffd700; text-align: left; font-size: 1.1rem; margin-bottom: 8px;">
				<!-- 可选标题占位 -->
				#纪念日
			</div>

			<div class="text-center">
				<!-- 生日人物中文姓名 -->
				<div class="mb-2 happy_birthday_card" style="font-family: 'CooperZhengKai'; font-size:1.45rem;">
				<?php echo esc_html( $tittle); ?>
				</div>

				<!-- 生日人物壁纸 -->
				<?php if ( ! empty( $custom_bg_url ) ) : ?>
				<img src="<?php echo esc_url( $custom_bg_url ); ?>" alt="生日壁纸"
					style="width:100%; max-height:150px; object-fit:cover;
							border-radius:1.8rem; margin-bottom:0.75rem;" />
				<?php endif; ?>

				<!-- 生日人物日语姓名 -->
				<div class="mb-1" style="font-size:0.8rem;">
				（<?php echo esc_html( $birthday_name_jp ); ?>）
				</div>

				<!-- 短分割线 -->
				<div style="width:100px; height:2px; background:#ffd700; margin:0.5rem auto;"></div>

				<!-- 生日名言 -->
				<?php if ( ! empty( $birthday_quote ) ) : ?>
				<blockquote class="mb-2"
							style="font-size:0.92rem;
									font-style:italic;
									border-left:3px solid #8cdcfe;
									padding:10px 15px;
									margin:5px;
									background: rgba(253, 226, 169, 0.08);
									border-radius:0.5rem;">
					<?php echo wp_kses( $birthday_quote, ['br'=>[], 'del'=>[]] ); ?>
				</blockquote>
				<?php endif; ?>

				<!-- 生日描述 -->
				<?php if ( ! empty( $birthday_description ) ) : ?>
				<div style="font-size:0.95rem;">
					<?php echo wp_kses( $birthday_description, ['br'=>[], 'del'=>[]] ); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php else : ?>
		<!-- 普通日子的数字时钟与日期 -->
		<div class="text-center mb-2">
		<div id="sidebar-date" style="font-size:1rem; font-weight:bold;"></div>
		<div id="sidebar-weekday" style="font-size:0.9rem;">(UTC+8)</div>
		<div style="font-size:0.8rem;">「今日无事发生。」</div>
		</div>

			<div class="text-center">
			<div id="sidebar-clock" style="font-size:2rem; font-weight:bold; letter-spacing:1px;"></div>
			<div style="font-size:0.6rem;">(UTC+8)</div>
			<button id="toggle-decimal" class="btn btn-sm btn-light mt-2"
					style="">
				切换百进制时钟
			</button>
			<div id="decimal-warning" style="font-size:0.8rem; margin-top:0.5rem; display:none;">
				在现实生活中，请警惕百进制时间，害人不浅的……
			</div>
			</div>
			<script>
			(function(){
				var weekdays = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
				var useDecimal = false;
				function updateSidebar() {
					var now = new Date();
					// 始终更新日期和星期
					var y = now.getFullYear();
					var m = String(now.getMonth()+1).padStart(2,'0');
					var d = String(now.getDate()).padStart(2,'0');
					document.getElementById('sidebar-date').textContent = y + '年' + m + '月' + d + '日';
					document.getElementById('sidebar-weekday').textContent = weekdays[now.getDay()];
					if (!useDecimal) {
						// 标准时间显示
						var h = String(now.getHours()).padStart(2, '0');
						var min = String(now.getMinutes()).padStart(2, '0');
						var s = String(now.getSeconds()).padStart(2, '0');
						document.getElementById('sidebar-clock').textContent = h + ':' + min + ':' + s;
						document.getElementById('decimal-warning').style.display = 'none';
					} else {
						// 百进制时间
						var secondsSinceMidnight = now.getHours()*3600 + now.getMinutes()*60 + now.getSeconds();
						var decimalTotal = secondsSinceMidnight / 86400 * 100000;
						var dh = Math.floor(decimalTotal / 10000);
						var dm = Math.floor((decimalTotal % 10000) / 100);
						var ds = Math.floor(decimalTotal % 100);
						var h = String(dh);
						var min = String(dm).padStart(2,'0');
						var s_ = String(ds).padStart(2,'0');
						document.getElementById('sidebar-clock').textContent = h + ':' + min + ':' + s_;
						document.getElementById('decimal-warning').style.display = 'block';
					}
				}
				document.getElementById('toggle-decimal').addEventListener('click', function(){
					useDecimal = !useDecimal;
					this.textContent = useDecimal ? '切换标准时钟' : '切换百进制时钟';
					updateSidebar();
				});
				updateSidebar();
				setInterval(updateSidebar, 1000);
			})();
			</script>

		<?php endif; ?>
		</div>


		
        <!--  侧边栏-最近在看（改为读独立表） -->
        <div id="leftbar_part1" class="widget widget_search card bg-white shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="card-title recent-media-header"><a href="https://loneapex.cn/galgame/"><span>最近在看</span><span>></span></a></h5>
                <?php
                global $apex_media_list;
                $recent_media_items = [];
                if (isset($apex_media_list) && $apex_media_list instanceof Apex_Media_List) {
                    // 仅显示 想看 / 在看
                    $recent_media_items = $apex_media_list->media_query([
                        'status' => ['want','watching'],
                        'show_on_home' => true,
                        'order_by' => 'updated_at',
                        'order' => 'DESC',
                        'limit' => 3,
                    ]);
                    if (empty($recent_media_items)) {
                        $recent_media_items = $apex_media_list->media_query([
                            'status' => ['want','watching'],
                            'order_by' => 'updated_at',
                            'order' => 'DESC',
                            'limit' => 3,
                        ]);
                    }
                }

                if (!empty($recent_media_items)) {
                    foreach ($recent_media_items as $item) {
                        $cover = '';
                        $att_id = isset($item['cover_attachment_id']) ? intval($item['cover_attachment_id']) : 0;
                        if ($att_id > 0) {
                            $cover = wp_get_attachment_image_url($att_id, 'full');
                        }
                        if (empty($cover)) {
                            $cover = isset($item['cover_source_url']) ? $item['cover_source_url'] : '';
                        }
                        $score_10 = isset($apex_media_list) ? $apex_media_list->resolve_score_10_from_row($item) : 0;
                        $season = isset($item['season_text']) ? $item['season_text'] : '';
                        $review = isset($item['review']) ? $item['review'] : '';
                        $type_label = ($item['type'] === 'anime') ? '番剧' : 'Galgame';

                        $status_slug = isset($item['status']) ? $item['status'] : '';
                        $status_name = isset($apex_media_list) ? $apex_media_list->get_status_label($status_slug) : '';
                        $status_class = '';
                        if ($status_slug === 'watching') {
                            $status_class = 'status-watching';
                        } elseif ($status_slug === 'want') {
                            $status_class = 'status-want';
                        }
                        ?>
                        <div class="recent-media-card">
                            <h6 class="recent-media-title"><?php echo esc_html($item['title']); ?></h6>
                            <div class="recent-media-content">
                                <div class="recent-media-cover">
                                    <?php if (!empty($cover)) : ?>
                                        <img src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($item['title']); ?>">
                                    <?php else: ?>
                                        <div class="recent-media-score">暂无封面</div>
                                    <?php endif; ?>
                                    <div class="recent-media-score">评分: <?php echo ($score_10 > 0 ? esc_html(number_format($score_10, 1)) : '暂无'); ?></div>
                                </div>
                                <div class="recent-media-info">
                                    <div class="recent-media-meta">
                                        <div class="recent-media-text">
                                            <div class="media-season"><?php echo esc_html($season); ?></div>
                                            <div class="media-type"><?php echo esc_html($type_label); ?></div>
                                        </div>
                                        <span class="media-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_name); ?></span>
                                    </div>
                                    <div class="recent-media-review">
                                        <?php echo empty($review) ? '<div class="no-review">暂无作品评论</div>' : wp_kses_post($review); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="text-center">暂无内容</p>';
                }
                ?>
            </div>
        </div>

		<style>
			.recent-media-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-weight: bold;
				margin-bottom: .5rem;
			}
			.recent-media-header a {
				display: flex;
				justify-content: space-between;
				width: 100%;
				color: inherit;
				text-decoration: none;
			}
			.recent-media-header a:hover {
				text-decoration: none;
			}
			.recent-media-card {
				margin-bottom: 15px;
				padding-bottom: 10px;
				border-bottom: 1px solid #eee;
			}
			html.darkmode .recent-media-card {
				border-bottom-color: #333;
			}
			.recent-media-card:last-child {
				border-bottom: none;
				margin-bottom: 0;
				padding-bottom: 0;
			}
			.recent-media-title {
				font-size: 1rem;
				font-weight: bold;
				margin-bottom: 10px;
				text-align: left;
			}
			.recent-media-content {
				display: flex;
			}
			.recent-media-cover {
				width: 33.33%;
				flex-shrink: 0;
				margin-right: 10px;
			}
			.recent-media-cover img {
				width: 100%;
				height: auto;
				border-radius: 4px;
			}
			.recent-media-score {
				font-size: 0.8rem;
				text-align: center;
				margin-top: 5px;
				font-weight: bold;
			}
			.recent-media-info {
				width: 66.67%;
				display: flex;
				flex-direction: column;
			}
			.recent-media-meta {
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 0.75rem;
				margin-bottom: 5px;
				color: #666;
			}
			.recent-media-text {
				display: flex;
				flex-direction: column;
				gap: 2px;
			}
			html.darkmode .media-season,
			html.darkmode .media-type {
				color: #aaa;
			}
			html.darkmode .recent-media-meta {
				color: #aaa;
			}
			.media-status-badge {
				background-color:rgb(41, 77, 68);
				color: white;
				padding: 2px 6px;
				border-radius: 4px;
			}
			.media-status-badge.status-watching {
				background-color: #2e8b57;
				color: #fff;
			}
			.media-status-badge.status-want {
				background-color: #3b82f6;
				color: #fff;
			}
			.media-status-badge.status-watched {
				background-color: #f59e0b;
				color: #fff;
			}
			.recent-media-review {
				font-size: 0.8rem;
				flex-grow: 1;
				max-height: 80px; /* 限制最大高度 */
				overflow-y: auto;
				padding-right: 5px; /* 留出滚动条空间 */
			}
			.recent-media-review .no-review {
				display: flex;
				align-items: center;
				justify-content: center;
				height: 100%;
				color: #999;
			}
			html.darkmode .recent-media-review .no-review{
				color: #666;
			}
			/* Custom Scrollbar */
			.recent-media-review::-webkit-scrollbar {
				width: 4px;
			}
			.recent-media-review::-webkit-scrollbar-track {
				background: #f1f1f1;
				border-radius: 2px;
			}
			html.darkmode .recent-media-review::-webkit-scrollbar-track {
				background: #444;
			}
			.recent-media-review::-webkit-scrollbar-thumb {
				background: #888;
				border-radius: 2px;
			}
			.recent-media-review::-webkit-scrollbar-thumb:hover {
				background: #555;
			}
		</style>

		<!--  侧边栏5-作者简介 -->
		<div id="leftbar_part2" class="widget widget_search card bg-white shadow-sm border-0">
			<div id="leftbar_part2_inner" class="card-body">
				<?php
					$nowActiveTab = 1;/*默认激活的标签*/
					if (have_catalog()){
						$nowActiveTab = 0;
					}
				?>
				<div class="nav-wrapper" style="padding-top: 5px;<?php if (!have_catalog() && !is_active_sidebar('leftbar-tools')) { echo ' display:none;'; }?>">
	                <ul class="nav nav-pills nav-fill" role="tablist">
						<?php if (have_catalog()) { ?>
							<li class="nav-item sidebar-tab-switcher">
								<a class="<?php if ($nowActiveTab == 0) { echo 'active show'; }?>" id="leftbar_tab_catalog_btn" data-toggle="tab" href="#leftbar_tab_catalog" role="tab" aria-controls="leftbar_tab_catalog" no-pjax><?php _e('文章目录', 'argon');?></a>
							</li>
						<?php } ?>
						<li class="nav-item sidebar-tab-switcher">
							<a class="<?php if ($nowActiveTab == 1) { echo 'active show'; }?>" id="leftbar_tab_overview_btn" data-toggle="tab" href="#leftbar_tab_overview" role="tab" aria-controls="leftbar_tab_overview" no-pjax><?php _e('站点概览', 'argon');?></a>
						</li>
						<?php if ( is_active_sidebar( 'leftbar-tools' ) ){?>
							<li class="nav-item sidebar-tab-switcher">
								<a class="<?php if ($nowActiveTab == 2) { echo 'active show'; }?>" id="leftbar_tab_tools_btn" data-toggle="tab" href="#leftbar_tab_tools" role="tab" aria-controls="leftbar_tab_tools" no-pjax><?php _e('功能', 'argon');?></a>
							</li>
						<?php }?>
	                </ul>
				</div>
				<div>
					<div class="tab-content" style="padding: 10px 10px 0 10px;">
						<?php if (have_catalog()) { ?>
							<div class="tab-pane fade<?php if ($nowActiveTab == 0) { echo ' active show'; }?>" id="leftbar_tab_catalog" role="tabpanel" aria-labelledby="leftbar_tab_catalog_btn">
								<div id="leftbar_catalog"></div>
								<script type="text/javascript">
									$(function () {
										$(document).headIndex({
											articleWrapSelector: '#post_content',
											indexBoxSelector: '#leftbar_catalog',
											subItemBoxClass: "index-subItem-box",
											itemClass: "index-item",
											linkClass: "index-link",
											offset: 80,
										});
									})
								</script>
								<?php if (get_option('argon_show_headindex_number') == 'true') {?>
									<style>
										#leftbar_catalog ul {
											counter-reset: blog_catalog_number;
										}
										#leftbar_catalog li.index-item > a:before {
											content: counters(blog_catalog_number, '.') " ";
											counter-increment: blog_catalog_number;
										}
									</style>
								<?php }?>
							</div>
						<?php } ?>
						<div class="tab-pane fade text-center<?php if ($nowActiveTab == 1) { echo ' active show'; }?>" id="leftbar_tab_overview" role="tabpanel" aria-labelledby="leftbar_tab_overview_btn">
							<div id="leftbar_overview_author_image" style="background-image: url(<?php echo get_option('argon_sidebar_auther_image') == '' ? 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj48cmVjdCBmaWxsPSIjNUU3MkU0MjIiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIi8+PGc+PGcgb3BhY2l0eT0iMC4zIj48cGF0aCBmaWxsPSIjNUU3MkU0IiBkPSJNNzQuMzksMzIuODZjLTAuOTgtMS43LTMuMzktMy4wOS01LjM1LTMuMDlINDUuNjJjLTEuOTYsMC00LjM3LDEuMzktNS4zNSwzLjA5TDI4LjU3LDUzLjE1Yy0wLjk4LDEuNy0wLjk4LDQuNDgsMCw2LjE3bDExLjcxLDIwLjI5YzAuOTgsMS43LDMuMzksMy4wOSw1LjM1LDMuMDloMjMuNDNjMS45NiwwLDQuMzctMS4zOSw1LjM1LTMuMDlMODYuMSw1OS4zMmMwLjk4LTEuNywwLjk4LTQuNDgsMC02LjE3TDc0LjM5LDMyLjg2eiIvPjwvZz48ZyBvcGFjaXR5PSIwLjgiPjxwYXRoIGZpbGw9IiM1RTcyRTQiIGQ9Ik02Mi4wNCwyMC4zOWMtMC45OC0xLjctMy4zOS0zLjA5LTUuMzUtMy4wOUgzMS43M2MtMS45NiwwLTQuMzcsMS4zOS01LjM1LDMuMDlMMTMuOSw0Mi4wMWMtMC45OCwxLjctMC45OCw0LjQ4LDAsNi4xN2wxMi40OSwyMS42MmMwLjk4LDEuNywzLjM5LDMuMDksNS4zNSwzLjA5aDI0Ljk3YzEuOTYsMCw0LjM3LTEuMzksNS4zNS0zLjA5bDEyLjQ5LTIxLjYyYzAuOTgtMS43LDAuOTgtNC40OCwwLTYuMTdMNjIuMDQsMjAuMzl6Ii8+PC9nPjwvZz48L3N2Zz4=' : get_option('argon_sidebar_auther_image'); ?>)" class="rounded-circle shadow-sm" alt="avatar"></div>
							<h6 id="leftbar_overview_author_name"><?php echo get_option('argon_sidebar_auther_name') == '' ? bloginfo('name') : get_option('argon_sidebar_auther_name'); ?></h6>
							<?php $author_desctiption = get_option('argon_sidebar_author_description'); if (!empty($author_desctiption)) {echo '<h6 id="leftbar_overview_author_description">'. $author_desctiption .'</h6>';}?>
							<nav class="site-state">
								<div class="site-state-item site-state-posts">
									<a <?php $archives_page_url = get_option('argon_archives_timeline_url'); echo (empty($archives_page_url) ? ' style="cursor: default;"' : 'href="' . $archives_page_url . '"');?>>
										<span class="site-state-item-count"><?php echo wp_count_posts() -> publish; ?></span>
										<span class="site-state-item-name"><?php _e('文章', 'argon');?></span>
									</a>
								</div>
								<div class="site-state-item site-state-categories">
									<a data-toggle="modal" data-target="#blog_categories">
										<span class="site-state-item-count"><?php echo wp_count_terms('category'); ?></span>
										<span class="site-state-item-name"><?php _e('分类', 'argon');?></span>
									</a>
								</div>      
								<div class="site-state-item site-state-tags">
									<a data-toggle="modal" data-target="#blog_tags">
										<span class="site-state-item-count"><?php echo wp_count_terms('post_tag'); ?></span>
										<span class="site-state-item-name"><?php _e('标签', 'argon');?></span>
									</a>
								</div>
							</nav>
							<?php
								/*侧栏作者链接*/
								class leftbarAuthorLinksWalker extends Walker_Nav_Menu{
									public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
										if ($depth == 0){
											$output .= "\n
											<div class='site-author-links-item'>
												<a href='" . $object -> url . "' rel='noopener' target='_blank'>". $object -> title . "</a>";
										}
									}
									public function end_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
										if ($depth == 0){
											$output .= "</div>";
										}
									}
								}

								if ( has_nav_menu('leftbar_author_links') ){
									echo "<div class='site-author-links'>";
									wp_nav_menu( array(
										'container'  => '',
										'theme_location'  => 'leftbar_author_links',
										'items_wrap'  => '%3$s',
										'depth' => 0,
										'walker' => new leftbarAuthorLinksWalker()
									) );
									echo "</div>";
								}
							?>
							<?php
								/*侧栏友情链接*/
								class leftbarFriendLinksWalker extends Walker_Nav_Menu{
									public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
										if ($depth == 0){
											$output .= "\n
											<li class='site-friend-links-item'>
												<a href='" . $object -> url . "' rel='noopener' target='_blank'>". $object -> title . "</a>";
										}
									}
									public function end_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
										if ($depth == 0){
											$output .= "</li>";
										}
									}
								}

								if ( has_nav_menu('leftbar_friend_links') ){
									echo "<div class='site-friend-links'>
											<div class='site-friend-links-title'><i class='fa fa-fw fa-link'></i> Links</div>
											<ul class='site-friend-links-ul'>";
									wp_nav_menu( array(
										'container'  => '',
									    'theme_location'  => 'leftbar_friend_links',
										'items_wrap'  => '%3$s',
									    'depth' => 0,
										'walker' => new leftbarFriendLinksWalker()
									) );
									echo "</ul></div>";
								}else{
									echo "<div style='height: 20px;'></div>";
								}
							?>
							<?php if ( is_active_sidebar( 'leftbar-siteinfo-extra-tools' ) ){?>
								<div id="leftbar_siteinfo_extra_tools">
									<?php dynamic_sidebar( 'leftbar-siteinfo-extra-tools' ); ?>
								</div>
							<?php }?>
						</div>
						<?php if ( is_active_sidebar( 'leftbar-tools' ) ){?>
							<div class="tab-pane fade<?php if ($nowActiveTab == 2) { echo ' active show'; }?>" id="leftbar_tab_tools" role="tabpanel" aria-labelledby="leftbar_tab_tools_btn">
								<?php dynamic_sidebar( 'leftbar-tools' ); ?>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
		</div>

		
		
		
</aside>
<div class="modal fade" id="blog_categories" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?php _e('分类', 'argon');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php
					$categories = get_categories(array(
						'child_of' => 0,
						'orderby' => 'name',
						'order' => 'ASC',
						'hide_empty' => 0,
						'hierarchical' => 0,
						'taxonomy' => 'category',
						'pad_counts' => false
					));
					foreach($categories as $category) {
						echo "<a href=" . get_category_link( $category -> term_id ) . " class='badge badge-secondary tag'>" . $category->name . " <span class='tag-num'>" . $category -> count . "</span></a>";
					}
				?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="blog_tags" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?php _e('标签', 'argon');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php
					$categories = get_categories(array(
						'child_of' => 0,
						'orderby' => 'name',
						'order' => 'ASC',
						'hide_empty' => 0,
						'hierarchical' => 0,
						'taxonomy' => 'post_tag',
						'pad_counts' => false
					));
					foreach($categories as $category) {
						echo "<a href=" . get_category_link( $category -> term_id ) . " class='badge badge-secondary tag'>" . $category->name . " <span class='tag-num'>" . $category -> count . "</span></a>";
					}
				?>
			</div>
		</div>
	</div>
</div>
<?php
	if (get_option('argon_page_layout') == 'triple'){
		echo '<aside id="rightbar" class="rightbar widget-area" role="complementary">';
		dynamic_sidebar( 'rightbar-tools' );
		echo '</aside>';
	}
?>
