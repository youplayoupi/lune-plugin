<?php namespace Youpiyoupla\Ccy\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Apis extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController','Backend\Behaviors\FormController'    ];
    CONST HITBTC_API_URL = 'http://demo-api.hitbtc.com';
    CONST HITBTC_TRADING_API_URL_SEGMENT = '/api/2/trading/';
    private $_key, $_secret, $_pass;
    private $_availableMethods = array(
        'balance',
        'orders/active',
        'new_order',
        'cancel_order',
        'trades',
        'orders/recent',
    );
    private $_postMethods = array(
        'new_order',
        'cancel_order'
    );
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct($api_key = '', $pass = '', $api_secret = '') {
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
		$this->_pass = $pass;
        $this->_nonce =  time()*1E3;
    }
    public function __call($name, $arguments) {
        $methodPathParts = preg_split('/(?=[A-Z])/', $name);
        $methodPathParts = array_map(
            function($pathSegment) { return strtolower($pathSegment); },
            $methodPathParts
        );
        $method = implode('/', $methodPathParts);
        if(!in_array($method, $this->_availableMethods)){
            throw new \Exception( 'Method that you try to call doesn\'t exists!' );
        }
        return $this->_request($method, $arguments, in_array($method, $this->_postMethods));
    }
    private function _request($method, $arguments, $isPost = FALSE)
    {
        $requestUri = self::HITBTC_TRADING_API_URL_SEGMENT
            . $method
            . '?nonce=' . $this->_getNonce()
            . '&apikey=' . $this->_key;
        $arguments = sizeof($arguments) > 0 ? $arguments[0] : array();
        $params = http_build_query($arguments);
        if (strlen($params) && $isPost === FALSE) {
            $requestUri .= '&' . $params;
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
                CURLOPT_URL => self::HITBTC_API_URL . $requestUri,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => 1
            ));
        if($isPost) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Signature: ' . $this->_signature($requestUri, $isPost ? $params : '')));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    private function _signature($uri, $postData)
    {
        return strtolower(hash_hmac('sha512', $uri . $postData, $this->_secret));
    }
    private function _getNonce()
    {
        return $this->_nonce++;
    }
    
    public function hitbtc_api_request($url, $params = [], $method = "POST") {
		if ( empty($this->api_key) ) die("signedRequest error: API Key not set!");
		if ( empty($this->api_secret) ) die("signedRequest error: API Secret not set!");
		//$base = $this->base;
		$opt = [
			"http" => [
				"method" => $method,
				"ignore_errors" => true,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\n"
			]
		];
		$context = stream_context_create($opt);
		$params['timestamp'] = number_format(microtime(true)*1000,0,'.','');

		$query = http_build_query($params, '', '&');
		$signature = hash_hmac('sha256', $query, $this->api_secret);
		$endpoint = $url.'?'.$query.'&signature='.$signature;
		$data = file_get_contents($endpoint, false, $context);
		return json_decode($data, true);
	}
    
    public function hitbtc_api_createorder($trade) {
		$url = "https://api.hitbtc.com/api/2/public/orderbook/".$trade."?limit=10";
		
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public function hitbtc_api_openorder($trade) {
		$url = "https://api.hitbtc.com/api/2/public/orderbook/".$trade."?limit=10";
		static $ch = null;
		$res = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
			$ch = null;
		}
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public function hitbtc_api_symbol() {
		$url = "https://api.hitbtc.com/api/2/public/symbol";
		static $ch = null;
		$res = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
			$ch = null;
		}
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public static function coinmarketcap_api_query() {
		$url = "https://api.coinmarketcap.com/v1/ticker/?limit=0";
		static $ch = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
		}
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public static function coinmarketcap_api_query_ccy($ccy) {
		$url = "https://api.coinmarketcap.com/v1/ticker/".$ccy."/?limit=0";
		static $ch = null;
		$res = "";
		var_dump($ccy);

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
		}
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public static function cryptocompare_coinList() {
		$url = "https://www.cryptocompare.com/api/data/coinlist/";
		static $ch = null;
		$res = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
			$ch = null;
		}
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public static function cryptocompare_api_query($ccy, $time) {
		$url = "https://min-api.cryptocompare.com/data/pricehistorical?fsym=".$ccy."&tsyms=USD,EUR&ts=".$time;
		static $ch = null;
		$res = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
			$ch = null;
		}
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public static function cryptocompare_api_limit() {
		$url = "https://min-api.cryptocompare.com/stats/rate/hour/limit";
		static $ch = null;
		$res = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
			$ch = null;
		}
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    //duplicate function due to strange api behaviour returning null if 2 request too close in time
    public static function cryptocompare_api_query2($ccy, $time) {
		$url = "https://min-api.cryptocompare.com/data/pricehistorical?fsym=".$ccy."&tsyms=USD,EUR&ts=".$time;
		static $ch = null;
		$res = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);

			$res = curl_exec($ch);
			$ch = null;
		}
		//if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
    
    public static function cryptopia_api_query($method, array $req = array()) {
		$API_KEY = '';
		$API_SECRET = '';
		$public_set = array( "GetCurrencies", "GetTradePairs", "GetMarkets", "GetMarket", "GetMarketHistory", "GetMarketOrders", "GetMarketOrderGroups" );
		$private_set = array( "GetBalance", "GetDepositAddress", "GetOpenOrders", "GetTradeHistory", "GetTransactions", "SubmitTrade", "CancelTrade", "SubmitTip" );
		static $ch = null;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Cryptopia.co.nz API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
		if ( in_array( $method ,$public_set ) ) {
		$url = "https://www.cryptopia.co.nz/api/" . $method;
		if ($req) { foreach ($req as $r ) { $url = $url . '/' . $r; } }
		curl_setopt($ch, CURLOPT_URL, $url );
		} elseif ( in_array( $method, $private_set ) ) {
		$url = "https://www.cryptopia.co.nz/Api/" . $method;
		$nonce = explode(' ', microtime())[1];
		$post_data = json_encode( $req );
		$m = md5( $post_data, true );
		$requestContentBase64String = base64_encode( $m );
		$signature = $API_KEY . "POST" . strtolower( urlencode( $url ) ) . $nonce . $requestContentBase64String;
		$hmacsignature = base64_encode( hash_hmac("sha256", $signature, base64_decode( $API_SECRET ), true ) );
		$header_value = "amx " . $API_KEY . ":" . $hmacsignature . ":" . $nonce;
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: $header_value");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $req ) );
		}
		// run the query
		$res = curl_exec($ch);
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
	
	public function cryptopia_api_query_obj($method, array $req = array()) {
		$API_KEY = $this->api_key;
		$API_SECRET = $this->api_secret;
		$public_set = array( "GetCurrencies", "GetTradePairs", "GetMarkets", "GetMarket", "GetMarketHistory", "GetMarketOrders", "GetMarketOrderGroups" );
		$private_set = array( "GetBalance", "GetDepositAddress", "GetOpenOrders", "GetTradeHistory", "GetTransactions", "SubmitTrade", "CancelTrade", "SubmitTip" );
		static $ch = null;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Cryptopia.co.nz API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
		if ( in_array( $method ,$public_set ) ) {
		$url = "https://www.cryptopia.co.nz/api/" . $method;
		if ($req) { foreach ($req as $r ) { $url = $url . '/' . $r; } }
		curl_setopt($ch, CURLOPT_URL, $url );
		} elseif ( in_array( $method, $private_set ) ) {
		$url = "https://www.cryptopia.co.nz/Api/" . $method;
		$nonce = explode(' ', microtime())[1];
		$post_data = json_encode( $req );
		$m = md5( $post_data, true );
		$requestContentBase64String = base64_encode( $m );
		$signature = $API_KEY . "POST" . strtolower( urlencode( $url ) ) . $nonce . $requestContentBase64String;
		$hmacsignature = base64_encode( hash_hmac("sha256", $signature, base64_decode( $API_SECRET ), true ) );
		$header_value = "amx " . $API_KEY . ":" . $hmacsignature . ":" . $nonce;
		$headers = array("Content-Type: application/json; charset=utf-8", "Authorization: $header_value");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $req ) );
		}
		// run the query
		$res = curl_exec($ch);
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
	
	public function coinbase_api_query($method)
	{
		$key = $this->api_key;
		$secret = $this->api_secret;

		$time = time();
		$url = "https://api.coinbase.com/v2/".$method;

		$data = $time."GET"."/".$method;

		$sign = base64_encode(hash_hmac("sha256", $data, base64_decode($secret), true));

		$headers = array(                
			'CB-ACCESS-KEY: '.$key,
			'CB-ACCESS-SIGN: '.$sign,
			'CB-ACCESS-TIMESTAMP: '.$time,
			'Content-Type: application/json'
		);

		static $ch = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$res = curl_exec($ch);
		}
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
	
	public function gdax_api_query($method)
	{
		$key = $this->api_key;
		$secret = $this->api_secret;
		$passphrase = $this->_pass;

		$time = time();
		$url = "https://api.gdax.com/".$method;

		$data = $time."GET"."/".$method;

		$sign = base64_encode(hash_hmac("sha256", $data, base64_decode($secret), true));

		$headers = array(                
			'CB-ACCESS-KEY: '.$key,
			'CB-ACCESS-SIGN: '.$sign,
			'CB-ACCESS-TIMESTAMP: '.$time,
			'CB-ACCESS-PASSPHRASE: '.$passphrase,
			'Content-Type: application/json'
		);

		static $ch = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$res = curl_exec($ch);
		}
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		return $jsonr;
	}
	
	public function gdax_api_query_nokey($method)
	{
		$res = "";

		$time = time();
		$url = "https://api.gdax.com/".$method;
		$data = $time."GET"."/".$method;
		//$sign = base64_encode(hash_hmac("sha256", $data, base64_decode($secret), true));

		$headers = array(
			'CB-ACCESS-TIMESTAMP: '.$time,
			'Content-Type: application/json'
		);

		static $ch = null;

		if (is_null($ch)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

			$res = curl_exec($ch);
		}
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$jsonr = json_decode($res, true);
		$ch = null;
		return $jsonr;
	}
	
		protected $base = "https://api.binance.com/api/", $wapi = "https://api.binance.com/wapi/", $api_key, $api_secret;
	protected $depthCache = [];
	protected $depthQueue = [];
	protected $chartQueue = [];
	protected $charts = [];
	protected $info = [];
	public $balances = [];
	public $btc_value = 0.00; // value of available assets
	public $btc_total = 0.00; // value of available + onOrder assets

	public function buy($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
	}
	public function sell($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
	}
	public function marketBuy($symbol, $quantity) {
		return $this->order("BUY", $symbol, $quantity, 0, "MARKET", $flags = []);
	}
	public function marketSell($symbol, $quantity) {
		return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags = []);
	}
	public function cancel($symbol, $orderid) {
		return $this->signedRequest("v3/order", ["symbol"=>$symbol, "orderId"=>$orderid], "DELETE");
	}
	public function orderStatus($symbol, $orderid) {
		return $this->signedRequest("v3/order", ["symbol"=>$symbol, "orderId"=>$orderid]);
	}
	public function openOrders($symbol) {
		return $this->signedRequest("v3/openOrders",["symbol"=>$symbol]);
	}
	public function orders($symbol, $limit = 500) {
		return $this->signedRequest("v3/allOrders", ["symbol"=>$symbol, "limit"=>$limit]);
	}
	public function history($symbol, $limit = 500) {
		return $this->signedRequest("v3/myTrades", ["symbol"=>$symbol, "limit"=>$limit]);
	}
	public function exchangeInfo() {
		return $this->apiRequest("v1/exchangeInfo");
	}
	public function withdraw($asset, $address, $amount, $addressTag = false) {
		$options = ["asset"=>$asset, "address"=>$address, "amount"=>$amount, "wapi"=>true];
		if ( $addressTag ) $options['addressTag'] = $addressTag;
		return $this->signedRequest("v3/withdraw.html", $options, "POST");
	}
	public function depositAddress($asset) {
		$params = ["wapi"=>true, "asset"=>$asset];
		return $this->signedRequest("v3/depositAddress.html", $params, "GET");
	}
	public function depositHistory($asset = false) {
		$params = ["wapi"=>true];
		if ( $asset ) $params['asset'] = $asset;
		return $this->signedRequest("v3/depositHistory.html", $params, "GET");
	}
	public function withdrawHistory($asset = false) {
		$params = ["wapi"=>true];
		if ( $asset ) $params['asset'] = $asset;
		return $this->signedRequest("v3/withdrawHistory.html", $params, "GET");
	}
	public function prices() {
		return $this->priceData($this->request("v1/ticker/allPrices"));
	}
	public function bookPrices() {
		return $this->bookPriceData($this->request("v1/ticker/allBookTickers"));
	}
	public function account() {
		return $this->signedRequest("v3/account");
	}
	public function prevDay($symbol) {
		return $this->request("v1/ticker/24hr", ["symbol"=>$symbol]);
	}
	public function aggTrades($symbol) {
		return $this->tradesData($this->request("v1/aggTrades", ["symbol"=>$symbol]));
	}
	public function depth($symbol, $limit) {
		$json = $this->request("v1/depth",["symbol"=>$symbol, "limit"=>$limit]);
		if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
		$this->info[$symbol]['firstUpdate'] = $json['lastUpdateId'];
		return $this->depthData($symbol, $json);
	}
	public function balances($priceData = false) {
		return $this->balanceData($this->signedRequest("v3/account"),$priceData);
	}
	private function request($url, $params = [], $method = "GET") {
		$opt = [
			"http" => [
				"method" => $method,
				"ignore_errors" => true,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\n"
			]
		];
		$context = stream_context_create($opt);
		$query = http_build_query($params, '', '&');
		return json_decode(file_get_contents($this->base.$url.'?'.$query, false, $context), true);
	}
	private function signedRequest($url, $params = [], $method = "GET") {
		if ( empty($this->api_key) ) die("signedRequest error: API Key not set!");
		if ( empty($this->api_secret) ) die("signedRequest error: API Secret not set!");
		$base = $this->base;
		$opt = [
			"http" => [
				"method" => $method,
				"ignore_errors" => true,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\n"
			]
		];
		$context = stream_context_create($opt);
		$params['timestamp'] = number_format(microtime(true)*1000,0,'.','');
		if ( isset($params['wapi']) ) {
			unset($params['wapi']);
			$base = $this->wapi;
		}
		$query = http_build_query($params, '', '&');
		$signature = hash_hmac('sha256', $query, $this->api_secret);
		$endpoint = $base.$url.'?'.$query.'&signature='.$signature;
		$data = file_get_contents($endpoint, false, $context);
		return json_decode($data, true);
	}
	private function apiRequest($url, $method = "GET") {
		if ( empty($this->api_key) ) die("apiRequest error: API Key not set!");
		$opt = [
			"http" => [
				"method" => $method,
				"ignore_errors" => true,
				"header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\n"
			]
		];
		$context = stream_context_create($opt);
		return json_decode(file_get_contents($this->base.$url, false, $context), true);
	}
	public function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
		$opt = [
			"symbol" => $symbol,
			"side" => $side,
			"type" => $type,
			"quantity" => $quantity,
			"recvWindow" => 60000
		];
		if ( $type == "LIMIT" ) {
			$opt["price"] = $price;
			$opt["timeInForce"] = "GTC";
		}
		if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
		if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
		return $this->signedRequest("v3/order", $opt, "POST");
	}
	//1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
	public function candlesticks($symbol, $interval = "5m") {
		if ( !isset($this->charts[$symbol]) ) $this->charts[$symbol] = [];
		$response = $this->request("v1/klines", ["symbol"=>$symbol, "interval"=>$interval]);
		$ticks = $this->chartData($symbol, $interval, $response);
		$this->charts[$symbol][$interval] = $ticks;
		return $ticks;
	}
	// Converts all your balances into a nice array
	// If priceData is passed from $api->prices() it will add btcValue & btcTotal to each symbol
	// This function sets $btc_value which is your estimated BTC value of all assets combined and $btc_total which includes amount on order
	private function balanceData($array, $priceData = false) {
		if ( $priceData ) $btc_value = $btc_total = 0.00;
		$balances = [];
		foreach ( $array['balances'] as $obj ) {
			$asset = $obj['asset'];
			$balances[$asset] = ["available"=>$obj['free'], "onOrder"=>$obj['locked'], "btcValue"=>0.00000000, "btcTotal"=>0.00000000];
			if ( $priceData ) {
				if ( $obj['free'] + $obj['locked'] < 0.00000001 ) continue;
				if ( $asset == 'BTC' ) {
					$balances[$asset]['btcValue'] = $obj['free'];
					$balances[$asset]['btcTotal'] = $obj['free'] + $obj['locked'];
					$btc_value+= $obj['free'];
					$btc_total+= $obj['free'] + $obj['locked'];
					continue;
				}
				$symbol = $asset.'BTC';
				if ( $symbol == 'USDTBTC' ) {
					$btcValue = number_format($obj['free'] / $priceData['BTCUSDT'],8,'.','');
					$btcTotal = number_format(($obj['free'] + $obj['locked']) / $priceData['BTCUSDT'],8,'.','');
				} elseif ( !isset($priceData[$symbol]) ) {
					$btcValue = $btcTotal = 0;
				} else {
					$btcValue = number_format($obj['free'] * $priceData[$symbol],8,'.','');
					$btcTotal = number_format(($obj['free'] + $obj['locked']) * $priceData[$symbol],8,'.','');
				}
				$balances[$asset]['btcValue'] = $btcValue;
				$balances[$asset]['btcTotal'] = $btcTotal;
				$btc_value+= $btcValue;
				$btc_total+= $btcTotal;
			}
		}
		if ( $priceData ) {
			uasort($balances, function($a, $b) { return $a['btcValue'] < $b['btcValue']; });
			$this->btc_value = $btc_value;
			$this->btc_total = $btc_total;
		}
		return $balances;
	}
	// Convert balance WebSocket data into array
	private function balanceHandler($json) {
		$balances = [];
		foreach ( $json as $item ) {
			$asset = $item->a;
			$available = $item->f;
			$onOrder = $item->l;
			$balances[$asset] = ["available"=>$available, "onOrder"=>$onOrder];
		}
		return $balances;
	}
	// Convert WebSocket trade execution into array
	private function executionHandler($json) {
		return [
			"symbol" => $json->s,
			"side" => $json->S,
			"orderType" => $json->o,
			"quantity" => $json->q,
			"price" => $json->p,
			"executionType" => $json->x,
			"orderStatus" => $json->X,
			"rejectReason" => $json->r,
			"orderId" => $json->i,
			"clientOrderId" => $json->c,
			"orderTime" => $json->T,
			"eventTime" => $json->E
		];
	}
	// Convert kline data into object
	private function chartData($symbol, $interval, $ticks) {
		if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
		if ( !isset($this->info[$symbol][$interval]) ) $this->info[$symbol][$interval] = [];
		$output = [];
		foreach ( $ticks as $tick ) {
			list($openTime, $open, $high, $low, $close, $assetVolume, $closeTime, $baseVolume, $trades, $assetBuyVolume, $takerBuyVolume, $ignored) = $tick;
			$output[$openTime] = [
				"open" => $open,
				"high" => $high,
				"low" => $low,
				"close" => $close,
				"volume" => $baseVolume,
				"openTime" =>$openTime,
				"closeTime" =>$closeTime
			];
		}
		$this->info[$symbol][$interval]['firstOpen'] = $openTime;
		return $output;
	}
	// Convert aggTrades data into easier format
	private function tradesData($trades) {
		$output = [];
		foreach ( $trades as $trade ) {
			$price = $trade['p'];
			$quantity = $trade['q'];
			$timestamp = $trade['T'];
			$maker = $trade['m'] ? 'true' : 'false';
			$output[] = ["price"=>$price, "quantity"=> $quantity, "timestamp"=>$timestamp, "maker"=>$maker];
		}
		return $output;
	}
	// Consolidates Book Prices into an easy to use object
	private function bookPriceData($array) {
		$bookprices = [];
		foreach ( $array as $obj ) {
			$bookprices[$obj['symbol']] = [
				"bid"=>$obj['bidPrice'],
				"bids"=>$obj['bidQty'],
				"ask"=>$obj['askPrice'],
				"asks"=>$obj['askQty']
			];
		}
		return $bookprices;
	}
	// Converts Price Data into an easy key/value array
	private function priceData($array) {
		$prices = [];
		foreach ( $array as $obj ) {
			$prices[$obj['symbol']] = $obj['price'];
		}
		return $prices;
	}
	// Converts depth cache into a cumulative array
	public function cumulative($depth) {
		$bids = []; $asks = [];
		$cumulative = 0;
		foreach ( $depth['bids'] as $price => $quantity ) {
			$cumulative+= $quantity;
			$bids[] = [$price, $cumulative];
		}
		$cumulative = 0;
		foreach ( $depth['asks'] as $price => $quantity ) {
			$cumulative+= $quantity;
			$asks[] = [$price, $cumulative];
		}
		return ["bids"=>$bids, "asks"=>array_reverse($asks)];
	}
	// Converts Chart Data into array for highstock & kline charts
	public function highstock($chart, $include_volume = false) {
		$array = [];
		foreach ( $chart as $timestamp => $obj ) {
			$line = [
				$timestamp,
				floatval($obj['open']),
				floatval($obj['high']),
				floatval($obj['low']),
				floatval($obj['close'])
			];
			if ( $include_volume ) $line[] = floatval($obj['volume']);
			$array[] = $line;
		}
		return $array;
	}
	// For WebSocket Depth Cache
	private function depthHandler($json) {
		$symbol = $json['s'];
		if ( $json['u'] <= $this->info[$symbol]['firstUpdate'] ) return;
		foreach ( $json['b'] as $bid ) {
			$this->depthCache[$symbol]['bids'][$bid[0]] = $bid[1];
			if ( $bid[1] == "0.00000000" ) unset($this->depthCache[$symbol]['bids'][$bid[0]]);
		}
		foreach ( $json['a'] as $ask ) {
			$this->depthCache[$symbol]['asks'][$ask[0]] = $ask[1];
			if ( $ask[1] == "0.00000000" ) unset($this->depthCache[$symbol]['asks'][$ask[0]]);
		}
	}
	// For WebSocket Chart Cache
	private function chartHandler($symbol, $interval, $json) {
		if ( !$this->info[$symbol][$interval]['firstOpen'] ) { // Wait for /kline to finish loading
			$this->chartQueue[$symbol][$interval][] = $json;
			return;
		}
		$chart = $json->k;
		$symbol = $json->s;
		$interval = $chart->i;
		$tick = $chart->t;
		if ( $tick < $this->info[$symbol][$interval]['firstOpen'] ) return; // Filter out of sync data
		$open = $chart->o;
		$high = $chart->h;
		$low = $chart->l;
		$close = $chart->c;
		$volume = $chart->q; //+trades buyVolume assetVolume makerVolume
		$this->charts[$symbol][$interval][$tick] = ["open"=>$open, "high"=>$high, "low"=>$low, "close"=>$close, "volume"=>$volume];
	}
	// Gets first key of an array
	public function first($array) {
		return array_keys($array)[0];
	}
	// Gets last key of an array
	public function last($array) {
		return array_keys(array_slice($array, -1))[0];
	}
	// Formats nicely for console output
	public function displayDepth($array) {
		$output = '';
		foreach ( ['asks', 'bids'] as $type ) {
			$entries = $array[$type];
			if ( $type == 'asks' ) $entries = array_reverse($entries);
			$output.= "{$type}:".PHP_EOL;
			foreach ( $entries as $price => $quantity ) {
				$total = number_format($price * $quantity,8,'.','');
				$quantity = str_pad(str_pad(number_format(rtrim($quantity,'.0')),10,' ',STR_PAD_LEFT),15);
				$output.= "{$price} {$quantity} {$total}".PHP_EOL;
			}
			//echo str_repeat('-', 32).PHP_EOL;
		}
		return $output;
	}
	// Sorts depth data for display & getting highest bid and lowest ask
	public function sortDepth($symbol, $limit = 11) {
		$bids = $this->depthCache[$symbol]['bids'];
		$asks = $this->depthCache[$symbol]['asks'];
		krsort($bids);
		ksort($asks);
		return ["asks"=> array_slice($asks, 0, $limit, true), "bids"=> array_slice($bids, 0, $limit, true)];
	}
	// Formats depth data for nice display
	private function depthData($symbol, $json) {
		$bids = $asks = [];
		foreach ( $json['bids'] as $obj ) {
			$bids[$obj[0]] = $obj[1];
		}
		foreach ( $json['asks'] as $obj ) {
			$asks[$obj[0]] = $obj[1];
		}
		return $this->depthCache[$symbol] = ["bids"=>$bids, "asks"=>$asks];
	}
	////////////////////////////////////
	// WebSockets
	////////////////////////////////////
	// Pulls /depth data and subscribes to @depth WebSocket endpoint
	// Maintains a local Depth Cache in sync via lastUpdateId. See depth() and depthHandler()
	public function depthCache($symbols, $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		$loop = \React\EventLoop\Factory::create();
		$react = new \React\Socket\Connector($loop);
		$connector = new \Ratchet\Client\Connector($loop, $react);
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			if ( !isset($this->depthQueue[$symbol]) ) $this->depthQueue[$symbol] = [];
			if ( !isset($this->depthCache[$symbol]) ) $this->depthCache[$symbol] = ["bids" => [], "asks" => []];
			$this->info[$symbol]['firstUpdate'] = 0;
			$connector('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@depth')->then(function($ws) use($callback) {
				$ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data, true);
					$symbol = $json['s'];
					if ( $this->info[$symbol]['firstUpdate'] == 0 ) {
						$this->depthQueue[$symbol][] = $json;
						return;
					}
					$this->depthHandler($json);
					call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
				});
				$ws->on('close', function($code = null, $reason = null) {
					echo "depthCache({$symbol}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
				});
			}, function($e) use($loop) {
				echo "depthCache({$symbol})) Could not connect: {$e->getMessage()}".PHP_EOL;
				$loop->stop();
			});
			$this->depth($symbol);
			foreach ( $this->depthQueue[$symbol] as $data ) {
				$this->depthHandler($json);
			}
			$this->depthQueue[$symbol] = [];
			call_user_func($callback, $this, $symbol, $this->depthCache[$symbol]);
		}
		$loop->run();
	}
	// Trades WebSocket Endpoint
	public function trades($symbols, $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		$loop = \React\EventLoop\Factory::create();
		$react = new \React\Socket\Connector($loop);
		$connector = new \Ratchet\Client\Connector($loop, $react);
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			//$this->info[$symbol]['tradesCallback'] = $callback;
			$connector('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@aggTrade')->then(function($ws) use($callback) {
				$ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data, true);
					$symbol = $json['s'];
					$price = $json['p'];
					$quantity = $json['q'];
					$timestamp = $json['T'];
					$maker = $json['m'] ? 'true' : 'false';
					$trades = ["price"=>$price, "quantity"=>$quantity, "timestamp"=>$timestamp, "maker"=>$maker];
					//$this->info[$symbol]['tradesCallback']($this, $symbol, $trades);
					call_user_func($callback, $this, $symbol, $trades);
				});
				$ws->on('close', function($code = null, $reason = null) {
					echo "trades({$symbol}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
				});
			}, function($e) use($loop) {
				echo "trades({$symbol}) Could not connect: {$e->getMessage()}".PHP_EOL;
				$loop->stop();
			});
		}
		$loop->run();
	}
	// Pulls /kline data and subscribes to @klines WebSocket endpoint
	public function chart($symbols, $interval = "30m", $callback) {
		if ( !is_array($symbols) ) $symbols = [$symbols];
		$loop = \React\EventLoop\Factory::create();
		$react = new \React\Socket\Connector($loop);
		$connector = new \Ratchet\Client\Connector($loop, $react);
		foreach ( $symbols as $symbol ) {
			if ( !isset($this->charts[$symbol]) ) $this->charts[$symbol] = [];
			$this->charts[$symbol][$interval] = [];
			if ( !isset($this->info[$symbol]) ) $this->info[$symbol] = [];
			if ( !isset($this->info[$symbol][$interval]) ) $this->info[$symbol][$interval] = [];
			if ( !isset($this->chartQueue[$symbol]) ) $this->chartQueue[$symbol] = [];
			$this->chartQueue[$symbol][$interval] = [];
			$this->info[$symbol][$interval]['firstOpen'] = 0;
			//$this->info[$symbol]['chartCallback'.$interval] = $callback;
			$connector('wss://stream.binance.com:9443/ws/'.strtolower($symbol).'@kline_'.$interval)->then(function($ws) use($callback) {
				$ws->on('message', function($data) use($ws, $callback) {
					$json = json_decode($data);
					$chart = $json->k;
					$symbol = $json->s;
					$interval = $chart->i;
					$this->chartHandler($symbol, $interval, $json);
					//$this->info[$symbol]['chartCallback'.$interval]($this, $symbol, $this->charts[$symbol][$interval]);
					call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
				});
				$ws->on('close', function($code = null, $reason = null) {
					echo "chart({$symbol},{$interval}) WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
				});
			}, function($e) use($loop) {
				echo "chart({$symbol},{$interval})) Could not connect: {$e->getMessage()}".PHP_EOL;
				$loop->stop();
			});
			$this->candlesticks($symbol, $interval);
			foreach ( $this->chartQueue[$symbol][$interval] as $json ) {
				$this->chartHandler($symbol, $interval, $json);
			}
			$this->chartQueue[$symbol][$interval] = [];
			//$this->info[$symbol]['chartCallback'.$interval]($this, $symbol, $this->charts[$symbol][$interval]);
			call_user_func($callback, $this, $symbol, $this->charts[$symbol][$interval]);
		}
		$loop->run();
	}
	// Keep-alive function for userDataStream
	public function keepAlive() {
		$loop = \React\EventLoop\Factory::create();
		$loop->addPeriodicTimer(30, function() {
			$listenKey = $this->options['listenKey'];
			$this->apiRequest("v1/userDataStream?listenKey={$listenKey}", "PUT");
		});
		$loop->run();
	}
	// Issues userDataStream token and keepalive, subscribes to userData WebSocket
	public function userData(&$balance_callback, &$execution_callback = false) {
		$response = $this->apiRequest("v1/userDataStream", "POST");
		$listenKey = $this->options['listenKey'] = $response['listenKey'];
		$this->info['balanceCallback'] = $balance_callback;
		$this->info['executionCallback'] = $execution_callback;
		\Ratchet\Client\connect('wss://stream.binance.com:9443/ws/'.$listenKey)->then(function($ws) {
			$ws->on('message', function($data) use($ws) {
				$json = json_decode($data);
				$type = $json->e;
				if ( $type == "outboundAccountInfo") {
					$balances = $this->balanceHandler($json->B);
					$this->info['balanceCallback']($this, $balances);
				} elseif ( $type == "executionReport" ) {
					$report = $this->executionHandler($json);
					if ( $this->info['executionCallback'] ) {
						$this->info['executionCallback']($this, $report);
					}
				}
			});
			$ws->on('close', function($code = null, $reason = null) {
				echo "userData: WebSocket Connection closed! ({$code} - {$reason})".PHP_EOL;
			});
		}, function($e) {
			echo "userData: Could not connect: {$e->getMessage()}".PHP_EOL;
		});
	}
}
