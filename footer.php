					<footer id="footer" class="site-footer card shadow-sm border-0">
						<?php
							echo get_option('argon_footer_html');
						?>
						<!-- 字数统计-->
						<?php echo get_site_word_count_comparison_filtered(); ?>

						
						<div>Theme <a href="https://github.com/solstice23/argon-theme" target="_blank"><strong>Argon</strong></a><?php if (get_option('argon_hide_footer_author') != 'true') {echo " By solstice23"; }?></div>
						
					
					</footer>
				</main>
			</div>
		</div>
		<script src="<?php echo $GLOBALS['assets_path']; ?>/argontheme.js?v<?php echo $GLOBALS['theme_version']; ?>"></script>
		<?php if (get_option('argon_math_render') == 'mathjax3') { /*Mathjax V3*/?>
			<script>
				window.MathJax = {
					tex: {
						inlineMath: [["$", "$"], ["\\\\(", "\\\\)"]],
						displayMath: [['$$','$$']],
						processEscapes: true,
						packages: {'[+]': ['noerrors']}
					},
					options: {
						skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
						ignoreHtmlClass: 'tex2jax_ignore',
						processHtmlClass: 'tex2jax_process'
					},
					loader: {
						load: ['[tex]/noerrors']
					}
				};
			</script>
			<script src="<?php echo get_option('argon_mathjax_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js' : get_option('argon_mathjax_cdn_url'); ?>" id="MathJax-script" async></script>
		<?php }?>
		<?php if (get_option('argon_math_render') == 'mathjax2') { /*Mathjax V2*/?>
			<script type="text/x-mathjax-config" id="mathjax_v2_script">
				MathJax.Hub.Config({
					messageStyle: "none",
					tex2jax: {
						inlineMath: [["$", "$"], ["\\\\(", "\\\\)"]],
						displayMath: [['$$','$$']],
						processEscapes: true,
						skipTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code']
					},
					menuSettings: {
						zoom: "Hover",
						zscale: "200%"
					},
					"HTML-CSS": {
						showMathMenu: "false"
					}
				});
			</script>
			<script src="<?php echo get_option('argon_mathjax_v2_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/mathjax@2.7.5/MathJax.js?config=TeX-AMS_HTML' : get_option('argon_mathjax_v2_cdn_url'); ?>"></script>
		<?php }?>
		<?php if (get_option('argon_math_render') == 'katex') { /*Katex*/?>
			<link rel="stylesheet" href="<?php echo get_option('argon_katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : get_option('argon_katex_cdn_url'); ?>katex.min.css">
			<script src="<?php echo get_option('argon_katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : get_option('argon_katex_cdn_url'); ?>katex.min.js"></script>
			<script src="<?php echo get_option('argon_katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : get_option('argon_katex_cdn_url'); ?>contrib/auto-render.min.js"></script>
			<script>
				document.addEventListener("DOMContentLoaded", function() {
					renderMathInElement(document.body,{
						delimiters: [
							{left: "$$", right: "$$", display: true},
							{left: "$", right: "$", display: false},
							{left: "\\(", right: "\\)", display: false}
						]
					});
				});
			</script>
		<?php }?>

		<?php if (get_option('argon_enable_code_highlight') == 'true') { /*Highlight.js*/?>
			<link rel="stylesheet" href="<?php echo $GLOBALS['assets_path']; ?>/assets/vendor/highlight/styles/<?php echo get_option('argon_code_theme') == '' ? 'vs2015' : get_option('argon_code_theme'); ?>.css">
		<?php }?>

	</div>
</div>
<?php 
	wp_enqueue_script("argonjs", $GLOBALS['assets_path'] . "/assets/js/argon.min.js", null, $GLOBALS['theme_version'], true);
?>
<?php wp_footer(); ?>



<!--鼠标样式
<style>
    body {
    	cursor: url(https://loneapex.cn/extra-js/mouse/pointer2.cur), default;
    }
    button,select,a,a:hover{
    	cursor: url(https://loneapex.cn/extra-js/mouse/hand.cur), pointer;
    }
    textarea,p,font,h1,h2,h3,h4,h5,h6,input:focus{
    	cursor:url(https://loneapex.cn/extra-js/mouse/beam6.cur), text;	
    }
</style>
-->

<!-- 标题等的大楷体 -->
<link
  rel="preload"
  as="style"
  crossorigin
  href="https://fontsapi.zeoseven.com/482/main/result.css"
  onload="this.rel='stylesheet';"
/>
<noscript>
  <link rel="stylesheet" href="https://fontsapi.zeoseven.com/482/main/result.css" />
</noscript>
<style>
  .banner-title-inner,
  .leftbar-banner-title,
  .nav-wrapper,
  .leftbar-announcement-title,
  .navbar-title {
    font-family: 'CooperZhengKai' !important;
    font-size: 125% !important;
  }
</style>


<!-- 主字体-Glow Sans-(全局字体，主要)预加载 -->
<link
  rel="preload"
  as="style"
  crossorigin
  href="https://fontsapi.zeoseven.com/537/main/result.css"
  onload="this.rel='stylesheet';"
/>
<noscript>
    <link rel="stylesheet" href="https://fontsapi.zeoseven.com/537/main/result.css" />
</noscript>

<style>
  body {
    font-family: 'Glow Sans SC Normal', sans-serif;
    font-weight: normal;
  }
</style>

<!-- 引用-楷体 -->
<link href="https://fontsapi.zeoseven.com/993/main/result.css" onload="this.rel='stylesheet'" rel="preload" as="style" crossorigin />
<noscript><link rel="stylesheet" href="https://fontsapi.zeoseven.com/993/main/result.css" /></noscript>
<style>
    blockquote {
        font-family: "LXGW Bright";
        font-weight: light;
    }
</style>


<!--全局字体
<style>
@font-face {
    font-family: TsangerYuYang;
    src: url(https://source.loneapex.cn/others/TsangerYuYangT_W02_W02.woff2) format('woff2');
    font-display: swap;
}

body{
    font-family: 'TsangerYuYang'
}
</style>
-->


<style>
/* 日间模式 */
.nav-link-inner--text,
.banner-title-inner,
.footer-link,
.banner-subtitle,
.footer-links{
    color: #525f7f;
}

/* 暗色模式 */
html.darkmode .nav-link-inner--text,
html.darkmode .banner-title-inner,
html.darkmode .footer-link,
html.darkmode .banner-subtitle,
html.darkmode .footer-links{
    color: #ffffff !important;
}


</style>








<!-- 标题自动锚点: Start -->
<script>
window.addEventListener('load', function() {
    // 构建标题文本与 Argon ID 的映射表
    const headers = document.querySelectorAll('h1[id], h2[id], h3[id], h4[id], h5[id], h6[id]');
    const textToIdMap = new Map();
    headers.forEach(header => {
        const id = header.id;
        const text = header.textContent.trim();
        textToIdMap.set(text, id); // 标题文本 -> ID 映射
    });
 
    // 替换页面中的基于文本的锚点链接
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        const targetText = decodeURIComponent(link.getAttribute('href').slice(1)); // 获取锚点文本
        if (textToIdMap.has(targetText)) {
            link.setAttribute('href', `#${textToIdMap.get(targetText)}`); // 替换为 Argon 的 ID
        }
    });
 
    //文外跳转
    if (window.location.hash) {
        const hash = window.location.hash.slice(1);  // 去掉 #
        let targetElement;
        // 优先检查哈希值是否是一个有效的 ID
        targetElement = document.getElementById(hash);
        if (!targetElement) {
            // 如果哈希值是标题文本，检查映射表
            const decodedHash = decodeURIComponent(hash);  // 解码哈希值
            if (textToIdMap.has(decodedHash)) {
                const targetId = textToIdMap.get(decodedHash);  // 获取对应的 ID
                targetElement = document.getElementById(targetId);  // 查找对应 ID 的元素
            }
             // 替换图片的 src 属性
            const lazyImages = document.querySelectorAll('img.lazyload[data-original]');
            lazyImages.forEach(img => {
                img.src = img.getAttribute('data-original'); // 直接替换为真正的图片链接
            });
        }
        // 如果找到了目标元素，滚动到该元素
        if (targetElement) {
            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
</script>
<!-- 标题自动锚点: End -->


<!-- 杂项 -->
<style>
/* 设置右下角的aplayer播放器的字体颜色*/
.aplayer-list-title,
.aplayer-title {
  color: #525f7f; 
}
/* 副标题斜体 */
.banner-subtitle d-block {
font-style: oblique;
}
.banner-subtitle d-block typing-effect {
font-style: oblique;
}


/* 将根元素（html）的字体大小设置为原来的 110% */
html {
  font-size: 102%;
} !important

/* 确保所有使用 em/rem 单位的文字都会相应放大 */
body {
  font-size: 1rem; /* 如果你之前有对 body font-size 做过覆盖，请保留此行 */
}

</style>

<!-- 文字放大
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // 获取所有可见元素
    var all = document.getElementsByTagName("*");
    for (var i = 0; i < all.length; i++) {
      var el = all[i];
      // 读取当前计算后的字体大小
      var style = window.getComputedStyle(el, null).getPropertyValue("font-size");
      var currentSize = parseFloat(style);
      // 设置为原来的 110%
      el.style.fontSize = (currentSize * 1.02) + "px";
    }
  });
</script>
-->
<script>
  const style1 = 'color: #fff; background: #4caf50; padding: 6px 12px; border-radius: 4px; font-size: 16px;';
  const style2 = 'color: #4caf50; font-size: 14px;';
  const style3 = 'color: #aaa; font-size: 12px; font-style: italic;';

  console.log('%c🎉 这里是Mimosa，欢迎来到我的博客', style1);
  console.log('%c如果你在寻找灵感或想深入了解，请随意探索', style2);
</script>





<!--全页特效开始-->
<!--鼠标跟随特效，允许任何设备
<script type="text/javascript">
    $.getScript("https://loneapex.cn/extra-js/beauty/mouse-click.js"); //小烟花特效
    $.getScript("https://loneapex.cn/extra-js/beauty/fairyDustCursor.min.js"); // 鼠标移动的仙女棒特效
</script>-->

<!-- 鼠标跟随特效特效，设备检测
<script src="https://loneapex.cn/extra-js/beauty/mobile-detect.js"></script>
<script type="text/javascript">
    // 设备检测
    var md = new MobileDetect(window.navigator.userAgent);
    // PC生效，手机/平板不生效
    // md.mobile(); md.phone();
    if(!md.phone()){
        if(!md.tablet()){
            // 雪花特效
            // $.getScript("/wp-content/themes/argon/extra-js/extra-js/beauty/xiaxue.js");
            // 樱花特效
            // $.getScript("/wp-content/themes/argon/extra-js/extra-js/beauty/yinghua2.js");
            // 小烟花特效
            // $.getScript("/wp-content/themes/argon/extra-js/extra-js/beauty/mouse-click.js");
            // 鼠标移动的仙女棒特效
            // $.getScript("/wp-content/themes/argon/extra-js/extra-js/beauty/fairyDustCursor.min.js");
        }   
    }
</script>
-->

<!--星空＋鼠标粒子效果
<canvas id="canvas"></canvas>
<style>
    #canvas {
      position: fixed;     /* 固定在视口 */

      top: 0;
      left: 0;
      z-index: -1;         /* 放到内容后面 */
    }
</style>
<script>
    // DOM 加载后再初始化,确保脚本在 Canvas 元素加载完毕后执行
    document.addEventListener('DOMContentLoaded', function() {
      setCanvasSize();
      init();
      window.addEventListener('resize', setCanvasSize);
    });
    // 用户可能会改变浏览器大小，最好在 resize 时也更新 Canvas 大小：
    function setCanvasSize() {
      WIDTH = document.documentElement.clientWidth;
      HEIGHT = document.documentElement.clientHeight;
      canvas.width = WIDTH;
      canvas.height = HEIGHT;
    }
    window.addEventListener('resize', setCanvasSize);

</script>
<script type="text/javascript" src="/wp-content/themes/argon/extra-js/beauty/star_style.js"></script>
-->
<!--星空＋鼠标粒子效果 结束-->


<!-- 配置 meting api -->
<!-- **注意**：请务必按照此格式填写api地址：https://your-api-server.com/?server=:server&type=:type&id=:id&auth=:auth&r=:r -->
<script>var meting_api='https://loneapex.cn/meting-api/?server=:server&type=:type&id=:id&auth=:auth&r=:r';</script>
<!-- 小播放器信息 -->
<link rel="stylesheet" href="/wp-content/themes/argon/extra-js/Aplayer/APlayer.min.css">
<script src="/wp-content/themes/argon/extra-js/Aplayer/APlayer.min.js"></script>
<script src="/wp-content/themes/argon/extra-js/Aplayer/Meting.min.js"></script>
<meting-js 
    server="netease" 
    type="playlist" 
    id="8159389492"
    fixed="true" 
    mini="true"
    order="list"
    loop="all"
    preload="false"
    list-folded="true"
></meting-js>

<!-- 加载预制格式 -->
<script src="<?php echo $GLOBALS['assets_path']; ?>/includes/prefabricated-format.js?v<?php echo $GLOBALS['theme_version']; ?>"></script>

<script>
  function hexToRgb(hex,op){
    let str = hex.slice(1);
    let arr;
    if (str.length === 3) arr = str.split('').map(d => parseInt(d.repeat(2), 16));
    else arr = [parseInt(str.slice(0, 2), 16), parseInt(str.slice(2, 4), 16), parseInt(str.slice(4, 6), 16)];
    return  `rgb(${arr.join(', ')}, ${op})`;
};
 
  let themeColorHex = getComputedStyle(document.documentElement).getPropertyValue('--themecolor').trim();
  let op1 = 0.8
  let themeColorRgb = hexToRgb(themeColorHex,op1);
  let themecolorGradient = getComputedStyle(document.documentElement).getPropertyValue('--themecolor-gradient')*
  document.documentElement.style.setProperty('--themecolor-gradient',themeColorRgb)
 
  let op2 = 0.8
  // 方法一：
  let colorTint92 = getComputedStyle(document.documentElement).getPropertyValue('--color-tint-92').trim();
  colorTint92 += ', '+op2;
  document.documentElement.style.setProperty('--color-tint-92',colorTint92)
  
  let op3 = 0.8
  let colorShade90 = getComputedStyle(document.documentElement).getPropertyValue('--color-shade-90').trim();
  colorShade90 += ', ' + op3;
  document.documentElement.style.setProperty('--color-shade-90',colorShade90)
 
  let op4 = 0.8
  let colorShade86 = getComputedStyle(document.documentElement).getPropertyValue('--color-shade-86').trim();
  colorShade86 += ', ' + op4;
  document.documentElement.style.setProperty('--color-shade-86',colorShade86)
</script>


<!-- 评论ip的自适应颜色 -->
<style>
.comment--location {
  color: #333333 !important;
  fill: #333333 !important;
}

.comment--location svg {
  color: #333333 !important;
  fill: #333333 !important;
}

/* 暗色模式样式 */
html.darkmode .comment--location {
  color:rgb(196, 248, 241) !important;
  fill: rgb(196, 248, 241) !important;
}

html.darkmode .comment--location svg {
  color: rgb(196, 248, 241) !important;
  fill: rgb(196, 248, 241) !important;
}
</style>

<!-- 引入样式 -->
<link
  rel="stylesheet"
  href="https://loneapex.cn/wp-content/themes/argon/extra-js/beauty/sakana.min.css"
/>

<!-- 挂载容器：固定在页面右下角 -->
<div id="sakana-container" class="sakana-fixed" aria-hidden="false"></div>

<style>
  /* 固定右下角容器（大小可调整） */
  .sakana-fixed {
    position: fixed;
    right: 65px;
    bottom: 5px;
    width: 180px;
    height: 180px;
    z-index: 1;
    pointer-events: auto;
    /* BFC: 确保 autoFit 工作正常（如果用 autoFit） */
    contain: layout;
  }

  /* 移动端隐藏：如果小于等于 768px，完全隐藏（双保险） */
  @media (max-width: 768px) {
    .sakana-fixed {
      display: none !important;
    }
  }
</style>

<!-- 引入脚本并初始化（会检查屏幕宽度，移动端不初始化） -->
<script>
  function initSakanaMulti() {
    // 如果是移动端（小屏）就不初始化，避免加载/渲染
    if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) {
      return;
    }

    /* ========== 请把这里的 URL 换成你自己的图片地址 ========== */
    const images = [
      { name: 'takina', url: 'https://loneapex-cn.oss-accelerate.aliyuncs.com/loneapex.cn-others/sora.png' },
      { name: 'chisato', url: 'https://loneapex-cn.oss-accelerate.aliyuncs.com/loneapex.cn-others/tsubaki.png' },
    ];
    /* ========================================================= */

    // 取一个内置角色做模板（优先 chisato，再 fallback takina）
    const template = SakanaWidget.getCharacter('chisato') || SakanaWidget.getCharacter('takina') || {
      image: '',
      initialState: {
        i: 1, s: 1, d: 0.9, r: 0, y: 10, t: 0, w: 0
      }
    };

    // 安全复制函数（structuredClone 或 JSON 备选）
    function deepClone(obj) {
      if (typeof structuredClone === 'function') return structuredClone(obj);
      return JSON.parse(JSON.stringify(obj));
    }

    // 注册每个自定义角色
    images.forEach((item) => {
      const c = deepClone(template);
      c.image = item.url;
      // 可在这里自定义每个角色的 initialState（例如大小、惯性等）
      // c.initialState = { ...c.initialState, i: 0.8, d: 0.95 };
      SakanaWidget.registerCharacter(item.name, c);
    });

    // 默认第一个角色
    const startName = images.length ? images[0].name : 'chisato';

    // 创建并挂载组件（无外部切换器）
    new SakanaWidget({
      character: startName,
      autoFit: true,     // 挂载容器会被适配（容器需为 BFC => fixed 已满足）
      controls: true,    // 若希望完全不显示任何控制栏，改为 false
      draggable: true,
      rod: true,
      size: 180
    }).mount('#sakana-container');
  }

  // 只在脚本载入完成后初始化（异步加载的情况确保 onload 调用 init）
  window.addEventListener('DOMContentLoaded', function () {
    if (window.SakanaWidget) {
      initSakanaMulti();
    }
  });
</script>

<!-- 异步引入官方脚本（onload 双保险） -->
<script
  async
  onload="if(window.SakanaWidget){ initSakanaMulti(); }"
  src="https://loneapex.cn/wp-content/themes/argon/extra-js/beauty/sakana.min.js"
></script>

<!-- 技术债，懒得搞了，日程表多余的元素直接隐藏掉 -->
<!-- 什么叫只有不到三千行代码，这个奇葩的元素在第5000行，我服了orz. -->
<script>
  document.addEventListener('DOMContentLoaded', ()=> {
  const el = document.querySelector('.timeline-back-to-top');
  if(el) el.remove();
});
</script>

<script>
    console.log('一切准备就绪。');
</script>

</body>
<?php echo get_option('argon_custom_html_foot'); ?>

</html>

