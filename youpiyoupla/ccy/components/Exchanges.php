<?php namespace youpiyoupla\ccy\Components;

use Auth;
use Input;
use File;
use Request;
use Storage;
use Redirect;
use Cms\Classes\Page;
use October\Rain\Exception\ApplicationException;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use RainLab\Forum\Models\Member as MemberModel;
use youpiyoupla\ccy\Models\Ledgers as LedgersModel;
use youpiyoupla\ccy\Models\Coins as CoinsModel;
use youpiyoupla\ccy\Models\Exchanges as ExchangesModel;
use youpiyoupla\ccy\Models\Totals as TotalsModel;
use youpiyoupla\ccy\Models\Buys as BuysModel;
use youpiyoupla\ccy\Models\Sells as SellsModel;
use youpiyoupla\ccy\Controllers\Apis as ApisCtrl;
use youpiyoupla\ccy\Models\Apis as ApisModel;

class Exchanges extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Exchanges component',
            'description' => 'Upload CSV from exchanges',
        ];
    }
    
    /**
     * 
     * On start
     * 
     **/ 
	public function onRun()
	{
		//check if user is logued
        try {
            if (!$user = Auth::getUser()) {
               return Redirect::to('account')->with('message', 'Need to login');
            }
			//get exchanges list to display in view
			$exchanges = exchangesModel::get();
			$this->page['exchanges'] = $exchanges;
			$this->page['limits'] = ApisCtrl::cryptocompare_api_limit();
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}
    
    /**
     * 
     * When user click on upload button
     * 
     **/ 
	public function onApiUpdate()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }
			switch(Input::get('exchange')){
				case 1:
					$data = $this->checkApiBinance($user->id, 1);
					break;
				case 2:
					$data = $this->checkApiCrypto($user->id, 2);
					break;
				case 3:
					$data = $this->checkApiGDAXs($user->id, 3);
					break;
				case 4:
					$data = $this->checkApiCoinbase($user->id, 4);
					break;
			}
            
		}
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}
	
	public function checkApiCoinbase($user_id, $exchange_id){
		//get key from DB
		$GDAXKeys = ApisModel::where('user_id', $user_id)->where('exchanges_id', $exchange_id)->first();
		$apiGDAX = new ApisCtrl($GDAXKeys["publickey"], $GDAXKeys["privatekey"]);
		//get all history
		$data = $apiGDAX->coinbase_api_query("accounts");
		dd($data);
	}
	
	public function checkApiGDAXs($user_id, $exchange_id){
		//get key from DB
		$GDAXKeys = ApisModel::where('user_id', $user_id)->where('exchanges_id', $exchange_id)->first();
		$pass = $GDAXKeys["passphrase"];
		$priv = $GDAXKeys["privatekey"];

		$apiGDAX = new ApisCtrl($GDAXKeys["publickey"], $pass, $priv);
		//get all history
		$data = $apiGDAX->gdax_api_query("fills");
		$counter = 0;
		$toSave = array();
		if(!isset($data["message"])){
			for($x=0; $x<sizeof($data);$x++){
				$toSave[$counter]['user_id'] = Auth::getUser()->id;
				$arr = explode("-", $data[$x]["product_id"], 2);
				$toSave[$counter]['coins_id'] = $this->getCCYIdAbv($arr[0]);
				//-- to do dynamic
				$toSave[$counter]['exchanges_id'] = $exchange_id;
				$toSave[$counter]['rate'] = $data[$x]["price"];
				$toSave[$counter]['currency_id'] = $this->getCCYIdAbv($arr[1]);
				$toSave[$x]['date'] = $data[$x]["created_at"];
				//$toSave[$x]['date'] = date('d-m-Y H:i:s', strtotime($data[$x]["TimeStamp"]));
				//$toSave[$x]['date'] = date('Y-m-d H:i:s', strtotime($arrdate[1]."/".$arrdate[0]."/".$arrdate[2]));
				$dateNow = date('Y-m-d H:i:s');
				$toSave[$counter]['exchangetradeid'] = $data[$x]["trade_id"];
				$toSave[$counter]['created_at'] = $dateNow;
				$toSave[$counter]['updated_at'] = $dateNow;
				$toSave[$counter]['amount'] = $data[$x]["size"];
				$toSave[$counter]['total'] = $data[$x]["price"]*$data[$x]["size"]-$data[$x]["fee"];
				if($data[$x]["side"]==	"buy"){
					$toSave[$counter]['buyorsell'] = 1;
				}else{
					$toSave[$counter]['buyorsell'] = 0;
				}
				$counter++;
			}
		}
		//process these like when csv upload
		//check if ID already exists
		$resultLedger = $this->checkLedgerDB($toSave, $exchange_id);
		
		//save to DB
		$column_id = ledgersModel::addData($resultLedger);
		//update total
		$totalUpdate = $this->updateTotal($resultLedger);
	}
	
	public function checkApiCrypto($user_id, $exchange_id){
		//get key from DB
		$cryptoKeys = ApisModel::where('user_id', $user_id)->where('exchanges_id', $exchange_id)->first();

		$pass = $cryptoKeys["passphrase"];
		$priv = $cryptoKeys["privatekey"];

		$apiCrypto = new ApisCtrl($cryptoKeys["publickey"], $pass, $priv);
		//get all history
		$data = $apiCrypto->cryptopia_api_query_obj("GetTradeHistory", array( 'Market'=> "",'Count'=> 1000000 ) );
		//dd($data["Data"]);
		//get all order for each pair
		$counter = 0;
		$toSave = array();
		if($data!="null"){
			for($x=0; $x<sizeof($data["Data"]);$x++){
				$toSave[$counter]['user_id'] = Auth::getUser()->id;
				$arr = explode("/", $data["Data"][$x]["Market"], 2);
				$toSave[$counter]['coins_id'] = $this->getCCYIdAbv($arr[0]);
				//-- to do dynamic
				$toSave[$counter]['exchanges_id'] = $exchange_id;
				$toSave[$counter]['rate'] = $data["Data"][$x]["Rate"];
				$toSave[$counter]['currency_id'] = $this->getCCYIdAbv($arr[1]);
				$toSave[$x]['date'] = $data["Data"][$x]["TimeStamp"];
				//$toSave[$x]['date'] = date('d-m-Y H:i:s', strtotime($data["Data"][$x]["TimeStamp"]));
				//$toSave[$x]['date'] = date('Y-m-d H:i:s', strtotime($arrdate[1]."/".$arrdate[0]."/".$arrdate[2]));
				$dateNow = date('Y-m-d H:i:s');
				$toSave[$counter]['exchangetradeid'] = $data["Data"][$x]["TradeId"];
				$toSave[$counter]['created_at'] = $dateNow;
				$toSave[$counter]['updated_at'] = $dateNow;
				$toSave[$counter]['amount'] = $data["Data"][$x]["Amount"];
				$toSave[$counter]['total'] = $data["Data"][$x]["Total"]-$data["Data"][$x]["Fee"];
				if($data["Data"][$x]["Type"]==	"Buy"){
					$toSave[$counter]['buyorsell'] = 1;
				}else{
					$toSave[$counter]['buyorsell'] = 0;
				}
				$counter++;
			}
		}
		//process these like when csv upload
		//check if ID already exists
		$resultLedger = $this->checkLedgerDB($toSave, $exchange_id);
		
		//save to DB
		$column_id = ledgersModel::addData($resultLedger);
		//update total
		$totalUpdate = $this->updateTotal($resultLedger);
	}
	
	public function checkApiBinance($user_id, $exchange_id){
		//get key from DB
		$binanceKeys = ApisModel::where('user_id', $user_id)->where('exchanges_id', $exchange_id)->first();

		$pass = $binanceKeys["passphrase"];
		$priv = $binanceKeys["privatekey"];

		$apiBinance = new ApisCtrl($binanceKeys["publickey"], $pass, $priv);
		//get all pair on binance
		$bookPrices = $apiBinance->bookPrices();
		//get all order for each pair
		$counter = 0;
		$toSave = array();
		foreach($bookPrices as $key => $value){
			$mykey = $key;
			$data = $apiBinance->history($mykey);
			//dd($data);
			for($x=0; $x<sizeof($data);$x++){
				if(isset($data[$x]["price"])){
					$toSave[$counter]['user_id'] = Auth::getUser()->id;
					$arr = array();
					$arr[0] = substr($mykey, 0, 3);
					$arr[1] = substr($mykey, -3);
					$toSave[$counter]['coins_id'] = $this->getCCYIdAbv($arr[0]);
					//-- to do dynamic
					$toSave[$counter]['exchanges_id'] = $exchange_id;
					$toSave[$counter]['rate'] = $data[$x]["price"];
					$toSave[$counter]['currency_id'] = $this->getCCYIdAbv($arr[1]);
					$toSave[$counter]['date'] = $data[$x]["time"];
					$dateNow = date('Y-m-d H:i:s');
					$amount = $data[$x]["qty"]*$data[$x]["price"];
					$toSave[$counter]['exchangetradeid'] = $toSave[$counter]['date'].$amount.$toSave[$counter]['coins_id'].$toSave[$counter]['currency_id'];
					$toSave[$counter]['created_at'] = $dateNow;
					$toSave[$counter]['updated_at'] = $dateNow;
					if($data[$x]["isBuyer"]==	true){
						$toSave[$counter]['buyorsell'] = 1;
						$toSave[$counter]['amount'] = $data[$x]["qty"] - $data[$x]["commission"];
						$toSave[$counter]['total'] = $data[$x]["qty"]*$data[$x]["price"];
					}else{
						$toSave[$counter]['buyorsell'] = 0;
						$toSave[$counter]['amount'] = $data[$x]["qty"];
						$toSave[$counter]['total'] = $data[$x]["qty"]*$data[$x]["price"] - $data[$x]["commission"];
					}
					$counter++;
				}
			}
		}

		//process these like when csv upload
		//check if ID already exists
		$resultLedger = $this->checkLedgerDB($toSave, 1);
		//save to DB
		$column_id = ledgersModel::addData($resultLedger);
		//update total
		$totalUpdate = $this->updateTotal($resultLedger);
	}
	
	public function checkLedgerDB($toSave, $exchangeId){
		$ledgerData = LedgersModel::where('exchanges_id', $exchangeId)->get();
		$counter = 0;
		$saveCounter = 0;
		$newSave = array();
		//dd($toSave);
		if(isset($ledgerData)){
			for($x=0; $x<sizeof($toSave);$x++){
				foreach($ledgerData as $lData){
					if($lData->exchangetradeid == $toSave[$x]["exchangetradeid"]){
						$counter++;
					}
				}
				if($counter==0){
					$newSave[$saveCounter] = $toSave[$x];
					$saveCounter++;
				}
				$counter = 0;
			}
			//dd($newSave);
			return $newSave;
		}else{
			return $toSave;
		}
	}
	
	public function onUpCSV()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }
			if (Input::file('csv')) {
				if(Input::file('csv')->getMimeType()=="text/plain"){
					if((Input::file('csv')->getClientOriginalExtension()=="csv")||(Input::file('csv')->getClientOriginalExtension()=="xlsx")){
						$timestamp = date('Y-m-d-G-i-s');
						$check = rand(0,100);
						/*$filenameData = 'data'.$timestamp.'-'.$check;
						$folder = "storage/temp/public/updata/2".$filenameData;
						Input::file('csv')->move($folder, 'url.xls');
						$file = "storage/temp/public/updata/2".$filenameData."/url.xls";*/
						$csvMod = $this->assoc_getcsv(Input::file('csv'));
						//Manually list all exchanges
						//To be improved
						switch(Input::get('exchange')){
							case 1:
								$data = $this->checkDataBinance($csvMod);
								break;
							case 2:
								$data = $this->checkDataCryptoHistory($csvMod);
								break;
							case 3:
								$data = $this->checkDataGDAXfills($csvMod);
								break;
							case 4:
								$data = $this->checkDataCoinbase($csvMod);
								break;
						}
						//check amount per coin to update total table
						$totalUpdate = $this->updateTotal($data);
					}
				}
			}
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}

     /**
     * 
     * Update total table for each coin in CSV file
     * TODO calculate avg rate & total usd amount before updating total table
     * 
     **/ 
	public function updateTotal($data){
		//get coin list from DB
		$coins = coinsModel::get();
		$y = 0;
		$z = 0;
		$side1 = array();
		//for each coin available
		foreach($coins as $coin){
			//go trhough data array
			for($x = 0; $x < sizeof($data); $x++){
				//check coin side 1
				if($coin->id == $data[$x]['coins_id']){
					
					//get rates USD
					$date = strtotime($data[$x]['date']);
					$ccy = CoinsModel::where('id', $data[$x]['coins_id'])->get();
					$rate = ApisCtrl::cryptocompare_api_query($ccy[0]->abv, $date);
					
					//check sell or buy
					//if buy
					if($data[$x]['buyorsell']){
						if(!isset($side1[$y]['total'])){
							$side1[$y]['total']=$data[$x]['amount'];
							$side1[$y]['amount_usd']=$data[$x]['amount']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avg']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['total']= $side1[$y]['total'] + $data[$x]['amount'];
							$side1[$y]['amount_usd']= $side1[$y]['amount_usd'] + ($data[$x]['amount']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avg']= ($side1[$y]['rate_avg']*$side1[$y]['total'] + $data[$x]['amount']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['total']+$data[$x]['amount']);
						}
						if(!isset($side1[$y]['totalbuy'])){
							$side1[$y]['totalbuy']=$data[$x]['amount'];
							$side1[$y]['amount_usdbuy'] = $data[$x]['amount']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avgbuy']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['totalbuy']= $side1[$y]['totalbuy'] + $data[$x]['amount'];
							$side1[$y]['amount_usdbuy']= $side1[$y]['amount_usdbuy'] + ($data[$x]['amount']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avgbuy']= ($side1[$y]['rate_avgbuy']*$side1[$y]['totalbuy'] + $data[$x]['amount']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['totalbuy']+$data[$x]['amount']);
						}
					//if sell
					}else{
						if(!isset($side1[$y]['total'])){
							$side1[$y]['total']=-($data[$x]['amount']);
							$side1[$y]['amount_usd']=-($data[$x]['amount']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avg']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['total']= $side1[$y]['total'] - $data[$x]['amount'];
							$side1[$y]['amount_usd']= $side1[$y]['amount_usd'] - ($data[$x]['amount']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avg']= ($side1[$y]['rate_avg']*$side1[$y]['total'] - $data[$x]['amount']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['total']-$data[$x]['amount']);
						}
						if(!isset($side1[$y]['totalsell'])){
							$side1[$y]['totalsell']=$data[$x]['amount'];
							$side1[$y]['amount_usdsell'] = $data[$x]['amount']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avgsell']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['totalsell']= $side1[$y]['totalsell'] + $data[$x]['amount'];
							$side1[$y]['amount_usdsell']= $side1[$y]['amount_usdsell'] + ($data[$x]['amount']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avgsell']= ($side1[$y]['rate_avgsell']*$side1[$y]['totalsell'] + $data[$x]['amount']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['totalsell']+$data[$x]['amount']);
						}
					}
					$side1[$y]['user_id'] = $data[$x]['user_id'];
					$side1[$y]['created_at'] = $data[$x]['created_at'];
					$side1[$y]['updated_at']= $data[$x]['updated_at'];
					$side1[$y]['coins_id'] = $data[$x]['coins_id'];
					$z++;
				}
				//redo for coin side 2
				if($coin->id == $data[$x]['currency_id']){
					
					//get rates USD
					$date = strtotime($data[$x]['date']);
					$ccy = CoinsModel::where('id', $data[$x]['currency_id'])->get();
					$rate = ApisCtrl::cryptocompare_api_query2($ccy[0]->abv, $date);
					if($data[$x]['buyorsell']){
						if(!isset($side1[$y]['total'])){
							$side1[$y]['total']=-$data[$x]['total'];
							$side1[$y]['amount_usd']=-$data[$x]['total']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avg']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['total']= $side1[$y]['total'] - $data[$x]['total'];
							$side1[$y]['amount_usd']= $side1[$y]['amount_usd'] - ($data[$x]['total']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avg']= ($side1[$y]['rate_avg']*$side1[$y]['total'] - $data[$x]['total']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['total']-$data[$x]['total']);
						}
						if(!isset($side1[$y]['totalsell'])){
							$side1[$y]['totalsell']=$data[$x]['total'];
							$side1[$y]['amount_usdsell'] = $data[$x]['total']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avgsell']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['totalsell']= $side1[$y]['totalsell'] + $data[$x]['total'];
							$side1[$y]['amount_usdsell']= $side1[$y]['amount_usdsell'] + ($data[$x]['total']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avgsell']= ($side1[$y]['rate_avgsell']*$side1[$y]['totalsell'] + $data[$x]['total']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['totalsell']+$data[$x]['total']);
						}
					//if sell
					}else{
						if(!isset($side1[$y]['total'])){
							$side1[$y]['total']=$data[$x]['total'];
							$side1[$y]['amount_usd']=$data[$x]['total']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avg']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['total']= $side1[$y]['total'] + $data[$x]['total'];
							$side1[$y]['amount_usd']= $side1[$y]['amount_usd'] + ($data[$x]['total']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avg']= ($side1[$y]['rate_avg']*$side1[$y]['total'] + ($data[$x]['total']*$rate[$ccy[0]->abv]['USD']))/($side1[$y]['total']+$data[$x]['total']);
						}
						if(!isset($side1[$y]['totalbuy'])){
							$side1[$y]['totalbuy']=$data[$x]['total'];
							$side1[$y]['amount_usdbuy'] = $data[$x]['total']*$rate[$ccy[0]->abv]['USD'];
							$side1[$y]['rate_avgbuy']=$rate[$ccy[0]->abv]['USD'];
						}else{
							$side1[$y]['totalbuy']= $side1[$y]['totalbuy'] + $data[$x]['total'];
							$side1[$y]['amount_usdbuy']= $side1[$y]['amount_usdbuy'] + ($data[$x]['total']*$rate[$ccy[0]->abv]['USD']);
							$side1[$y]['rate_avgbuy']= ($side1[$y]['rate_avgbuy']*$side1[$y]['totalbuy'] + $data[$x]['total']*$rate[$ccy[0]->abv]['USD'])/($side1[$y]['totalbuy']+$data[$x]['total']);
						}
					}
					$side1[$y]['user_id'] = $data[$x]['user_id'];
					$side1[$y]['created_at'] = $data[$x]['created_at'];
					$side1[$y]['updated_at']= $data[$x]['updated_at'];
					$side1[$y]['coins_id'] = $data[$x]['currency_id'];
					$z++;
				}
			}
			if($z > 0){
				$z = 0;
				$y++;
			}
		}
		//save both coin & currency == coin 1 & coin 2 results to DB
		// update or create in DB
		$idTotal = TotalsModel::bulkUpdateOrCreate($side1);
		$idSell = SellsModel::bulkUpdateOrCreate($side1);
		$idBuy = BuysModel::bulkUpdateOrCreate($side1);
		return $idTotal;
	} 
	
	public function checkDataCoinbase($csvMod){
		//dd($csvMod);
		if(array_column($csvMod, 'Currency')) {
			if(array_column($csvMod, 'Transfer ID')) {
				if(array_column($csvMod, 'Transfer Total Currency')) {
					if(array_column($csvMod, 'Amount')) {
						if(array_column($csvMod, 'Transfer Total')) {
							if(array_column($csvMod, 'Transfer Fee')) {
								if(array_column($csvMod, 'Timestamp')) {
									$sani = array(
												'Currency'   => FILTER_SANITIZE_STRING,
												'Transfer ID'    => FILTER_SANITIZE_STRING,
												'Transfer Total Currency'     => FILTER_SANITIZE_STRING,
												'Amount'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Transfer Total' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Transfer Fee' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Timestamp'   => FILTER_SANITIZE_STRING
											);
											$x = 0;
											foreach($csvMod as $value)
											{
												$myinputs[$x] = filter_var_array($value, $sani);
												$x++;
											}
											//dd($myinputs);
									//save to DB ledger table
									$data = $this->saveCoinbaseHistory($myinputs);
								}
							}
						}
					}
				}
			}
		}else{
			//error message
			echo "reror";
		}
		return $data;
	}
	
	public function saveCoinbaseHistory($myinputs)
	{
		for($x = 0; $x < sizeof($myinputs); $x++)
		{
			if($myinputs[$x]["Transfer Total"]!=""){
				$data[$x]['user_id'] = Auth::getUser()->id;
				$arr = array();
				$data[$x]['coins_id'] = $this->getCCYIdAbv($myinputs[$x]["Currency"]);
				//$data[$x]['coins_id'] = substr($myinputs[$x]["Market"], 0, 3);
				//-- to do dynamic
				$data[$x]['exchanges_id'] = 4;
				//
				$data[$x]['rate'] = $myinputs[$x]["Transfer Total"]/$myinputs[$x]["Amount"];
				$data[$x]['currency_id'] = $this->getCCYIdAbv($myinputs[$x]["Transfer Total Currency"]);
				//$data[$x]['currency_id'] = substr(strpbrk($myinputs[$x]["Market"],"/"), 1, 3);
				//$data[$x]['date'] = date('d-m-Y H:i:s', strtotime($myinputs[$x]["Date"]));
				$data[$x]['date'] = $myinputs[$x]["Timestamp"];
				//$arrdate = explode("/", $myinputs[$x]["Timestamp"], 3);
				//dd($data[$x]['date']);
				$dateNow = date('Y-m-d H:i:s');
				$data[$x]['exchangetradeid'] = $myinputs[$x]["Transfer ID"];
				$data[$x]['created_at'] = $dateNow;
				$data[$x]['updated_at'] = $dateNow;
				if($myinputs[$x]["Transfer Total"]>0){
					$data[$x]['buyorsell'] = 1;
					$data[$x]['amount'] = $myinputs[$x]["Amount"];
					$data[$x]['total'] = $myinputs[$x]["Transfer Total"]+$myinputs[$x]["Transfer Fee"];
				}else{
					$data[$x]['buyorsell'] = 0;
					$data[$x]['amount'] = $myinputs[$x]["Amount"];
					$data[$x]['total'] = $myinputs[$x]["Transfer Total"] + $myinputs[$x]["Transfer Fee"];
				}
			}
		}
		$column_id = ledgersModel::addData($data);
		return $data;
	}

     /**
     * 
     * Check data consitancy for Binance exchange history file
     * 
     **/ 
	public function checkDataBinance($csvMod){
		//dd($csvMod);
		if(array_column($csvMod, 'Market')) {
			if(array_column($csvMod, 'Type')) {
				if(array_column($csvMod, 'Price')) {
					if(array_column($csvMod, 'Amount')) {
						if(array_column($csvMod, 'Total')) {
							if(array_column($csvMod, 'Fee')) {
								if(array_column($csvMod, 'Date')) {
									$sani = array(
												'Market'   => FILTER_SANITIZE_STRING,
												'Type'    => FILTER_SANITIZE_STRING,
												'Price'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Amount'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Total' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Fee' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Date'   => FILTER_SANITIZE_STRING
											);
											$x = 0;
											foreach($csvMod as $value)
											{
												$myinputs[$x] = filter_var_array($value, $sani);
												$x++;
											}
											//dd($myinputs);
									//save to DB ledger table
									$data = $this->saveBinanceHistory($myinputs);
								}
							}
						}
					}
				}
			}
		}else{
			//error message
			echo "reror";
		}
		return $data;
	}
    
    /**
     * 
     * Save Binance history file to DB
     * 
     **/ 
	public function saveBinanceHistory($myinputs)
	{
		for($x = 0; $x < sizeof($myinputs); $x++)
		{
			$data[$x]['user_id'] = Auth::getUser()->id;
			$arr = array();
			$arr[0] = substr($myinputs[$x]["Market"], 0, 3);
			$arr[1] = substr($myinputs[$x]["Market"], -3);
			$data[$x]['coins_id'] = $this->getCCYIdAbv($arr[0]);
			//$data[$x]['coins_id'] = substr($myinputs[$x]["Market"], 0, 3);
			//-- to do dynamic
			$data[$x]['exchanges_id'] = 1;
			//
			$data[$x]['rate'] = $myinputs[$x]["Price"];
			$data[$x]['currency_id'] = $this->getCCYIdAbv($arr[1]);
			//$data[$x]['currency_id'] = substr(strpbrk($myinputs[$x]["Market"],"/"), 1, 3);
			//$data[$x]['date'] = date('d-m-Y H:i:s', strtotime($myinputs[$x]["Date"]));
			$data[$x]['date'] = $myinputs[$x]["Date"];
			//$arrdate = explode("/", $myinputs[$x]["Timestamp"], 3);
			$data[$x]['date'] = $data[$x]['date'];
			//dd($data[$x]['date']);
			$dateNow = date('Y-m-d H:i:s');
			$data[$x]['exchangetradeid'] = $data[$x]['date'].$myinputs[$x]["Amount"].$data[$x]['coins_id'].$data[$x]['currency_id'];
			$data[$x]['created_at'] = $dateNow;
			$data[$x]['updated_at'] = $dateNow;
			if($myinputs[$x]["Type"]=="BUY"){
				$data[$x]['buyorsell'] = 1;
				$data[$x]['amount'] = $myinputs[$x]["Amount"] - $myinputs[$x]["Fee"];
				$data[$x]['total'] = $myinputs[$x]["Total"];
			}else{
				$data[$x]['buyorsell'] = 0;
				$data[$x]['amount'] = $myinputs[$x]["Amount"];
				$data[$x]['total'] = $myinputs[$x]["Total"] - $myinputs[$x]["Fee"];
			}
		}
		$column_id = ledgersModel::addData($data);
		return $data;
	}

     /**
     * 
     * Check data consitancy for GDAX exchange history file
     * 
     **/ 
	public function checkDataCryptoHistory($csvMod){
		if(array_column($csvMod, 'Market')) {
			if(array_column($csvMod, 'Type')) {
				if(array_column($csvMod, 'Rate')) {
					if(array_column($csvMod, 'Amount')) {
						if(array_column($csvMod, 'Total')) {
							if(array_column($csvMod, 'Fee')) {
								if(array_column($csvMod, 'Timestamp')) {
									$sani = array(
												'#'   => FILTER_SANITIZE_STRING,
												'Market'    => FILTER_SANITIZE_STRING,
												'Type'    => FILTER_SANITIZE_STRING,
												'Rate'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Amount'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Total' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Fee' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'Timestamp'   => FILTER_SANITIZE_STRING
											);
											$x = 0;
											foreach($csvMod as $value)
											{
												$myinputs[$x] = filter_var_array($value, $sani);
												$x++;
											}
									//save to DB ledger table
									$data = $this->saveCryptoHistory($myinputs);
								}
							}
						}
					}
				}
			}
		}else{
			//error message
			echo "reror";
		}
		return $data;
	}
    
    /**
     * 
     * Save Cryptopia history file to DB
     * 
     **/ 
	public function saveCryptoHistory($myinputs)
	{//dd($myinputs);
		for($x = 0; $x < sizeof($myinputs); $x++)
		{
			$data[$x]['user_id'] = Auth::getUser()->id;
			$arr = explode("/", $myinputs[$x]["Market"], 2);
			$data[$x]['coins_id'] = $this->getCCYIdAbv($arr[0]);
			//$data[$x]['coins_id'] = substr($myinputs[$x]["Market"], 0, 3);
			$data[$x]['amount'] = $myinputs[$x]["Amount"];
			//-- to do dynamic
			$data[$x]['exchanges_id'] = 2;
			$data[$x]['exchangetradeid'] = $myinputs[$x]["#"];
			//
			$data[$x]['rate'] = $myinputs[$x]["Rate"];
			$data[$x]['currency_id'] = $this->getCCYIdAbv($arr[1]);
			//$data[$x]['currency_id'] = substr(strpbrk($myinputs[$x]["Market"],"/"), 1, 3);
			//$data[$x]['date'] = date('d-m-Y H:i:s', strtotime($myinputs[$x]["Timestamp"]));
			$arrdate = explode("/", $myinputs[$x]["Timestamp"], 3);
			$data[$x]['date'] = date('Y-m-d H:i:s', strtotime($arrdate[1]."/".$arrdate[0]."/".$arrdate[2]));
			//dd($data[$x]['date']);
			$data[$x]['total'] = $myinputs[$x]["Total"] - $myinputs[$x]["Fee"];
			$dateNow = date('Y-m-d H:i:s');
			$data[$x]['created_at'] = $dateNow;
			$data[$x]['updated_at'] = $dateNow;
			if($myinputs[$x]["Type"]=="Buy"){
				$data[$x]['buyorsell'] = 1;
			}else{
				$data[$x]['buyorsell'] = 0;
			}
		}
		$column_id = ledgersModel::addData($data);
		return $data;
	}
	
      /**
     * 
     * Check data consitancy for GDAX exchange history file
     * 
     **/ 
	public function checkDataGDAXFills($csvMod){
		if(array_column($csvMod, 'side')) {
			if(array_column($csvMod, 'created at')) {
				if(array_column($csvMod, 'size')) {
					if(array_column($csvMod, 'price')) {
						if(array_column($csvMod, 'size unit')) {
							if(array_column($csvMod, 'total')) {
								if(array_column($csvMod, 'price/fee/total unit')) {
									$sani = array(
												'price/fee/total unit'   => FILTER_SANITIZE_STRING,
												'side'    => FILTER_SANITIZE_STRING,
												'size unit'    => FILTER_SANITIZE_STRING,
												'trade id'    => FILTER_SANITIZE_STRING,
												'total'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'size'     => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'price' => array(
																	'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
																	'flags'  => FILTER_FLAG_ALLOW_FRACTION,
																   ),
												'created at'   => FILTER_SANITIZE_STRING
											);
											$x = 0;
											foreach($csvMod as $value)
											{
												$myinputs[$x] = filter_var_array($value, $sani);
												$x++;
											}
									$data = $this->saveGdaxDataFills($myinputs);
								}
							}
						}
					}
				}
			}
		}else{
			//error message
			echo "reror";
		}
		return $data;
	}
    
    /**
     * 
     * Save GDAX history file to DB
     * 
     **/ 
	public function saveGdaxDataFills($myinputs)
	{
		for($x = 0; $x < sizeof($myinputs); $x++)
		{
			$data[$x]['user_id'] = Auth::getUser()->id;
			$data[$x]['coins_id'] = $this->getCCYIdAbv($myinputs[$x]["size unit"]);
			$data[$x]['amount'] = $myinputs[$x]["size"];
			//-- to do dynamic
			$data[$x]['exchanges_id'] = 3;
			$data[$x]['exchangetradeid'] = $myinputs[$x]["trade id"];
			//
			$data[$x]['rate'] = $myinputs[$x]["price"];
			$data[$x]['currency_id'] = $this->getCCYIdAbv($myinputs[$x]["price/fee/total unit"]);
			$data[$x]['date'] = $myinputs[$x]["created at"];
			$data[$x]['total'] = $myinputs[$x]["total"];
			$dateNow = date('Y-m-d H:i:s');
			$data[$x]['created_at'] = $dateNow;
			$data[$x]['updated_at'] = $dateNow;
			if($myinputs[$x]["side"]=="BUY"){
				$data[$x]['buyorsell'] = 1;
			}else{
				$data[$x]['buyorsell'] = 0;
			}
		}
		$column_id = ledgersModel::addData($data);
		return $data;
	}
    
    /**
     * 
     * CCY-CCY get ID from DB from abv value
     * 
     **/ 
	public function getCCYIdAbv($abv)
	{
		$ccy = CoinsModel::where('abv',$abv)->first();
		if(!isset($ccy)){
				$ccy2 = CoinsModel::where('abv','like', '%' .$abv. '%')->first();
				if(!isset($ccy2)){
					return 0;
					}else{
						return $ccy2->id;
					}
			}else{
				return $ccy->id;
			}
	}
	  
    /**
     * 
     * Check data consitancy for GDAX exchange history file
     * 
     **/ 
	public function checkDataGDAXAccount($csvMod){
		if(array_column($csvMod, 'type')) {
			if(array_column($csvMod, 'time')) {
				if(array_column($csvMod, 'amount')) {
					if(array_column($csvMod, 'balance')) {
						if(array_column($csvMod, 'amount/balance unit')) {
							$sani = array(
										'type'   => FILTER_SANITIZE_STRING,
										'time'    => FILTER_SANITIZE_STRING,
										'amount'     => array(
															'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
															'flags'  => FILTER_FLAG_ALLOW_FRACTION,
														   ),
										'balance' => array(
															'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
															'flags'  => FILTER_FLAG_ALLOW_FRACTION,
														   ),
										'amount/balance unit'   => FILTER_SANITIZE_STRING
									);
									foreach($csvMod as $value)
									{
										$myinputs = filter_var_array($value, $sani);
										dd($myinputs);
										$this->saveGdaxDataAccount($myinputs);
									}
						}
					}
				}
			}
		}else{
			//error message
			echo "reror";
		}
	}
    
    /**
     * 
     * Save GDAX history file to DB
     * 
     **/ 
	public function saveGdaxDataAccount($myinputs)
	{
		for($x = 0; $x < sizeof($myinputs); $x++)
		{
			$data[$x]['user_id'] = Auth::getUser()->id;
			$data[$x]['coins_id'] = "Club ".$league." ".$x;
			$data[$x]['amount'] = $myinputs[$x]->amount;
			$data[$x]['exchanges_id'] = $myinputs[$x]->amount;
			$data[$x]['rate'] = $myinputs[$x]->amount;
			$data[$x]['currency_id'] = $myinputs[$x]->amount;
			$data[$x]['date'] = $myinputs[$x]->{"amount/balance unit"};
			if($myinputs[$x]->type=="Match"){
				$data[$x]['buyorsell'] = 1;
			}else{
				$data[$x]['buyorsell'] = 0;
			}
		}
		$column_id = ledgersModel::addData($data);
		return $column_id;
	}
    
    /**
     * 
     * Convert csv to array
     * 
     **/ 
	public function assoc_getcsv($csv_path) {
		$r = array_map('str_getcsv', file($csv_path));
		foreach( $r as $k => $d ) { $r[$k] = array_combine($r[0], $r[$k]); }
		return array_values(array_slice($r,1));
	}
}
