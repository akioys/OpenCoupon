<?php

include('NewWorld5.class.php');

class Coupon extends App
{	
	function Init()
	{
		parent::Init();
	}
	
	function InitAction(){
		return true;
	}
	
	function GetAction(){
		$args = $this->GetArgs();
		$action = isset($args[0]) ? $args[0]: '';
		return $action;
	}
	
	function Action(){
		$action = $this->GetAction();
		switch($action){
			case 'debug':
				$this->doDebug();
				break;
			case 'buy':
				$this->doBuy();
				break;
			default:
				$this->doIndex();
				break;
		}
	}
	
	function doDebug(){
		if($this->admin()){
			include('debug.phtml');
		}
	}
	
	function doIndex(){
		
		$t_coupon = $this->GetCoupon(null);
		$t_shop   = $this->GetTShopByShopId($t_coupon['shop_id']);
		
		$this->d($t_coupon);
		
		include('index.phtml');
	}
	
	function doBuy(){
		include('action.buy.php');
		$buy = new buy($this);
		$buy->action();
	}
	
	/**
	 *  General checker
	 *  汎用的な Checker
	 *
	 *  @return  Boolean
	 */
	function Check($key){
		switch(strtolower($key)){
			
			case 'login':
			case 'loggedin':
				$io = $this->GetSession('isLoggedin');
				break;
				
			case 'shop_flag':
				$io = $this->GetSession('account_id') == 1 ? true: false;
				break;
				
			default:
				$io = false;
		}
		return $io;
	}

	/**
	 *
	 *  ログイン状態を取得
	 *
	 *  @return integer t_account.idを返す
	 */
	function isLoggedin(){
		return $this->GetSession('isLoggedin');
	}

	/**
	 *
	 *  ログインに設定
	 *
	 *  @param  string  $mailaddr  メールアドレス
	 *  @param  string  $password  パスワード
	 *  @return Boolean 成功の場合はt_account.idを返す
	 */
	function Login( $mailaddr=null, $password=null ){
		$this->mark();
		$this->StackLog(__METHOD__,'Coupon');
		
		if(!$this->form){
			$this->StackError("Does not initialized Form object.");
			return false;
		}

		$this->mark();
		$mailaddr = $mailaddr ? $mailaddr: $this->form->GetInputValue('mailaddr');
		$password = $password ? $password: $this->form->GetInputValue('password');
		$this->StackLog("mailaddr=$mailaddr, password=$password",'Coupon');
		
		//	メールアドレスが存在するか
		$select = array();
		$select['table'] = 't_account';
		$select['where']['mailaddr_md5'] = md5($mailaddr);
		$select['limit'] = 1;
		$t_account = $this->mysql->select($select);
		$this->StackLog("id={$t_account['id']}",'Coupon');
		
		$this->mark();
		//	パスワードが一致するかチェック
		if( $t_account['password'] == md5($password) ){
			$this->mark();
			$this->StackLog("password is match!",'Coupon');
			$id = $t_account['id'];
			$this->SetSession('isLoggedin',true);
			$this->SetSession('account_id',$id);
			$this->mark();
		}else{
			$this->mark();
			$this->StackLog("password is unmatch...",'Coupon');
			$id = 0;
		}
		$this->mark("id=$id");

		return $id;
	}

	/**
	 *
	 *  ログアウトに設定
	 *
	 */
	function Logout(){
		$this->SetSession('isLoggedin',0);
		return null;
	}

	function GetMailaddrFromId( $id ){

		//	IDからレコードを取得
		$select = array();
		$select['table'] = 't_account';
		$select['where']['id'] = $id;
		$select['limit'] = 1;
		$t_account = $this->mysql->select($select);
		//		$this->d($t_account);

		$mailaddr = $this->Dec($t_account['mailaddr']);
		//		$this->d($mailaddr);

		return $mailaddr;
	}

	/**
	 *
	 * メールアドレスからIDを求める（存在しない場合は0を返す）
	 *
	 */
	function GetIdFromMailaddr( $mailaddr ){

		//	メールアドレスからレコードを取得
		$select = array();
		$select['table'] = 't_account';
		$select['where']['mailaddr_md5'] = md5($mailaddr);
		$select['limit'] = 1;
		$t_account = $this->mysql->select($select);

		if( count($t_account) ){
			$id = $t_account['id'];
		}else{
			$id = 0;
		}

		return $id;
	}

	//	デフォルト表示のcoupon_id（トップページのオススメクーポンなどに利用）
	function GetDefaultCID(){
		/*
		$database = $this->config()->database();
		$this->d( Toolbox::toArray($database) );
		
		//  PDOの取得
		$pdo = $this->pdo();
		$io = $pdo->Connect($database);
		*/
		
		//$record = $this->PDO()->Quick(' t_test.id = 1 ');
		//$this->d($record);
		
		/*
		$select['table'] = 't_coupon';
		$select['where']['is_delete'] = null;
		$select['order'][] = 'coupon_sales_limit desc';
		$select['limit'] = 1;
		$select['cache'] = 1;
		$t_coupon = $this->mysql->select($select);
		*/
		
		//  SELECTの定義を作成
		$config = $this->config()->select_coupon_default();
		//$this->d( Toolbox::toArray($config));
		
		/*
		$config->table = 't_coupon';
		$config->where->is_delete = 'null';
		$config->limit = 3;

		$config->order = 'timestamp desc';
		*/
		
		//  SELECTを実行
		$t_coupon = $this->pdo()->select($config);
		//$this->d($this->pdo()->qu());

		//$this->d($t_coupon);
		
		//$this->d($t_coupon);
		//$this->mark($this->mysql->qu());
		
		//return isset($t_coupon['coupon_id']) ? $t_coupon['coupon_id']: 1;
		
		return $t_coupon['coupon_id'];
	}

	/**
	 * t_couponからcoupon_idのレコードを取得
	 *
	 * @param  integer  coupon_id
	 * @return mixed    成功=record
	 */
	function GetCoupon( $coupon_id=null )
	{
		if(!$coupon_id){
			$coupon_id = $this->GetDefaultCID();
		}

		//	クーポン情報を取得
		/*
		$select = array();
		$select['table'] = 't_coupon';
		$select['where']['coupon_id'] = $coupon_id;
		$select['limit'] = 1;
		$t_coupon = $this->mysql->select($select);
		*/
		//$this->mark($coupon_id);
		//$this->d($t_coupon);
		
		//  PDOの取得
		/*
		$database = $this->config()->database();
		$pdo = $this->pdo();
		$io = $pdo->Connect($database);
		var_dump($io);
		*/
		
		//  SELECTの定義を作成
		$config = new Config();
		$config->table = 't_coupon';
		$config->where->coupon_id = $coupon_id;
		$config->limit = 1;
		
		//  SELECTを実行
		$record = $this->pdo()->select($config);
		$this->d($record);
		
		$t_coupon = $pdo->select($config);
		$this->d($coupon_id);
		$this->d($t_coupon);
		
		//	クーポンの販売枚数を取得
		/*
		$select = array();
		$select['table'] = 't_buy';
		$select['where']['coupon_id'] = $coupon_id;
		$select['as']['sum'] = 'sum(num)';
		$select['group'] = 'coupon_id';
		$select['limit'] = 1;
		$t_buy = $this->mysql->select($select);
		*/
		//  SELECTの定義を作成
		$config = new Config();
		$config->table = 't_buy';
		$config->where->coupon_id = $coupon_id;
		$config->agg->sum = 'coupon_id';
		$config->group = 'coupon_id';
		$config->limit = 1;
		
		//  SELECTを実行
		$t_buy = $pdo->select($config);
		$t_buy = $this->pdo()->select($config);
		$this->d($t_buy);
		
		if(!count($t_buy)){
			return false;
		}
		
		//	販売枚数
		$t_coupon['coupon_sales_num_sum'] = $t_buy['SUM(coupon_id)'];

		//	割引額
		$t_coupon['coupon_discount_price'] = $t_coupon['coupon_normal_price'] - $t_coupon['coupon_sales_price'];

		//	割引率(%)
		$t_coupon['coupon_discount_rate'] = 100 - (($t_coupon['coupon_sales_price'] / $t_coupon['coupon_normal_price']) * 100);

		//	残り時間を計算
		$rest_time = strtotime($t_coupon['coupon_sales_limit']) - time();

		//	レコードに残り時間を追加
		$t_coupon['rest_time_day']    = floor($rest_time / (86400));
		$t_coupon['rest_time_hour']   = floor(($rest_time - ($t_coupon['rest_time_day']*86400)) / 3600);
		$t_coupon['rest_time_minute'] = date("i",$rest_time);
		$t_coupon['rest_time_second'] = date("s",$rest_time);

		return $t_coupon;
	}
	
	function GetTShopByShopId($shop_id)
	{	
		/*
		$select = array();
		$select['table'] = 't_shop';
		$select['where']['shop_id'] = $shop_id;
		$select['limit'] = 1;
		$t_shop = $this->mysql->select($select);
		*/
				
		//  SELECTの定義を作成
		$config = new Config();
		$config->table = 't_shop';
		$config->where->shop_id = $shop_id;
		$config->limit = 1;
		
		//  SELECTを実行
		$t_shop = $this->pdo()->select($config);
		$this->d($t_shop);
		
		return $t_shop;
	}

	/**
	 * これは使ってる？
	 * @param $sid
	 */
	function _GetShopTable($sid){

		$select = array();
		$select['table'] = 't_shop';
		$select['where']['shop_id'] = $sid;
		$select['limit'] = 1;
		$t_shop = $this->mysql->select($select);

		$s_name				 = $t_shop['shop_name'];
		$s_desc				 = $t_shop['shop_description'];
		$s_address			 = $t_shop['shop_address'];
		$s_telephone		 = $t_shop['shop_telephone'];
		$s_holiday			 = $t_shop['shop_holiday'];
		$s_opening_hour		 = $t_shop['shop_opening_hour'];
		$s_nearest_station	 = $t_shop['shop_nearest_station'];

		return $t_shop;
	}

	/**
	 * 新規アカウントをt_accountに登録
	 *
	 * @param  string   mailaddress
	 * @param  string   password
	 * @return integer  account_id
	 */
	function AccountRegister( $mail, $password ){

		$mailaddr = $this->enc($mail);
		$mailaddr_md5 = md5($mail);
		$password_md5 = md5($password);

		//	既に登録済みかチェック
		if( $id = $this->GetIdFromMailaddr($mail) ){
			//	既にデータベースに登録済み
		}else{
			//	データベースに登録
			$insert = array();
			$insert['table'] = 't_account';
			$insert['set']['mailaddr']		 = $mailaddr; // $this->form->getInputValue('mailaddr');
			$insert['set']['mailaddr_md5']	 = $mailaddr_md5; // md5($this->form->getInputValue('mailaddr'));
			$insert['set']['password']		 = $password_md5; // md5($this->form->getInputValue('password'));
			$id = $this->mysql->insert($insert);
		}
	  
		return $id;
	}

	/**
	 * account_idからcustomer_idを求める
	 *
	 * @param   integer  t_account.account_id
	 * @return  integer  t_customer.customer_id
	 */
	function GetCustomerIdByAccountId($account_id){
		$select['table'] = 't_customer';
		$select['where']['account_id'] = $account_id;
		$select['limit'] = 1;
		$select['as'][] = 'customer_id';
		$temp = $this->mysql->select($select);

		return $temp['customer_id'];
	}

	/**
	 * customer_idからt_addressに次に登録するseq_noを求める
	 *
	 * @param   integer  t_customer.customer_id
	 * @return  integer  t_address.seq_no
	 */
	function GetNextSeqNoOfTAddressByCustomerId($customer_id){
		$select['table'] = 't_address';
		$select['as']['max'] = 'max(seq_no)';
		$select['where']['customer_id'] = $customer_id;
		$select['limit'] = 1;
		$temp = $this->mysql->select($select);

		return $temp['max'] +1;
	}

	function GetTCustomerByAccountId( $account_id ){

		$select['table'] = 't_customer';
		$select['where']['account_id'] = $account_id;
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		return $t_temp;
	}

	function GetTAddressByAccountId( $account_id, $seq_no ){
		$customer_id = $this->GetCustomerIdByAccountId($account_id);
		$t_temp = $this->GetTAddressByCustomerId( $customer_id, $seq_no );

		return $t_temp;
	}

	function GetTAddressByCustomerId( $customer_id, $seq_no ){
		$select['table'] = 't_address';
		$select['where']['customer_id'] = $customer_id;
		$select['where']['seq_no'] = $seq_no;
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		return $t_temp;
	}

	function GetAddressSeqNoByAccountId( $account_id ){
		$costomer_id = $this->GetCustomerIdByAccountId( $account_id );
		return $this->GetAddressSeqNoByCostomerId($costomer_id);
	}

	function GetAddressSeqNoByCostomerId( $costomer_id ){
		$select['table'] = 't_customer';
		$select['where']['customer_id'] = $costomer_id;
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		return $t_temp['address_seq_no'];
	}

	//	販売済みのクーポンの枚数を取得
	function GetNumberOfSoldTheCouponStock($stock_id){

		$select = array();
		$select['table'] = 't_passport_sales';
		$select['where']['stock_id'] = $stock_id;
		$count = $this->mysql->count($select);

		return $count;
	}

	//	クーポンが販売中か取得
	function isCouponForSale($stock_id){

		$select = array();
		$select['table'] = 't_passport_stock';
		$select['where']['stock_id'] = $stock_id;
		$select['limit'] = 1;
		$t_stock = $this->mysql->select($select);

		$count = $this->GetNumberOfSoldThePassportStock($stock_id);

		return $count < $t_stock['limit'] ? true: false;
	}

	/**
	 * $coupon_idのクーポン名
	 *
	 * @param  integer  t_coupon.coupon_id
	 * @return integer  クーポン名
	 */
	function GetCouponName( $coupon_id ){
		$select = array();
		$select['table'] = 't_coupon';
		$select['where']['coupon_id'] = $coupon_id;
		$select['as'][] = 'coupon_title';
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		if( 0 ){
			$this->d($t_temp);
			$this->d($this->mysql->qu());
			$this->d($select);
		}

		return $t_temp['coupon_title'];
	}

	/**
	 * $coupon_idのクーポンの販売価格
	 *
	 * @param  integer  t_coupon.coupon_id
	 * @return integer  クーポンの販売価格
	 */
	function GetCouponPrice( $coupon_id ){
		$select = array();
		$select['table'] = 't_coupon';
		$select['where']['coupon_id'] = $coupon_id;
		$select['as'][] = 'coupon_sales_price';
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		if( 0 ){
			$this->d($t_temp);
			$this->d($this->mysql->qu());
			$this->d($select);
		}

		return $t_temp['coupon_sales_price'];
	}

	/**
	 * $coupon_idの販売済みの枚数を取得
	 *
	 * @param  integer  t_coupon.coupon_id
	 * @return integer  販売枚数
	 */
	function GetCouponSoldNum( $coupon_id ){
		$select = array();
		$select['table'] = 't_buy';
		$select['where']['coupon_id'] = $coupon_id;
		$select['as']['sum'] = 'sum(num)';
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		if( 0 ){
			$this->d($t_temp);
			$this->d($this->mysql->qu());
			$this->d($select);
		}

		return (int)$t_temp['sum'];
	}

	/**
	 * $coupon_idの販売可能な上限の枚数を取得
	 *
	 * @param  integer  t_coupon.coupon_id
	 * @return integer  販売可能下限枚数
	 */
	function GetCouponStockTop( $coupon_id ){
		$select = array();
		$select['table'] = 't_coupon';
		$select['where']['coupon_id'] = $coupon_id;
		$select['as'][] = 'coupon_sales_num_bottom';
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		if( 1 ){
			$this->d($t_temp);
			$this->d($this->mysql->qu());
			$this->d($select);
		}

		return (int)$t_temp['coupon_sales_num_bottom'];
	}

	function GetCouponSerialNo( $args ){
//		$this->d($args);

		/*
		 $seler_id	 = sprintf("%02d",1);
		 $stock_id	 = sprintf("%02d",$stock_id);
		 $seq_no		 = sprintf("%02d",$count +1);
		 $serial_no	 = sprintf('1-%s-%s-%s', $seler_id, $stock_id, $seq_no);
		 */

		$account_id = $args['user_id'];
		$coupon_id  = $args['item_id'];

		$select = array();
		$select['table'] = 't_coupon';
		$select['as'][] = 'shop_id';
		$select['where']['coupon_id'] = $coupon_id;
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);
		$seler_id = $t_temp['shop_id'];

		if( 0 ){
			$this->d($t_temp);
			$this->d($this->mysql->qu());
			$this->d($select);
		}

		$select = array();
		$select['table'] = 't_buy';
		$select['where']['account_id']	 = $account_id;
		$select['where']['coupon_id']	 = $coupon_id;
		$seq_no = $this->mysql->count($select);

		if( 0 ){
			$this->d($seq_no);
			$this->d($this->mysql->qu());
			$this->d($select);
		}

		$serial_no	 = sprintf('%03d-%03d-%03d-%s', $seler_id, $coupon_id, $account_id, $seq_no+1 );
		$this->mark("serial_no = $serial_no");

		return $serial_no;
	}

	function CreateSID(){

		do{
			$sid = date('m_d_His_').rand(100,999);
			//	SIDの重複チェック
			$select = array();
			$select['table'] = 't_buy';
			$select['where']['SID'] = $sid;
			$count = $this->mysql->count($select);
			if( 0 ){
				$this->mark($count);
				$this->mark($this->mysql->qu());
				$this->d($select);
			}
				
		}while($count);

		$this->SetSession('sid',$sid);

		return $sid;
	}

	function GetSID(){
		return $this->GetSession('sid');
	}

	function CreateIP_USER_ID( $account_id, $op_uniq_id, $mailaddr_md5, $card_no, $card_exp ){

		if( strlen($card_no) != 4 ){
			$this->StackError("String length is not 4 characters.($card_no)");
			return false;
		}
		if( strlen($card_exp) != 4 ){
			$this->StackError("String length is not 4 characters.($card_exp)");
			return false;
		}

		$select = array();
		$select['table'] = 'dc_ip_user_id';
		$select['where']['account_id']	 = $account_id;
		$select['where']['op_uniq_id']	 = $op_uniq_id;
		$select['where']['mailaddr_md5'] = $mailaddr_md5;
		$select['where']['card_number']	 = $card_no;
		$select['where']['card_expire']	 = $card_exp;
		$select['as'][] = 'ip_user_id';
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);

		if( 0 ){
			$this->d($this->mysql->qu());
			$this->d($t_temp);
			$this->d($select);
		}

		if( $t_temp['ip_user_id'] ){
			$ip_user_id = $t_temp['ip_user_id'];
		}else{
			$insert = array();
			$insert['table'] = 'dc_ip_user_id';
			$insert['set']['account_id']	 = $account_id;
			$insert['set']['op_uniq_id']	 = $op_uniq_id;
			$insert['set']['mailaddr_md5']	 = $mailaddr_md5;
			$insert['set']['card_number']	 = $card_no;
			$insert['set']['card_expire']	 = $card_exp;
			$ip_user_id = $this->mysql->insert($insert);
		}

		if( 0 ){
			$this->mark($this->mysql->qu());
			$this->d($insert);
			$this->mark($ip_user_id);
		}

		return 'coupon_'.$ip_user_id;
	}

	function GetIP_USER_ID( $user_id, $card_no, $card_exp ){

		$account_id = $user_id;
		$op_uniq_id = $this->GetEnv('op-uniq_id');
		$mailaddr   = $this->GetMailaddrFromId($account_id);
		$mailaddr_md5 = md5($mailaddr);

		$card_no = substr( $card_no, -4, 4 );
		if( strlen($card_no) != 4 ){
			$this->StackError("String length is not 4 characters.(card_no=$card_no)");
			return false;
		}
		if( strlen($card_exp) != 4 ){
			$this->StackError("String length is not 4 characters.(card_exp=$card_exp)");
			return false;
		}

		$select = array();
		$select['table'] = 't_customer';
		$select['where']['account_id'] = $account_id;
		$select['limit'] = 1;
		$select['as'][] = 'IP_USER_ID';
		$t_temp = $this->mysql->select($select);
		$ip_user_id = $t_temp['IP_USER_ID'];

		if( $ip_user_id ){
			//	exists
		}else{
			//	empty
			$ip_user_id = $this->CreateIP_USER_ID( $account_id, $op_uniq_id, $mailaddr_md5, $card_no, $card_exp );
		}

		if( 0 ){
			$this->mark($this->mysql->qu());
			$this->d($select);
			$this->d($t_temp);
			$this->mark($ip_user_id);
		}

		return $ip_user_id;
	}

	function SetIP_USER_ID( $user_id, $ip_user_id ){

		if( empty($user_id) ){
			throw new Exception('empty user_id, PATH='.__FILE__.': '.__LINE__);
		}

		if( empty($ip_user_id) ){
			throw new Exception('empty ip_user_id, PATH='.__FILE__.': '.__LINE__);
		}
		
		$select = array();
		$select['table'] = 't_customer';
		$select['where']['account_id'] = $user_id;
		$select['limit'] = 1;
		$t_temp = $this->mysql->select($select);
		if( $t_temp['IP_USER_ID'] ){
			// すでに登録済み
			return true;
		}
		
		$update = array();
		$update['table'] = 't_customer';
		$update['where']['account_id'] = $user_id;
		$update['set']['IP_USER_ID']   = $ip_user_id;
		$num = $this->mysql->update($update);
		
		// 直近のSQL文がエラーだったかチェック
		if(!$this->mysql->io){
			throw new Exception('MySQL UPDATE Failed. SQL='.$this->mysql->qu().', PATH='.__FILE__.': '.__LINE__);
		}

		return true;
	}

	/**
	 * 売上テーブル（t_buy）に記録（トランザクション中）
	 *
	 *
	 *
	 */
	function InsertBuy( $user_id, $item_id, $item_num, $sid ){

		if( empty($user_id) ){
			throw new Exception('empty user_id, PATH='.__FILE__.': '.__LINE__);
		}

		if( empty($item_id) ){
			throw new Exception('empty item_id, PATH='.__FILE__.': '.__LINE__);
		}

		if( empty($item_num) ){
			throw new Exception('empty item_num, PATH='.__FILE__.': '.__LINE__);
		}

		if( empty($sid) ){
			throw new Exception('empty sid, PATH='.__FILE__.': '.__LINE__);
		}

		$insert = array();
		$insert['table'] = 't_buy';
		$insert['set']['account_id']	 = $user_id;
		$insert['set']['coupon_id']		 = $item_id;
		$insert['set']['num']			 = $item_num;
		$insert['set']['settle_flag']	 = 51; // 51=クレジットカード, 
		$insert['set']['SID']			 = $sid;
		$buy_id = $this->mysql->insert($insert);

		if(!$buy_id){
			throw new Exception('MySQL INSERT Failed. SQL='.$this->mysql->qu().', PATH='.__FILE__.': '.__LINE__);
		}

		return true;
	}

	function DC_Authority( $args, &$error ){

		include_once('modules/DigitalCheck.mod.php');
		$dc = new DigitalCheck($this);

		$args['SID']	 = $this->CreateSID();
		$args['KAKUTEI'] = 0;
		$args['STORE']	 = 51;

		$io = $dc->Settlement( $args, $error );
		$this->mark("$io, $error");

		return $io;
	}

	function DC_Decision( $args, &$error ){

		include_once('modules/DigitalCheck.mod.php');
		$dc = new DigitalCheck($this);

		$args['SID'] = $this->GetSID();

		$io = $dc->Decision( $args, $error );

		return $io;
	}

	function DC_Cancel( $args, &$error ){

		include_once('modules/DigitalCheck.mod.php');
		$dc = new DigitalCheck($this);

		$args['SID']	 = $this->GetSID();

		$io = $dc->Cancel( $args, $error, false );

		return $io;
	}
}
