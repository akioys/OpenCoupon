<?php
/* @var $this CouponApp */

//  Init
$action    = $this->GetBuyAction();
$coupon_id = $this->GetCouponId();
$this->mark("action=$action",'debug');
$this->mark("coupon_id=$coupon_id",'debug');

//  クーポンのrecord
$select = $this->config()->select_coupon_list($coupon_id);
//$record = $this->pdo()->select($select);
//$this->d($coupon_id);
$record = $this->GetTCoupon($coupon_id);

//	ショップのデータを取得
//$this->d($record);
$t_shop = $this->GetTShop($record['shop_id']);
//$this->d($t_shop);

//  templateに渡すdata
$data = new Config();

switch( $action ){
	case 'index':
		//$this->template("index.phtml",array('coupon_list'=>$record, 't_shop'=>$t_shop));
		$this->template("index.phtml",array('t_coupon'=>$record, 't_shop'=>$t_shop));
		break;
		
	default:
		$this->mark("undefined action: $action");
}
