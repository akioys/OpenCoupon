<?php
/* @var $this CouponApp */

//  My shop ID.
$shop_id = $this->GetShopID();

//  Form
$form_config = $this->config()->form_coupon( $shop_id );
$this->form()->AddForm( $form_config );
$form_name = $form_config->name;
//$this->form()->Clear($form_name);

//  Action
$action = $this->GetAction();

//	data
$data = new Config();
$data->template = 'form.phtml';

switch( $action ){
	case 'index':
		$data->template = 'form.phtml';
		break;

	case 'confirm':
		if(!$this->form()->Secure('form_coupon') ){
			$data->message  = '入力内容を確かめて下さい。';
			$data->template = 'form.phtml';
		}else{
			$data->template = 'confirm.phtml';
			
			// for test
			$tmp_img = $this->form()->GetInputValue('coupon_image',$form_name);
			$this->d($tmp_img);
			$tmp_img = $this->ConvertPath($tmp_img);
			$this->d($tmp_img);
			
			//	set path of temp image into SESSION
			$this->SetSession('image_path', $tmp_img);
		}
		break;
	
	case 'commit':
		if( $this->form()->Secure('form_coupon') ){
				
			// for test
			//$tmp_img = $this->form()->GetInputValueRawAll($form_name);
			//$this->d($tmp_img);
			//$this->d($_SESSION['image_path']);
			/*
			$path = $this->form()->GetInputValue('coupon_image','form_coupon');
			$this->d($path);
			var_dump($path);
			$path = $this->form()->GetInputValueRaw('coupon_image','form_coupon');
			$this->d($path);
			$path = $this->form()->GetInputValueRawAll('form_coupon');
			$this->d($path);
			*/

			$this->d($_POST);
			
			//	Get temp image file name
			$tmp_img = $this->GetSession('image_path');
			$this->d($tmp_img);
			//$tmp_img = $this->ConvertPath($tmp_img);
			//$this->d($tmp_img);//for test
			
			
			//  Do Insert
			$config = $this->config()->insert_coupon($shop_id);
			$coupon_id = $this->pdo()->insert($config);
			
			//  View result
			if( $coupon_id === false ){
				$data->message = 'Couponレコードの作成に失敗しました。';
				
				//	clear temp image path in SESSION
				$this->SetSession('image_path', '');
			}else{
				$this->form()->Clear($form_name);
				//$this->Location("app://myshop/coupon/edit/$coupon_id");
				
				//	glob()使ってフォルダの有無チェック。
				$path     = "app://shop/$shop_id/$coupon_id";
				$path     = $this->ConvertPath($path);
				
				$res      = glob($path);
				//$num      = 1;
				$this->d($path);
				$this->d($res);//for test
				
				if( !$res or $res == false or $res =='' ){
					mkdir($path, 0777, true);
					$num = 1;
				}else{
					$re  = pathinfo($path);//ここの処理方法はかなりあやしいので要修正。
					$num = $re[0] + 1;//これもかなりあやしいので要修正。
				}
				
				//	フォルダ内にファイルがあるかチェック。
				
				//	renamte()使ってファイルを移動。
				$ext      = pathinfo($tmp_img, PATHINFO_EXTENSION);
				$new_name = $path."/coupon".$coupon_id."-".$num.".".$ext;
				$this->d($new_name);
				
				
				//	ファイルを移動。
				if( rename($tmp_img, $new_name) == true){
					echo 'successfully moved.';
				}else{
					echo 'move failed.';//for test
				}
				
				
				//	完了メッセージ表示。
				
				
				/*
				//	generate file path
				$ext = pathinfo($tmp_img,PATHINFO_EXTENSION);
				
				//$shop_folder = "app://shop/$shop_id/$coupon_id/";//これだとcoupon_idの値を取ってきてしまう。しかもまだフォルダが作られてない。
				//$shop_folder = $this->ConvertURL($shop_folder);
				$shop_folder = "/shop/$shop_id/$coupon_id/";
				mkdir($shop_folder, 0777);
				$this->d($shop_folder);
				
				$basename = pathinfo($shop_folder,PATHINFO_BASENAME);
				//$this->d($basename);
				
				if( !$basename or $basename == null or $basename =='' ){
					$basename = 1;
				}else{
					$basename = $basename+1;
				}
				
				$new_file =$basename.".".$ext;
				//$new_path = "app://shop/$shop_id/$coupon_id/$new_file";
				//$new_path = $this->ConvertURL($new_path);
				$new_path = "/shop/$shop_id/$coupon_id/$new_file";
				$this->d($new_path);
				$io = rename($tmp_img, $new_path);
				*/
				
				$data->template = 'commit.phtml';//for test
			}
		}
		break;
	default:
}

include('index.phtml');
