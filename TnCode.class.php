<?php
class TnCode
{
    public $im          = null; 
    public $im_fullbg   = null;
    public $im_bg       = null;
    public $im_slide    = null;
    public $bg_width    = 240;
    public $bg_height   = 150;
    public $mark_width  = 50;
    public $mark_height = 50;
    public $bg_num      = 6; //原始图库数量
    public $_x          = 0;
    public $_y          = 0;
    //容错象素 越大体验越好，越小破解难度越高
    public $_fault      = 3;
    const CACHE_FIELD = 'SLIDINNG_VERIFICATION_FIELD'; //缓存字段
    const CACHE_ERROR_FIELD = 'SLIDINNG_VERIFICATION_ERROR_FIELD'; //错误标识字段
    const CACHE_CHECK_RES = 'SLIDINNG_VERIFICATION_CHECK_RES'; //检查结果字段
    const CACHE_CHECK_TIME = 'SLIDINNG_VERIFICATION_CHECK_TIME'; //检查时间字段
    public function __construct(){
        ini_set('display_errors','On');
        //
        error_reporting(0);
        if(!isset($_SESSION)){
            session_start();
        }
    }

    public function make(){
        $this->_init(); //下
        $this->_createSlide(); //中
        $this->_createBg(); //上
        $this->_merge();
        $this->_imgout();
        $this->_destroy();
    }

    public function check($offset=''){
        if (!$this->cache(self::CACHE_FIELD)) {
            return false;
        }
        if (!$offset) {
            $offset = $_REQUEST['tn_r'];
        }
        $ret = abs($this->cache(self::CACHE_FIELD)-$offset) <= $this->_fault;
        if($ret){
            $this->cache(self::CACHE_CHECK_RES, 'ok');       //检查结果
            $this->cache(self::CACHE_CHECK_TIME, time());
            $this->cache(self::CACHE_FIELD, null);
        }else{
            $this->cache(self::CACHE_CHECK_RES, 'error');        //检查结果
            $error_num = $this->cache(self::CACHE_ERROR_FIELD);
            ++$error_num;
            $this->cache(self::CACHE_ERROR_FIELD, $error_num);
            if($error_num > 10){//错误10次必须刷新
                $this->cache(self::CACHE_FIELD, null);
            }
        }
        return $ret;
    }

    //需保证每个用户的唯一性(每个用户都有单独的一份)
    public function cache($key, $val = '') {
        if ($val === '') {
            return $_SESSION[$key];
        }
        if ($val === null) {
            unset($_SESSION[$key]);
            return;
        }
        //设置
        return $_SESSION[$key] = $val;
    }

    private function _init(){
        $bg = mt_rand(1,$this->bg_num);
        $file_bg = dirname(__FILE__).'/bg/'.$bg.'.png';
        $this->im_fullbg = imagecreatefrompng($file_bg);  //由文件或 URL 创建一个新图象
        $this->im_bg = imagecreatetruecolor($this->bg_width, $this->bg_height);  //新建指定长宽的一个真彩色图像
        imagecopy($this->im_bg,$this->im_fullbg,0,0,0,0,$this->bg_width, $this->bg_height); //拷贝图像的一部分
        //imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h ) : bool
        //将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
        $this->im_slide = imagecreatetruecolor($this->mark_width, $this->bg_height);
        $this->_x = mt_rand(50,$this->bg_width-$this->mark_width-1);
        $this->cache(self::CACHE_FIELD, $this->_x);
        $this->cache(self::CACHE_ERROR_FIELD, 0);
        $this->_y = mt_rand(0,$this->bg_height-$this->mark_height-1);
    }

    private function _destroy(){
        imagedestroy($this->im);
        imagedestroy($this->im_fullbg);
        imagedestroy($this->im_bg);
        imagedestroy($this->im_slide);
    }

    private function _imgout(){
        if(!$_GET['nowebp']&&function_exists('imagewebp')){//优先webp格式，超高压缩率
            $type = 'webp';
            $quality = 40;//图片质量 0-100
        }else{
            $type = 'png';
            $quality = 7;//图片质量 0-9
        }
        header('Content-Type: image/'.$type);
        $func = "image".$type;
        $func($this->im,null,$quality);
    }

    private function _merge(){
        $this->im = imagecreatetruecolor($this->bg_width, $this->bg_height*3);//新建指定长宽的一个真彩色图像
        imagecopy($this->im, $this->im_bg,0, 0 , 0, 0, $this->bg_width, $this->bg_height); //最下方的图
        //imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h ) : bool
        //将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
        imagecopy($this->im, $this->im_slide,0, $this->bg_height , 0, 0, $this->mark_width, $this->bg_height); //中间的图 
        imagecopy($this->im, $this->im_fullbg,0, $this->bg_height*2 , 0, 0, $this->bg_width, $this->bg_height); //最上方的图
        imagecolortransparent($this->im,0);//16777215  将某个颜色定义为透明色 一旦设定了某个颜色为透明色，图像中之前画为该色的任何区域都成为透明的。
    }

    private function _createBg(){
        $file_mark = dirname(__FILE__).'/img/mark.png';
        $im = imagecreatefrompng($file_mark);  //由文件或 URL 创建一个新图象
        // header('Content-Type: image/png');
        //imagealphablending( $im, true);
        imagecolortransparent($im,0);//16777215 将某个颜色定义为透明色 一旦设定了某个颜色为透明色，图像中之前画为该色的任何区域都成为透明的。
        //imagepng($im);exit;
        imagecopy($this->im_bg, $im, $this->_x, $this->_y  , 0  , 0 , $this->mark_width, $this->mark_height);
        imagedestroy($im);
    }

    private function _createSlide(){
        $file_mark = dirname(__FILE__).'/img/mark2.png';
        $img_mark = imagecreatefrompng($file_mark); //由文件或 URL 创建一个新图象
        imagecopy($this->im_slide, $this->im_fullbg,0, $this->_y , $this->_x, $this->_y, $this->mark_width, $this->mark_height);
        imagecopy($this->im_slide, $img_mark,0, $this->_y , 0, 0, $this->mark_width, $this->mark_height);
        imagecolortransparent($this->im_slide,0);//16777215 将某个颜色定义为透明色 一旦设定了某个颜色为透明色，图像中之前画为该色的任何区域都成为透明的。
        //header('Content-Type: image/png');
        //imagepng($this->im_slide);exit;
        imagedestroy($img_mark);
    }

}
?>
