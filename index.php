<?php


include('phpqrcode/phpqrcode.php');

$img = 'see.jpg';
$qrcode = 'lkj.png';
if (isset($_GET['qrcode']) && $_GET['qrcode'] != '') {
    $tempDir = "tempqrcode.png";
    $codeContents = $_GET['qrcode'];
    \QRcode::png($codeContents, $tempDir, QR_ECLEVEL_L, 8);
    $qrcode = $tempDir;
}

$content = isset($_GET['water']) && $_GET['water'] != '' ? $_GET['water'] : 'likangjun.com';

mergerImg($img, $qrcode, true, true, 0.8, false, $content);

/**
 * author lkj
 * date 2015/12/26
 * @param string $img 底图路径
 * @param string $qrcode 二维码路径
 * @param int $x 二维码左边距 true水平居中
 * @param int $y 二维码上边距 true垂直居中
 * @param int $percent 二维码按比例缩放
 * @param bool $save false直接输出true保存文件
 */

/**
 * bool imagecopy ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h )
 * 将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
 */

function mergerImg($img, $qrcode, $x, $y, $percent = 1, $save = false, $content)
{
    /***底图处理****/
    list($max_width, $max_height) = getimagesize($img);
    $dests = imagecreatetruecolor($max_width, $max_height);
    $dst_im = imagecreatefromjpeg($img);
    imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
    imagedestroy($dst_im);

    /*********二维码大小调整并保存********** */
    // Get new sizes
    list($width, $height) = getimagesize($qrcode);

    $newwidth = $width * $percent;
    $newheight = $height * $percent;
    // Load
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $source = imagecreatefrompng($qrcode);
    // Resize
    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    $name = 'tmep.png';//缩放二维码存放临时文件
    // Save
    imagepng($thumb, $name);

    /*******图片合成*********/
    $src_im = imagecreatefrompng($name);
    $src_info = getimagesize($name);

    if ($x === true) {//水平居中
        $padding_left = ($max_width - $src_info[0]) / 2;
    } else {
        $padding_left = $x;
    }
    if ($y === true) {//垂直居中
        $padding_top = ($max_height - $src_info[1]) / 2;
    } else {
        $padding_top = $y;
    }
    imagecopy($dests, $src_im, $padding_left, $padding_top, 0, 0, $src_info[0], $src_info[1]);

    /*******写入文字******/
    $font_size = 18;
    $color = imagecolorallocate($dests, 255, 255, 255); //字体颜色
    $fontfile = "font.ttf"; //字体文件
    $string_len = mb_strlen($content, 'UTF8');
    //字符串长度超出
    if ($string_len > 16) {
        $content = mb_substr($content, 0, 16, "utf-8") . "...";
    }
    //获取字体高宽
    $fontarea = imagettfbbox($font_size, 0, $fontfile, '@' . $content);
    $font_width = $fontarea[4] - $fontarea[6];

    //文字距离右下角12px
    $text_x = $max_width - $font_width - 12;
    $text_y = $max_height - 12;
    imagettftext($dests, $font_size, 0, $text_x, $text_y, $color, $fontfile, '@' . $content);
    unlink($name);//删除临时文件

    /*******保存文件********/
    if ($save) {
        $savepath = "qrcode.png";
        $result = imagepng($dests, $savepath);
        imagedestroy($dests);
        echo $result ? 'success' : 'fail';
    }

    /*******输出到浏览器********/
    header('Content-Type: image/jpeg');
    imagepng($dests);
    imagedestroy($dests);
}
