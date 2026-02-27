<?php
			$trim_words_count = get_option('argon_trim_words_count', 175);
		?>
		<?php if ($trim_words_count > 0){ ?>
			<div class="post-content">
				<?php
				
				// argon自带摘要逻辑
				// 全显示，手动摘要 > 正文截断 > 密码保护提示 > 空内容提示
					if (get_option("argon_hide_shortcode_in_preview") == 'true'){
						$preview = wp_trim_words(do_shortcode(get_the_content('...')), $trim_words_count);
					}else{
						$preview = wp_trim_words(get_the_content('...'), $trim_words_count);
					}
					if (post_password_required()){
						$preview = __("这篇文章受密码保护，输入密码才能阅读", 'argon');
					}
					if ($preview == ""){
						$preview = __("这篇文章没有摘要", 'argon');
					}
					if ($post -> post_excerpt){
						$preview = $post -> post_excerpt;
					}
					echo $preview;
				?>

			</div>
		<?php
			}
		?>