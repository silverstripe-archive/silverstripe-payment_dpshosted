<?php
/**
 * @package payment_dpshosted
 */
class DPSHostedPayment extends DataObject{
	
	static $pxAccess_Url = "https://www.paymentexpress.com/pxpay/pxpay.aspx";
	
	private static $pxAccess_Userid;
	
	private static $pxAccess_Key;
	
	private static $mac_Key;
	
	static static $pxPay_Url  = "https://www.paymentexpress.com/pxpay/pxaccess.aspx";
  	
	private static $pxPay_Userid;
  	
	private static $pxPay_Key;
	
	static $db = array(
		'Status' => "Enum('Incomplete,Success,Failure,Pending','Incomplete')",
		'Amount' => 'Currency',
		'Currency' => 'Varchar(3)',
		'TxnRef' => 'Varchar',
		'Message' => 'Varchar',
		'IP' => 'Varchar',
		'ProxyIP' => 'Varchar',
		'AuthorizationCode' => 'Varchar'
	);
	
	static $has_one = array(
	);
	
	static function set_px_access_userid($id){
		self::$pxAccess_Userid = $id;
	}
	
	static function get_px_access_userid(){
		return self::$pxAccess_Userid;
	}
	
	static function set_px_access_key($key){
		self::$pxAccess_Key = $key;
	}
	
	static function get_px_access_key(){
		return self::$pxAccess_Key;
	}
	
	static function set_mac_key($key){
		self::$mac_Key = $key;
	}
	
	static function get_mac_key(){
		return self::$mac_Key;
	}
	
	static function set_px_pay_userid($id){
		self::$pxPay_Userid = $id;
	}
	
	static function get_px_pay_userid(){
		return self::$pxPay_Userid;
	}
	
	static function set_px_pay_key($key){
		self::$pxPay_Key = $key;
	}
	
	static function get_px_pay_key(){
		return self::$pxPay_Key;
	}
	
	function processPayment($data, $form){
		$request = new PxPayRequest();
		$this->extend('prepareRequest', $request);
		/*
		$pxaccess = new PxAccess(self::$pxAccess_Url, self::$pxAccess_Userid, self::$pxAccess_Key, self::$mac_Key);
		$request_string = $pxaccess->makeRequest($request);
		*/
	
		$pxpay = new PxPay( self::$pxPay_Url, self::$pxPay_Userid, self::$pxPay_Key );
		$request_string = $pxpay->makeRequest($request);
		
		$response = new MifMessage( $request_string );
		$url = $response->get_element_text("URI");
		$valid = $response->get_attribute("valid");
		  #echo "request_string:".$request_string;
		  # exit;
		header("Location: ".$url);
		die;
	}
	
	static function processResponse(){
		if(preg_match('/^PXHOST/i', $_SERVER['HTTP_USER_AGENT'])){
			$dpsDirectlyConnecting = 1;
		}

		//$pxaccess = new PxAccess($PxAccess_Url, $PxAccess_Userid, $PxAccess_Key, $Mac_Key);

		$pxpay = new PxPay( DPSHostedPayment::$pxPay_Url, DPSHostedPayment::get_px_pay_userid(), DPSHostedPayment::get_px_pay_key());

		$enc_hex = $_REQUEST["result"];

		$rsp = $pxpay->getResponse($enc_hex);

		if(isset($dpsDirectlyConnecting)&&$dpsDirectlyConnecting) {
			$success = $rsp->getSuccess();   # =1 when request succeeds

			if($success =='1') {
				echo "success";
			} else {
				echo "failure";
			}

		// Human visitor
		}else{
			$paymentID = $rsp->getTxnId();

			$payment = DataObject::get_by_id('DPSHostedPayment', $paymentID);
		//Tomove	$donation = $payment->Donation();

			$success = $rsp->getSuccess();
			if($success =='1'){
				$payment->TxnRef=$rsp->getDpsTxnRef();
				$payment->Status="Success";
				$payment->AuthorizationCode=$rsp->getAuthCode();

			}else{
				$payment->Message=$rsp->getResponseText();
				$payment->Status="Failure";
			}
			$payment->write();
			return $payment;
		}
	}
	
	/**
	 * Set the IP address and Proxy IP (if available) from the site visitor.
	 * Does an ok job of proxy detection. Probably can't be too much better because anonymous proxies
	 * will make themselves invisible.
	 */	
	function setClientIP() {
		if(isset($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
		else $ip = null;
		
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$proxy = $ip;
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		
		// If the IP and/or Proxy IP have already been set, we want to be sure we don't set it again.
		if(!$this->IP) $this->IP = $ip;
		if(!$this->ProxyIP && isset($proxy)) $this->ProxyIP = $proxy;
	}
}