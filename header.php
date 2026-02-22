<!-- 飘飘乎如遗世独立，羽化而登仙。
     寄蜉蝣于天地，
     渺沧海之一粟。
     哀吾生之须臾，
     羡长江之无穷。
             ——2024.10.10-->

<!-- 祝愿网站一直运行顺利，服务器永不宕机，数据库永远长存，代码永不报错，后端不出bug，前端没问题，oss别炸，ssl自动续签别断，cdn别炸，流量没人盗刷。长长久久，平平安安。
     ——建站三周年寄语-->
<!DOCTYPE html>
<?php
	
	$htmlclasses = "";
	if (get_option('argon_page_layout') == "single"){
		$htmlclasses .= "single-column ";
	}
	if (get_option('argon_page_layout') == "triple"){
		$htmlclasses .= "triple-column ";
	}
	if (get_option('argon_page_layout') == "double-reverse"){
		$htmlclasses .= "double-column-reverse ";
	}
	if (get_option('argon_enable_immersion_color') == "true"){
		$htmlclasses .= "immersion-color ";
	}
	if (get_option('argon_enable_amoled_dark') == "true"){
		$htmlclasses .= "amoled-dark ";
	}
	if (get_option('argon_card_shadow') == 'big'){
		$htmlclasses .= 'use-big-shadow ';
	}
	if (get_option('argon_font') == 'serif'){
		$htmlclasses .= 'use-serif ';
	}
	if (get_option('argon_disable_codeblock_style') == 'true'){
		$htmlclasses .= 'disable-codeblock-style ';
	}
	if (get_option('argon_enable_headroom') == 'absolute'){
		$htmlclasses .= 'navbar-absolute ';
	}
	$banner_size = get_option('argon_banner_size', 'full');
	if ($banner_size != 'full'){
		if ($banner_size == 'mini'){
			$htmlclasses .= 'banner-mini ';
		}else if ($banner_size == 'hide'){
			$htmlclasses .= 'no-banner ';
		}else if ($banner_size == 'fullscreen'){
			$htmlclasses .= 'banner-as-cover ';
		}
	}
	if (get_option('argon_toolbar_blur', 'false') == 'true'){
		$htmlclasses .= 'toolbar-blur ';
	}
	$htmlclasses .= get_option('argon_article_header_style', 'article-header-style-default') . ' ';
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') === false){
		$htmlclasses .= ' using-safari';
	}
?>




<html <?php language_attributes(); ?> class="no-js <?php echo $htmlclasses;?>">
<?php
	$themecolor = get_option("argon_theme_color", "#5e72e4");
	$themecolor_origin = $themecolor;
	if (isset($_COOKIE["argon_custom_theme_color"])){
		if (checkHEX($_COOKIE["argon_custom_theme_color"]) && get_option('argon_show_customize_theme_color_picker') != 'false'){
			$themecolor = $_COOKIE["argon_custom_theme_color"];
		}
	}
	if (hex2gray($themecolor) < 50){
		echo '<script>document.getElementsByTagName("html")[0].classList.add("themecolor-toodark");</script>';
	}
?>
<?php
	$cardradius = get_option('argon_card_radius');
	if ($cardradius == ""){
		$cardradius = "4";
	}
	$cardradius_origin = $cardradius;
	if (isset($_COOKIE["argon_card_radius"]) && $_COOKIE["argon_card_radius"] != ""){
		$cardradius = $_COOKIE["argon_card_radius"];
	}
?>
<head>
    <!--umami用户跟踪-->
    <script defer src="https://umami.loneapex.cn/script.js" data-website-id="82373bbe-f072-4a81-a32e-81d0687b2ebe"></script>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php if (get_option('argon_enable_mobile_scale') != 'true'){ ?>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<?php }else{ ?>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
	<?php } ?>
	<meta property="og:site_name" content="<?php echo get_bloginfo('name');?>">
	<meta property="og:title" content="<?php echo wp_get_document_title();?>">
	<meta property="og:type" content="article">
	<meta property="og:url" content="<?php echo home_url(add_query_arg(array(),$wp->request));?>">
	<?php
		$seo_description = get_seo_description();
		if ($seo_description != ''){ ?>
			<meta name="description" content="<?php echo $seo_description?>">
			<meta property="og:description" content="<?php echo $seo_description?>">
	<?php } ?>

	<?php
		$seo_keywords = get_seo_keywords();
		if ($seo_keywords != ''){ ?>
			<meta name="keywords" content="<?php echo get_seo_keywords();?>">
	<?php } ?>


	<!--<meta property="og:image" content="http://loneapex.cn/wp-content/uploads/2024/02/cropped-ico.gif" /> -->
    <?php
		if (is_single() || is_page()){
			$og_image = get_og_image();
			if ($og_image != ''){ ?>
				<meta property="og:image" content="<?php echo $og_image?>" />
	<?php 	}
		} ?>

	
	<meta name="theme-color" content="<?php echo $themecolor; ?>">
	<meta name="theme-color-rgb" content="<?php echo hex2str($themecolor); ?>">
	<meta name="theme-color-origin" content="<?php echo $themecolor_origin; ?>">
	<meta name="argon-enable-custom-theme-color" content="<?php echo (get_option('argon_show_customize_theme_color_picker') != 'false' ? 'true' : 'false'); ?>">


	<meta name="theme-card-radius" content="<?php echo $cardradius; ?>">
	<meta name="theme-card-radius-origin" content="<?php echo $cardradius_origin; ?>">

	<meta name="theme-version" content="<?php echo $GLOBALS['theme_version']; ?>">
    
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">
	<?php endif; ?>
	<?php
		wp_enqueue_style("argon_css_merged", $GLOBALS['assets_path'] . "/assets/argon_css_merged.css", null, $GLOBALS['theme_version']);
		wp_enqueue_style("style", $GLOBALS['assets_path'] . "/style.css", null, $GLOBALS['theme_version']);
		if (get_option('argon_disable_googlefont') != 'true') {wp_enqueue_style("googlefont", "//fonts.proxy.ustclug.org/css?family=Open+Sans:300,400,600,700|Noto+Serif+SC:300,600&display=swap");}
		wp_enqueue_script("argon_js_merged", $GLOBALS['assets_path'] . "/assets/argon_js_merged.js", null, $GLOBALS['theme_version']);
		wp_enqueue_script("argonjs", $GLOBALS['assets_path'] . "/assets/js/argon.min.js", null, $GLOBALS['theme_version']);
	?>
	<?php wp_head(); ?>
	<?php $GLOBALS['wp_path'] = get_option('argon_wp_path') == '' ? '/' : get_option('argon_wp_path'); ?>
	
	

	<link rel="stylesheet" href="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<script>
		document.documentElement.classList.remove("no-js");
		var argonConfig = {
			wp_path: "<?php echo $GLOBALS['wp_path']; ?>",
			language: "<?php echo argon_get_locate(); ?>",
			dateFormat: "<?php echo get_option('argon_dateformat', 'YMD'); ?>",
			<?php if (get_option('argon_enable_zoomify') == 'true'){ ?>
				zoomify: {
					duration: <?php echo get_option('argon_zoomify_duration', 200); ?>,
					easing: "<?php echo get_option('argon_zoomify_easing', 'cubic-bezier(0.4,0,0,1)'); ?>",
					scale: <?php echo get_option('argon_zoomify_scale', 0.9); ?>
				},
			<?php } else { ?>
				zoomify: false,
			<?php } ?>
			pangu: "<?php echo get_option('argon_enable_pangu', 'false'); ?>",
			<?php if (get_option('argon_enable_lazyload') != 'false'){ ?>
				lazyload: {
					threshold: <?php echo get_option('argon_lazyload_threshold', 800); ?>,
					effect: "<?php echo get_option('argon_lazyload_effect', 'fadeIn'); ?>"
				},
			<?php } else { ?>
				lazyload: false,
			<?php } ?>
			fold_long_comments: <?php echo get_option('argon_fold_long_comments', 'false'); ?>,
			fold_long_shuoshuo: <?php echo get_option('argon_fold_long_shuoshuo', 'false'); ?>,
			disable_pjax: <?php echo get_option('argon_pjax_disabled', 'false'); ?>,
			pjax_animation_durtion: <?php echo (get_option("argon_disable_pjax_animation") == 'true' ? '0' : '600'); ?>,
			headroom: "<?php echo get_option('argon_enable_headroom', 'false'); ?>",
			waterflow_columns: "<?php echo get_option('argon_article_list_waterflow', '1'); ?>",
			code_highlight: {
				enable: <?php echo get_option('argon_enable_code_highlight', 'false'); ?>,
				hide_linenumber: <?php echo get_option('argon_code_highlight_hide_linenumber', 'false'); ?>,
				transparent_linenumber: <?php echo get_option('argon_code_highlight_transparent_linenumber', 'false'); ?>,
				break_line: <?php echo get_option('argon_code_highlight_break_line', 'false'); ?>
			}
		}
	</script>
	<script>
		var darkmodeAutoSwitch = "<?php echo (get_option("argon_darkmode_autoswitch") == '' ? 'false' : get_option("argon_darkmode_autoswitch"));?>";
		function setDarkmode(enable){
			if (enable == true){
				$("html").addClass("darkmode");
			}else{
				$("html").removeClass("darkmode");
			}
			$(window).trigger("scroll");
		}
		function toggleDarkmode(){
			if ($("html").hasClass("darkmode")){
				setDarkmode(false);
				sessionStorage.setItem("Argon_Enable_Dark_Mode", "false");
			}else{
				setDarkmode(true);
				sessionStorage.setItem("Argon_Enable_Dark_Mode", "true");
			}
		}
		if (sessionStorage.getItem("Argon_Enable_Dark_Mode") == "true"){
			setDarkmode(true);
		}
		function toggleDarkmodeByPrefersColorScheme(media){
			if (sessionStorage.getItem('Argon_Enable_Dark_Mode') == "false" || sessionStorage.getItem('Argon_Enable_Dark_Mode') == "true"){
				return;
			}
			if (media.matches){
				setDarkmode(true);
			}else{
				setDarkmode(false);
			}
		}
		function toggleDarkmodeByTime(){
			if (sessionStorage.getItem('Argon_Enable_Dark_Mode') == "false" || sessionStorage.getItem('Argon_Enable_Dark_Mode') == "true"){
				return;
			}
			let hour = new Date().getHours();
			if (<?php echo apply_filters("argon_darkmode_time_check", "hour < 7 || hour >= 22")?>){
				setDarkmode(true);
			}else{
				setDarkmode(false);
			}
		}
		if (darkmodeAutoSwitch == 'system'){
			var darkmodeMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
			darkmodeMediaQuery.addListener(toggleDarkmodeByPrefersColorScheme);
			toggleDarkmodeByPrefersColorScheme(darkmodeMediaQuery);
		}
		if (darkmodeAutoSwitch == 'time'){
			toggleDarkmodeByTime();
		}
		if (darkmodeAutoSwitch == 'alwayson'){
			setDarkmode(true);
		}

		function toggleAmoledDarkMode(){
			$("html").toggleClass("amoled-dark");
			if ($("html").hasClass("amoled-dark")){
				localStorage.setItem("Argon_Enable_Amoled_Dark_Mode", "true");
			}else{
				localStorage.setItem("Argon_Enable_Amoled_Dark_Mode", "false");
			}
		}
		if (localStorage.getItem("Argon_Enable_Amoled_Dark_Mode") == "true"){
			$("html").addClass("amoled-dark");
		}else if (localStorage.getItem("Argon_Enable_Amoled_Dark_Mode") == "false"){
			$("html").removeClass("amoled-dark");
		}
	</script>
	<script>
		if (navigator.userAgent.indexOf("Safari") !== -1 && navigator.userAgent.indexOf("Chrome") === -1){
			$("html").addClass("using-safari");
		}
	</script>

	<?php if (get_option('argon_enable_smoothscroll_type') == '2') { /*平滑滚动*/?>
		<script src="<?php echo $GLOBALS['assets_path']; ?>/assets/vendor/smoothscroll/smoothscroll2.js"></script>
	<?php }else if (get_option('argon_enable_smoothscroll_type') == '3'){?>
		<script src="<?php echo $GLOBALS['assets_path']; ?>/assets/vendor/smoothscroll/smoothscroll3.min.js"></script>
	<?php }else if (get_option('argon_enable_smoothscroll_type') == '1_pulse'){?>
		<script src="<?php echo $GLOBALS['assets_path']; ?>/assets/vendor/smoothscroll/smoothscroll1_pulse.js"></script>
	<?php }else if (get_option('argon_enable_smoothscroll_type') != 'disabled'){?>
		<script src="<?php echo $GLOBALS['assets_path']; ?>/assets/vendor/smoothscroll/smoothscroll1.js"></script>
	<?php }?>
</head>

<?php echo get_option('argon_custom_html_head'); ?>

<style id="themecolor_css">
	<?php
		$themecolor_rgbstr = hex2str($themecolor);
		$RGB = hexstr2rgb($themecolor);
		$HSL = rgb2hsl($RGB['R'], $RGB['G'], $RGB['B']);
	?>
	:root{
		--themecolor: <?php echo $themecolor; ?>;
		--themecolor-R: <?php echo $RGB['R']; ?>;
		--themecolor-G: <?php echo $RGB['G']; ?>;
		--themecolor-B: <?php echo $RGB['B']; ?>;
		--themecolor-H: <?php echo $HSL['H']; ?>;
		--themecolor-S: <?php echo $HSL['S']; ?>;
		--themecolor-L: <?php echo $HSL['L']; ?>;
	}
</style>
<style id="theme_cardradius_css">
	:root{
		--card-radius: <?php echo $cardradius; ?>px;
	}
</style>

<body <?php body_class(); ?>>
<div id="birthday-overlay"></div>
<?php /*wp_body_open();*/ ?>


<style>
	.timeline-back-to-top { display: none !important; visibility: hidden !important; pointer-events: none !important; }
</style>


<div id="toolbar">
	<header class="header-global">
		<nav id="navbar-main" class="navbar navbar-main navbar-expand-lg navbar-transparent navbar-light bg-primary headroom--not-bottom headroom--not-top headroom--pinned">
			<div class="container">
				<button id="open_sidebar" class="navbar-toggler" type="button" aria-expanded="false" aria-label="Toggle sidebar">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="navbar-brand mr-0">
					<?php if (get_option('argon_toolbar_icon') != '') { /*顶栏ICON(如果选项中开启)*/?>
						<a class="navbar-brand navbar-icon mr-lg-5" href="<?php echo get_option('argon_toolbar_icon_link'); ?>">
							<img src="<?php echo get_option('argon_toolbar_icon'); ?>">
						</a>
					<?php }?>
					<?php
						//顶栏标题
						$toolbar_title = get_option('argon_toolbar_title') == '' ? get_bloginfo('name') : get_option('argon_toolbar_title');
						if ($toolbar_title == '--hidden--'){
							$toolbar_title = '';
						}
					?>
					<a class="navbar-brand navbar-title" href="<?php bloginfo('url'); ?>"><?php echo $toolbar_title;?></a>
				</div>
				<div class="navbar-collapse collapse" id="navbar_global">
					<div class="navbar-collapse-header">
						<div class="row" style="display: none;">
							<div class="col-6 collapse-brand"></div>
							<div class="col-6 collapse-close">
								<button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbar_global" aria-controls="navbar_global" aria-expanded="false" aria-label="Toggle navigation">
									<span></span>
									<span></span>
								</button>
							</div>
						</div>
						<div class="input-group input-group-alternative">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fa fa-search"></i></span>
							</div>
							<input id="navbar_search_input_mobile" class="form-control" placeholder="搜索什么..." type="text" autocomplete="off">
						</div>
					</div>
					<?php
						/*顶栏菜单*/
						class toolbarMenuWalker extends Walker_Nav_Menu{
							public function start_lvl( &$output, $depth = 0, $args = array() ) {
								$indent = str_repeat("\t", $depth);
								$output .= "\n$indent<div class=\"dropdown-menu\">\n";
							}
							public function end_lvl( &$output, $depth = 0, $args = array() ) {
								$indent = str_repeat("\t", $depth);
								$output .= "\n$indent</div>\n";
							}
							public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
								if ($depth == 0){
									if ($args -> walker -> has_children == 1){
										$output .= "\n
										<li class='nav-item dropdown'>
											<a href='" . $object -> url . "' class='nav-link' data-toggle='dropdown' no-pjax onclick='return false;' title='" . $object -> description . "'>
										  		<i class='ni ni-book-bookmark d-lg-none'></i>
												<span class='nav-link-inner--text'>" . $object -> title . "</span>
										  </a>";
									}else{
										$output .= "\n
										<li class='nav-item'>
											<a href='" . $object -> url . "' class='nav-link' target='" . $object -> target . "' title='" . $object -> description . "'>
										  		<i class='ni ni-book-bookmark d-lg-none'></i>
												<span class='nav-link-inner--text'>" . $object -> title . "</span>
										  </a>";
									}
								}else if ($depth == 1){
									$output .= "<a href='" . $object -> url . "' class='dropdown-item' target='" . $object -> target . "' title='" . $object -> description . "'>" . $object -> title . "</a>";
								}
							}
							public function end_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
								if ($depth == 0){
									$output .= "\n</li>";
								}
							}
						}
						if ( has_nav_menu('toolbar_menu') ){
							echo "<ul class='navbar-nav navbar-nav-hover align-items-lg-center'>";
							wp_nav_menu( array(
								'container'  => '',
								'theme_location'  => 'toolbar_menu',
								'items_wrap'  => '%3$s',
								'depth' => 0,
								'walker' => new toolbarMenuWalker()
							) );
							echo "</ul>";
						}
					?>
					<ul class="navbar-nav align-items-lg-center ml-lg-auto">
						<li id="navbar_search_container" class="nav-item" data-toggle="modal">
							<div id="navbar_search_input_container">
								<div class="input-group input-group-alternative">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="fa fa-search"></i></span>
									</div>
									<input id="navbar_search_input" class="form-control" placeholder="<?php _e('搜索什么...', 'argon');?>" type="text" autocomplete="off">
								</div>
							</div>
						</li>
					</ul>
				</div>
				<div id="navbar_menu_mask" data-toggle="collapse" data-target="#navbar_global"></div>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar_global" aria-controls="navbar_global" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon navbar-toggler-searcg-icon"></span>
				</button>
			</div>
		</nav>
	</header>
</div>
<div class="modal fade" id="argon_search_modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?php _e('搜索', 'argon');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php get_search_form(); ?>
			</div>
		</div>
	</div>
</div>
<!--Banner-->
<link href="https://loneapex.cn/extra-js/myface.css" rel="stylesheet">
<section id="banner" class="banner section section-lg section-shaped">
	<div class="shape <?php echo get_option('argon_banner_background_hide_shapes') == 'true' ? '' : 'shape-style-1' ?> <?php echo get_option('argon_banner_background_color_type') == '' ? 'shape-primary' : get_option('argon_banner_background_color_type'); ?>">
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
	</div>


    <!-- 标题与副标题 -->
    <!-- {character_collections}标题与副标题-生日适配 -->
	<div id="a_banner_container" class="banner-container container text-center">
		<?php 
		global $custom_bg_url, $custom_bg_dark_url, $banner_subtitle;
		// 获取主题原本的默认标题
		$default_title = get_option('argon_banner_title') === '' 
			? get_bloginfo('name') 
			: get_option('argon_banner_title');

		$cyberpunk_title_en = get_option('argon_banner_title_en');
		$cyberpunk_title_en = $cyberpunk_title_en === '' ? 'WELCOME TO MY WORLD' : $cyberpunk_title_en;
		$cyberpunk_subtitle_en = get_option('argon_banner_subtitle_en');
		$cyberpunk_subtitle_en = $cyberpunk_subtitle_en === '' ? 'ENTER THE CYBER REALM' : $cyberpunk_subtitle_en;
		$enable_cyberpunk_effect = get_option('argon_enable_cyberpunk_banner_effect') === 'true';

		// 是否启用打字机效果（赛博朋克动效开启时强制关闭）
		$enable_typing = $enable_cyberpunk_effect ? false : ( get_option('argon_enable_banner_title_typing_effect') === 'true' );

		if ( $enable_cyberpunk_effect ) : ?>
			<div class="banner-title text-white cyberpunk-translate">
				<span data-text="<?php echo esc_attr( $default_title ); ?>" data-en="<?php echo esc_attr( $cyberpunk_title_en ); ?>" class="banner-title-inner">
					<?php echo esc_html( $default_title ); ?>
				</span>
				<?php if ( $banner_subtitle ) : ?>
					<span data-text="<?php echo esc_attr( $banner_subtitle ); ?>" data-en="<?php echo esc_attr( $cyberpunk_subtitle_en ); ?>" class="banner-subtitle d-block">
						<?php echo esc_html( $banner_subtitle ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php elseif ( ! $enable_typing ) : ?>
			<div class="banner-title text-white">
				<span class="banner-title-inner">
					<?php echo esc_html( $default_title ); ?>
				</span>
				<?php if ( $banner_subtitle ) : ?>
					<span class="banner-subtitle d-block">
						<?php echo esc_html( $banner_subtitle ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="banner-title text-white" data-interval="<?php echo intval( get_option('argon_banner_typing_effect_interval', 100) ); ?>">
				<span data-text="<?php echo esc_attr( $default_title ); ?>" class="banner-title-inner">&nbsp;</span>
				<?php if ( $banner_subtitle ) : ?>
					<span data-text="<?php echo esc_attr( $banner_subtitle ); ?>" class="banner-subtitle d-block">&nbsp;</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>


	</div>
	<?php if ( $enable_cyberpunk_effect ) : ?>
		<style id="cyberpunk-banner-style">
			#banner .cyberpunk-translate .banner-title-inner,
			#banner .cyberpunk-translate .banner-subtitle {
				visibility: hidden;
				text-shadow: 0 0 1em rgba(70, 142, 210, 0.55), 0 0 0.7em rgba(76, 163, 197, 0.52), 0 0 0.5em rgba(76, 179, 197, 0.35), 0 0 0.2em rgba(76, 195, 197, 0.29);
			}
			#banner .cyberpunk-translate .char {
				display: inline-block;
				text-shadow: 0 0 1em rgba(70, 142, 210, 0.55), 0 0 0.7em rgba(76, 163, 197, 0.52), 0 0 0.5em rgba(76, 179, 197, 0.35), 0 0 0.2em rgba(76, 195, 197, 0.29);
			}
		</style>
		<script>
		document.addEventListener("DOMContentLoaded", function () {
		// 下面一句是等全页加载完才出现效果
		// window.addEventListener("load", function () {
			var container = document.querySelector("#a_banner_container .banner-title.cyberpunk-translate");
			if (!container) return;
			var fallbackTitle = "<?php echo esc_js( $cyberpunk_title_en ); ?>";
			var fallbackSubtitle = "<?php echo esc_js( $cyberpunk_subtitle_en ); ?>";
			var targets = container.querySelectorAll(".banner-title-inner, .banner-subtitle");
			targets.forEach(function(el) {
				var zh = (el.dataset.text || el.textContent || "").trim();
				if (!zh) {
					el.style.visibility = "visible";
					return;
				}
				var defaultEn = el.classList.contains("banner-title-inner") ? fallbackTitle : fallbackSubtitle;
				var en = (el.dataset.en && el.dataset.en.trim()) ? el.dataset.en : defaultEn;
				el.innerHTML = "";
				var charSpans = [];
				// 只生成英文长度
				for (var i = 0; i < en.length; i++) {
					var span = document.createElement("span");
					span.className = "char";
					span.textContent = en[i] === " " ? "\u00A0" : en[i];
					el.appendChild(span);
					charSpans.push(span);
				}
				el.style.visibility = "visible";
				setTimeout(function() {
					var index = 0;
					var interval = setInterval(function() {
						if (index < zh.length) {
							// 中文有字符 → 覆盖或新增
							if (charSpans[index]) {
								charSpans[index].textContent =
									zh[index] === " " ? "\u00A0" : zh[index];
							} else {
								var span = document.createElement("span");
								span.className = "char";
								span.textContent =
									zh[index] === " " ? "\u00A0" : zh[index];
								el.appendChild(span);
								charSpans.push(span);
							}
						} else if (index < charSpans.length) {
							// 中文比英文短 → 删除多余英文
							charSpans[index].remove();
						}
						index++;
						if (index >= Math.max(en.length, zh.length)) {
							clearInterval(interval);
							// 最终收敛为纯文本（结构干净）
							el.innerHTML = "";
							el.textContent = zh;
						}
					}, 80);
				}, 900);
			});

		});
		</script>
	<?php endif; ?>
	<?php if (get_option('argon_banner_background_url') != '') { ?>
		<style>
			section.banner{
				background-image: url(<?php echo get_banner_background_url(); ?>) !important;
			}
		</style>
	<?php } ?>
	<?php if ($banner_size == 'fullscreen') { ?>
		<div class="cover-scroll-down">
			<i class="fa fa-angle-down" aria-hidden="true"></i>
		</div>
	<?php } ?>
</section>

<!--
功能：最大模糊强度/查找关键词：背景模糊滚动模糊动态模糊

作用：决定背景模糊到最深时的程度。
如何修改：在 header.php 文件中，找到 CSS 部分的 :root { --bg-blur: 10px; } 这一行，直接修改 10px 这个值即可，例如改成 8px 或 15px。
快速查找：在文件中搜索 bg-blur-style 或 --bg-blur。
模糊过渡范围

作用：控制从开始模糊到达到最大模糊所需的滚动距离。
当前逻辑：目前设定为滚动距离等于一个 Banner（头部横幅）的高度。
关键语句：在 JavaScript 部分（<script id="bg-blur-script"> 内），找到这一行： const progress = bannerHeight > 0 ? Math.max(0, Math.min(1, scrollY / bannerHeight)) : 0;
说明：这行代码就是核心，它通过计算“当前滚动距离 scrollY”占“Banner 高度 bannerHeight”的比例来得出模糊进度。如果你想让模糊过程更快（比如滚动半个 Banner 高度就完成），可以改成 scrollY / (bannerHeight / 2)。
模糊过渡的缓动效果

作用：让模糊过渡感觉更自然，而不是线性变化。
关键语句：紧接着上一条语句，你会找到： const easedProgress = progress * (2 - progress);
说明：这是一个“ease-out”（先快后慢）的缓动函数。你可以替换成其他函数来改变过渡的“节奏感”，比如 progress * progress 会变成“ease-in”（先慢后快）。
-->
<!-- {character_collections}2 -->
<?php
global $custom_bg_url, $custom_bg_dark_url, $banner_title, $banner_subtitle, $bg_overlay_alpha;
if ( $custom_bg_url !== '' ) : ?>
    <style>
        <?php if ( get_option('argon_page_background_banner_style','false') == 'transparent' ) : ?>
        #banner, #banner .shape {
            background: transparent !important;
        }
        <?php endif; ?>

        .background-layer {
            position: fixed;
            top: -5%; left: -5%;
            width: 110%;
            height: 110%;
            background-size: cover;
            background-position: center;
            z-index: -2;
            transition: opacity 0.5s ease, transform 0.1s ease-out;
            opacity: 0;
            /* 添加平滑过渡效果 */
            will-change: transform, filter;
        }

        #background-layer {
            background-image: url('<?php echo esc_url( $custom_bg_url ); ?>');
            opacity: <?php echo ( get_option('argon_page_background_opacity') === '' ? '1' : get_option('argon_page_background_opacity') ); ?>;
        }

        <?php if ( $custom_bg_dark_url != '' ) : ?>
        #background-layer-dark {
            background-image: url('<?php echo esc_url( $custom_bg_dark_url ); ?>');
        }
        html.darkmode #background-layer-dark {
            opacity: <?php echo ( get_option('argon_page_background_opacity') === '' ? '1' : get_option('argon_page_background_opacity') ); ?>;
        }
        html.darkmode #background-layer {
            opacity: 0;
        } 
        <?php else: ?>
        html.darkmode #background-layer {
            filter: brightness(0.65);
        }
        <?php endif; ?>
        
        #birthday-overlay {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background: rgba(0, 0, 0, <?php echo esc_attr($bg_overlay_alpha); ?>);
            transition: background .5s ease;
        }
    </style>
    <style id="bg-blur-style">
        /* 调整模糊强度：修改 --bg-blur 值，例如 6px/8px/12px */
        :root { --bg-blur: 15px; }

        #background-layer-blurred,
        #background-layer-dark-blurred {
            /* 预先应用最大模糊，初始不可见 */
            filter: blur(var(--bg-blur));
            opacity: 0;
            will-change: opacity;
        }

        #background-layer-blurred {
            background-image: url('<?php echo esc_url( $custom_bg_url ); ?>');
        }

        <?php if ( $custom_bg_dark_url != '' ) : ?>
        #background-layer-dark-blurred {
            background-image: url('<?php echo esc_url( $custom_bg_dark_url ); ?>');
        }
        /* 在夜间模式下，模糊的暗色背景应该显示，模糊的亮色背景应该隐藏 */
        html.darkmode #background-layer-blurred {
            opacity: 0 !important;
        }
        <?php else: ?>
        /* 若未设置 dark 背景且夜间模式使用亮度滤镜时，模糊层也需要加上 */
        html.darkmode #background-layer-blurred {
            filter: brightness(0.65) blur(var(--bg-blur));
        }
        <?php endif; ?>
    </style>

    <!-- 背景层容器 -->
    <div id="background-layer" class="background-layer"></div>
    <div id="background-layer-blurred" class="background-layer"></div>
    <?php if ( $custom_bg_dark_url != '' ) : ?>
    <div id="background-layer-dark" class="background-layer"></div>
    <div id="background-layer-dark-blurred" class="background-layer"></div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backgroundLayer = document.getElementById('background-layer');
            const backgroundLayerBlurred = document.getElementById('background-layer-blurred');
            const backgroundLayerDark = document.getElementById('background-layer-dark');
            const backgroundLayerDarkBlurred = document.getElementById('background-layer-dark-blurred');
            const banner = document.getElementById('banner');
            
            // 视差效果强度
            const parallaxStrength = 0.025;
            // 平滑度（0-1，数值越小越平滑）
            const smoothness = 0.06;
            // 当模糊进度达到 10% 后停用鼠标/设备跟随
            const blurStopThreshold = 0.1;
            
            let targetX = 0;
            let targetY = 0;
            let currentX = 0;
            let currentY = 0;
            let animationId = null;
            let bannerHeight = banner ? banner.clientHeight : 0;
            let parallaxEnabled = true;
            
            function lerp(start, end, factor) {
                return start + (end - start) * factor;
            }

            function updateParallaxState() {
                const scrollY = window.scrollY;
                const progress = bannerHeight > 0 ? Math.max(0, Math.min(1, scrollY / bannerHeight)) : 0;
                parallaxEnabled = progress < blurStopThreshold;
                if (!parallaxEnabled) {
                    targetX = 0;
                    targetY = 0;
                }
            }
            
            function animate() {
                currentX = lerp(currentX, targetX, smoothness);
                currentY = lerp(currentY, targetY, smoothness);
                
                // 当变化很小时停止更新以节省性能
                if (Math.abs(currentX - targetX) < 0.01 && Math.abs(currentY - targetY) < 0.01) {
                    currentX = targetX;
                    currentY = targetY;
                }
                
                const transformValue = `translate(${currentX}px, ${currentY}px)`;
                
                // 应用到所有背景层
                if (backgroundLayer) backgroundLayer.style.transform = transformValue;
                if (backgroundLayerBlurred) backgroundLayerBlurred.style.transform = transformValue;
                if (backgroundLayerDark) backgroundLayerDark.style.transform = transformValue;
                if (backgroundLayerDarkBlurred) backgroundLayerDarkBlurred.style.transform = transformValue;
                
                animationId = requestAnimationFrame(animate);
            }
            
            updateParallaxState();
            animate();
            
            document.addEventListener('mousemove', function(e) {
                if (!parallaxEnabled) return;
                const mouseX = e.clientX;
                const mouseY = e.clientY;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;
                
                targetX = (mouseX - windowWidth / 2) * parallaxStrength;
                targetY = (mouseY - windowHeight / 2) * parallaxStrength;
            });
            
            if (window.DeviceOrientationEvent) {
                window.addEventListener('deviceorientation', function(event) {
                    if (!parallaxEnabled) return;
                    const tiltLR = event.gamma || 0;
                    const tiltFB = event.beta || 0;
                    
                    targetX = tiltLR * 2;
                    targetY = tiltFB * 2;
                });
            }

            window.addEventListener('scroll', updateParallaxState, { passive: true });
            
            window.addEventListener('resize', function() {
                bannerHeight = banner ? banner.clientHeight : 0;
                updateParallaxState();
                targetX = 0;
                targetY = 0;
                currentX = 0;
                currentY = 0;
            });
            
            window.addEventListener('beforeunload', function() {
                if (animationId) {
                    cancelAnimationFrame(animationId);
                }
            });
        });
    </script>
    <script id="bg-blur-script">
        document.addEventListener('DOMContentLoaded', function () {
            const banner = document.getElementById('banner');
            const blurredLayer = document.getElementById('background-layer-blurred');
            const blurredLayerDark = document.getElementById('background-layer-dark-blurred');

            if (!banner || !blurredLayer) return;

            let activeBlurredLayer = null;
            let ticking = false;
            let bannerHeight = banner.clientHeight;
            let lastProgress = -1; // 缓存进度值，避免不必要的样式更新

            function updateActiveLayer() {
                const isDarkMode = document.documentElement.classList.contains('darkmode');
                const targetLayer = isDarkMode && blurredLayerDark ? blurredLayerDark : blurredLayer;

                if (activeBlurredLayer !== targetLayer) {
                    // 重置旧图层的不透明度
                    if (activeBlurredLayer) {
                        activeBlurredLayer.style.opacity = 0;
                    }
                    activeBlurredLayer = targetLayer;
                }
            }

            function computeAndApplyBlur() {
                const scrollY = window.scrollY;
                
                // 进度计算：在 banner 高度内完成从 0 到 1 的过渡
                const progress = bannerHeight > 0 ? Math.max(0, Math.min(1, scrollY / bannerHeight)) : 0;
                
                // 应用缓动函数 (ease-out-quad), 感觉会更平滑
                const easedProgress = progress * (2 - progress);

                // 只有在进度值变化时才更新样式
                if (easedProgress !== lastProgress) {
                    if (activeBlurredLayer) {
                        activeBlurredLayer.style.opacity = easedProgress;
                    }
                    lastProgress = easedProgress;
                }
                
                ticking = false;
            }

            function onScroll() {
                if (!ticking) {
                    ticking = true;
                    requestAnimationFrame(computeAndApplyBlur);
                }
            }

            // 监听暗黑模式切换
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        updateActiveLayer();
                        // 模式切换后，强制重新计算并应用一次
                        lastProgress = -1; // 重置缓存
                        onScroll();
                    }
                }
            });

            observer.observe(document.documentElement, { attributes: true });

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', () => {
                bannerHeight = banner.clientHeight;
                onScroll();
            }, { passive: true });

            // 初始化
            updateActiveLayer();
            onScroll();
        });
    </script>
<?php endif; ?>



<?php if (get_option('argon_show_toolbar_mask') == 'true') { ?>
	<style>
		#banner:after {
			content: '';
			width: 100vw;
			position: absolute;
			left: 0;
			top: 0;
			height: 120px;
			background: linear-gradient(180deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.15) 35%, rgba(0,0,0,0) 100%);
			display: block;
			z-index: -1;
		}
		.banner-title {
			text-shadow: 0 5px 15px rgba(0, 0, 0, .2);
		}
	</style>
<?php } ?>







<div id="float_action_buttons" class="float-action-buttons fabtns-unloaded">
	<button id="fabtn_toggle_sides" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-hidden="true" tooltip-move-to-left="<?php _e('移至左侧', 'argon'); ?>" tooltip-move-to-right="<?php _e('移至右侧', 'argon'); ?>">
		<span class="btn-inner--icon fabtn-show-on-right"><i class="fa fa-caret-left"></i></span>
		<span class="btn-inner--icon fabtn-show-on-left"><i class="fa fa-caret-right"></i></span>
	</button>
	<button id="fabtn_back_to_top" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-label="Back To Top" tooltip="<?php _e('回到顶部', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-angle-up"></i></span>
	</button>
	<button id="fabtn_go_to_comment" class="btn btn-icon btn-neutral fabtn shadow-sm d-none" type="button" <?php if (get_option('argon_fab_show_gotocomment_button') != 'true') echo " style='display: none;'";?> aria-label="Comment" tooltip="<?php _e('评论', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-comment-o"></i></span>
	</button>
	<button id="fabtn_toggle_darkmode" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" <?php if (get_option('argon_fab_show_darkmode_button') != 'true') echo " style='display: none;'";?> aria-label="Toggle Darkmode" tooltip-darkmode="<?php _e('夜间模式', 'argon'); ?>" tooltip-blackmode="<?php _e('暗黑模式', 'argon'); ?>" tooltip-lightmode="<?php _e('日间模式', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-moon-o"></i><i class='fa fa-lightbulb-o'></i></span>
	</button>
	<button id="fabtn_toggle_blog_settings_popup" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" <?php if (get_option('argon_fab_show_settings_button') == 'false') echo " style='display: none;'";?> aria-label="Open Blog Settings Menu" tooltip="<?php _e('设置', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-cog"></i></span>
	</button>
	<div id="fabtn_blog_settings_popup" class="card shadow-sm" style="opacity: 0;" aria-hidden="true">
		<div id="close_blog_settings"><i class="fa fa-close"></i></div>
		<div class="blog-setting-item mt-3">
			<div style="transform: translateY(-4px);"><div id="blog_setting_toggle_darkmode_and_amoledarkmode" tooltip-switch-to-darkmode="<?php _e('切换到夜间模式', 'argon'); ?>" tooltip-switch-to-blackmode="<?php _e('切换到暗黑模式', 'argon'); ?>"><span><?php _e('夜间模式', 'argon');?></span><span><?php _e('暗黑模式', 'argon');?></span></div></div>
			<div style="flex: 1;"></div>
			<label id="blog_setting_darkmode_switch" class="custom-toggle">
				<span class="custom-toggle-slider rounded-circle"></span>
			</label>
		</div>
		<div class="blog-setting-item mt-3">
			<div style="flex: 1;"><?php _e('字体', 'argon');?></div>
			<div>
				<button id="blog_setting_font_sans_serif" type="button" class="blog-setting-font btn btn-outline-primary blog-setting-selector-left">Sans Serif</button><button id="blog_setting_font_serif" type="button" class="blog-setting-font btn btn-outline-primary blog-setting-selector-right">Serif</button>
			</div>
		</div>
		<div class="blog-setting-item mt-3">
			<div style="flex: 1;"><?php _e('阴影', 'argon');?></div>
			<div>
				<button id="blog_setting_shadow_small" type="button" class="blog-setting-shadow btn btn-outline-primary blog-setting-selector-left"><?php _e('浅阴影', 'argon');?></button><button id="blog_setting_shadow_big" type="button" class="blog-setting-shadow btn btn-outline-primary blog-setting-selector-right"><?php _e('深阴影', 'argon');?></button>
			</div>
		</div>
		<div class="blog-setting-item mt-3 mb-3">
			<div style="flex: 1;"><?php _e('滤镜', 'argon');?></div>
			<div id="blog_setting_filters" class="ml-3">
				<button id="blog_setting_filter_off" type="button" class="blog-setting-filter-btn ml-0" filter-name="off"><?php _e('关闭', 'argon');?></button>
				<button id="blog_setting_filter_sunset" type="button" class="blog-setting-filter-btn" filter-name="sunset"><?php _e('日落', 'argon');?></button>
				<button id="blog_setting_filter_darkness" type="button" class="blog-setting-filter-btn" filter-name="darkness"><?php _e('暗化', 'argon');?></button>
				<button id="blog_setting_filter_grayscale" type="button" class="blog-setting-filter-btn" filter-name="grayscale"><?php _e('灰度', 'argon');?></button>
			</div>
		</div>
		<div class="blog-setting-item mb-3">
			<div id="blog_setting_card_radius_to_default" style="cursor: pointer;" tooltip="<?php _e('恢复默认', 'argon'); ?>"><?php _e('圆角', 'argon');?></div>
			<div style="flex: 1;margin-left: 20px;margin-right: 8px;transform: translateY(2px);">
				<div id="blog_setting_card_radius"></div>
			</div>
		</div>
		<?php if (get_option('argon_show_customize_theme_color_picker') != 'false') {?>
			<div class="blog-setting-item mt-1 mb-3">
				<div style="flex: 1;"><?php _e('主题色', 'argon');?></div>
				<div id="theme-color-picker" class="ml-3"></div>
			</div>
		<?php }?>
	</div>
	<button id="fabtn_reading_progress" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-hidden="true" tooltip="<?php _e('阅读进度', 'argon'); ?>">
		<div id="fabtn_reading_progress_bar" style="width: 0%;"></div>
		<span id="fabtn_reading_progress_details">0%</span>
	</button>
</div>

<div id="content" class="site-content">




