<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>zheng's new year</title>
  <link rel="stylesheet" type="text/css" href="http://bulen-jack.cn/wp-content/uploads/2023/01/normalize.css" />
  <link rel="stylesheet" type="text/css" href="http://bulen-jack.cn/wp-content/uploads/2023/01/hovertree.css">
</head>
<body>
<div style="font-size: 20px;color: #eeede8" id="text">-</div>
<script>
  var str = '你好！<br>话说回来，2022年真是神奇的一年啊<br>在这里，我还有许多话想对你说<br>';
  var i = 0;
  function typing(){
    var divTyping = document.getElementById('text');
    if (i <= str.length) {
      divTyping.innerHTML = str.slice(0, i++) + '_';
      setTimeout('typing()', 200);
    }
    else{
      divTyping.innerHTML = str;
    }
  }
  typing();
</script>



<div class="hovertree"></div>
<script type="text/javascript" src="http://bulen-jack.cn/wp-content/uploads/2023/01/jquery-1.11.0.min_.js"></script>
<script type="text/javascript" src="http://bulen-jack.cn/wp-content/uploads/2023/01/jquery.fireworks.js"></script>
<script type="text/javascript">
    $('.hovertree').fireworks({
	  sound: true, // sound effect
	  opacity: 0.5,
	  width: '100%',
	  height: '100%'
	});
</script>



<audio preload autoplay loop id="music">
     <source src="https://bulen-jack.cn/wp-content/uploads/2023/01/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90%E6%A0%A1%E5%9B%ADCMJ-%E9%93%B6%E6%B2%B3%E8%B5%B4%E7%BA%A6.mp3" type="audio/mpeg">
</audio>
<script type="text/javascript">
    window.onload = function(){
             setInterval("toggleSound()",1);
        }

    function toggleSound() {
                var music = document.getElementById("music");//获取ID
                if (music.paused) { //判读是否播放
                    music.paused=false;
                    music.play(); //没有就播放
                }
        }
</script>


</body>
</html>