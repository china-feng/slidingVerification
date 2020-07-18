<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TnCode</title>
    <script type="text/javascript" src="./js/tn_code.js?v=31"></script>
    <link rel="stylesheet" type="text/css" href="./css/style.css?v=25" />
<style type="text/css">

</style>
</head>
<body style="text-align:center;">
    <div class="tncode" style="text-align: center;margin: 100px auto;"></div>
   <!--  <div id="tncode_container"></div> -->
    <a href="http://www.39gs.com/archive/259.html">php+js实现极验滑块拖动验证码-tncode</a> | <a href="//www.39gs.com/">拖鞋小站</a>

<script type="text/javascript">
// $TN.onsuccess(function(){
// 	//验证通过
//     console.log($TN);
// });

// var $TN = tncode;
var _old_onload = window.onload;
var img_make_url = 'http://localhost/tncode-master/tncode.php?nowebp=1';
var check_url = 'http://localhost/tncode-master/check.php';
window.onload = function(){   //网页加载完毕后立刻执行的操作
    if(typeof _old_onload == 'function'){
        _old_onload();
    }
    tncode.init(); //初始化
    tncode.img_make_url = img_make_url;
    tncode.check_url = check_url;
};
</script>
</body>
</html>
