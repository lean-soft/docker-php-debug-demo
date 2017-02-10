<?PHP
//******************************* Lizenzbestimmungen *******************************//
//                                                                                  //
//  Der Quellcode von diesen Forum ist urheberrechtlich geschützt.                     //
//  Bitte beachten Sie die AGB auf www.frank-karau.de/agb.php                       //
//                                                                                  //
//  Dieser Lizenzhinweis darf nicht entfernt werden.                                //
//                                                                                  //
//  (C) phpFK - Forum ohne MySQL - www.frank-karau.de - support@frank-karau.de      //
//                                                                                  //
//**********************************************************************************//

session_name('sid');
session_start();
$chars = "QWERTZUPASDFGHJKLYXCVBNM123456789";	
$code = "";
mt_srand(time());
for ($i=0; $i<6; $i++) $code = $code.$chars[mt_rand (0,strlen($chars)-1)];
$_SESSION['new_code2'] = $code;
Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
Header("Content-type: image/png"); 
$pic=ImageCreate(100,25); 
$bg=ImageColorAllocate($pic,30,30,30); 
$lines=ImageColorAllocate($pic,150,150,150); 
ImageFilledRectangle($pic, 0, 0, 100, 25, $bg); 
ImageRectangle($pic, 1, 1, 98, 23, $lines);
for ($i = 0; $i < 6; $i++) 
{
	$col1 = ImageColorAllocate($pic,mt_rand(200, 255),mt_rand(200, 255),mt_rand(200, 255));
	ImageString($pic, mt_rand(3, 6), (15*$i+7), mt_rand(3, 6), substr($code, $i, 1), $col1); 
}
ImageLine($pic, 1, 12, 98, 12, $lines);
$x = mt_rand(23,28);
ImageLine($pic, $x, 1, $x, 23, $lines);
$x = mt_rand(47,54);
ImageLine($pic, $x, 1, $x, 23, $lines);
$x = mt_rand(73,78);
ImageLine($pic, $x, 1, $x, 23, $lines);
ImagePNG($pic); 
ImageDestroy($pic); 
?> 
