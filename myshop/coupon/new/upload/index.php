<?php
/* @var $this CouponApp */

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>
</head>
<body>
<?php

//  My shop ID.
$shop_id = $this->GetShopID();

//	Prepare the upload dir.
$upload_dir = $this->ConvertPath("app:/temp/$shop_id/new/");


//	Check uploaded file with ValidateImage()
//★op-coreのValidateInputを利用できないので処理を部分的に移植。input名はハードコードで処理★
$err = null;//エラーメッセージスタック用
if(!isset($_FILES['upload_image'])){
	$err = 'アップロードに失敗しました。';
}

//if($_FILES['upload_image']['error'] == 4){
if($_FILES['upload_image']['error'] == 0){

	//  image info
	if(!$info = getimagesize($_FILES['upload_image']['tmp_name'])){
		$err = '正しい画像ファイルを指定してください。';
	}
	
	//  image different (does not match mime type)
	if($info['mime'] !== $_FILES['upload_image']['type']){
		$err = '正しい画像ファイルを指定してください。';
	}
	
	//		$width  = $info[0];
	//		$height = $info[1];
	$mime   = $info['mime'];
	//		$size   = $_FILES[$input->name]['size'];
	list($type,$ext) = explode('/',$mime);
	
	//	Check if the file is image file.
	if( $type !== 'image' ){
		$err = '正しい画像ファイルを指定してください。';
	}
	
}else{
	$err = 'アップロードに失敗しました。';
}

//	Show Error (if any)
if( $err !==null ){
	echo '<script>parent.document.getElementById(\'form_coupon_image\').reset();</script>';
	echo '<script>alert(\''.$err.'\');</script>';
	return;
}

//	Retrieve data from $_FILES and set them into local valiables.
$_file    = $_FILES['upload_image'];
$filename = $_file['name'];
$tmp      = $_file['tmp_name'];

//	Form5のファイルアップロード処理を部分的に移植
//	extention
$temp = explode('.',$filename);
$ext  = array_pop($temp);
$op_uniq_id = $this->GetCookie( self::KEY_COOKIE_UNIQ_ID );
$time = microtime(true);//for 'salt'
$path = $upload_dir . md5($filename . $op_uniq_id . $time ).".jpg";

//	Check if the distination dir exists.
if(!file_exists( $dirname = dirname($path) )){
	$this->mark("Does not exists directory. ($dirname)");
	if(!$io = mkdir( $dirname, 0777, true ) ){
		$this->StackError("Failed to make directory. (".dirname($path).")");
		return;
	}
}


//	Image conversion.
//	Reference:
//		http://www.geekpage.jp/web/php-gd/
//		http://redwarcueid.seesaa.net/article/167597752.html

//	Set file path.
$path_from = $tmp;
$path_to   = $path;

//	Extract image data from tmp file, based on original file extension.
if( $ext === 'jpg' ){
	$img = imagecreatefromjpeg($path_from);
}elseif( $ext === 'png' ){
	$img = imagecreatefrompng($path_from);
}else{
	unlink($path_from);
	$err = 'jpg または png 形式の画像のみ使用できます。';
	echo '<script>parent.document.getElementById(\'form_coupon_image\').reset();</script>';
	echo '<script>alert(\''.$err.'\');</script>';
	return;
}

//	retrieve size of source image.
$base_size = 320;
list($src_x, $src_y) = getimagesize($path_from);

//	get original aspect ratio and set new image size.
if( $src_x > $src_y ){
	$dst_x = $base_size;
	$dst_y = $src_y / ( $src_x / $base_size );
}else{
	$dst_y = $base_size;
	$dst_x = $src_x / ( $src_y / $base_size);
}

//	Create new image with dimension resized.
$new_img = imagecreatetruecolor($dst_x, $dst_y);
if( imagecopyresampled($new_img, $img, 0,0,0,0,$dst_x, $dst_y, $src_x, $src_y) == false ){
	$this->StackError("Image convert and resize is failed.");
}

//	Output the resized image to $shop_id/$coupon_id folder.
$res = imagejpeg($new_img, $path_to);
if( $res == false ){
	$this->StackError("Image output is failed.");
}else{
	$re = unlink($path_from);
	if($re == false){
		echo '<script>alert(\'failed to delete tmp file.\');</script>';
	}
}

//	Destroy image.
imagedestroy($new_img);
imagedestroy($img);

//	output path info for creating preview image.
$imgpath = $this->ConvertURL($path);//ここをフルパスにするか？
$img_id  = pathinfo($path, PATHINFO_FILENAME);

?>
<script type="text/javascript">

//	Reference:
//		http://blog.joyfullife.jp/archives/2007/07/18115458.php
//		http://nui.joyfullife.jp/sui/edit/


//	for preview image.
//	create div for preview img.
div_image = parent.document.createElement('div');

image = parent.document.createElement('img');
image.src = '<?php print ($imgpath);?>';
image.width  = 80;//	width of preview image. Should be the same as that of form.phtml.
image.height = 60;//	height of preview image. Should be the same as that of form.phtml.

div_image.appendChild(image);

//	create a delete button.
span_del_button = parent.document.createElement('span');
span_del_button.className = 'image_del_button';
del_button = parent.document.createElement('a');
del_button.href = "javascript:del_image('<?php print ($img_id);?>')";
del_button.appendChild(parent.document.createTextNode('[x]'));
span_del_button.appendChild(del_button);

//	create div for delete button.
div_catch = parent.document.createElement('div');
div_catch.appendChild(span_del_button);

//	create a wrapping div.
div_all = parent.document.createElement('div');
div_all.className = 'uploaded_image_div';
div_all.id = '<?php print ($img_id);?>';

//	add img and button to the wrapping div.
div_all.appendChild(div_image);
div_all.appendChild(div_catch);

container = parent.document.getElementById('uploaded_image');

container.appendChild(div_all);

parent.document.getElementById('form_coupon_image').reset();


//for hidden input of main form.
input_image = parent.document.createElement('input');
input_image.id = '<?php print ($img_id);?>_image';
input_image.type = 'hidden';
input_image.name = 'image_<?php print ($img_id);?>';
input_image.value = '<?php print ($imgpath);?>';

form = parent.document.getElementsByTagName('form')[0];//formにidを設定できないためタグ名と順序で取得
form.appendChild(input_image);


</script>

</body>
</html>