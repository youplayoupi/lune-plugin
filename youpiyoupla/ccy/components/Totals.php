<?php namespace youpiyoupla\ccy\Components;

use Auth;
use Input;
use File;
use Request;
use Storage;
use Redirect;
use Paginator;
use Cms\Classes\Page;
use October\Rain\Exception\ApplicationException;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use RainLab\Forum\Models\Member as MemberModel;
use youpiyoupla\ccy\Models\Ledgers as LedgersModel;
use youpiyoupla\ccy\Models\Totals as TotalsModel;
use youpiyoupla\ccy\Models\Coins as CoinsModel;
use youpiyoupla\ccy\Models\Exchanges as ExchangesModel;
use youpiyoupla\ccy\Controllers\Totals as totalCtrl;
use youpiyoupla\ccy\Models\Apis as ApisModel;
use youpiyoupla\ccy\Controllers\Apis as ApisCtrl;

class Totals extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Totals component',
            'description' => 'Display list of coin total',
        ];
    }
    
	public function onRun()
	{
        try {
			if (!$user = Auth::getUser()) {
			   return Redirect::to('account')->with('message', 'Need to login');
			}
			$this->page['time'] = 1;
			//get coin ID if provided -- not required
			$coin_id = $this->param('coin_id');
			$user = Auth::getUser();
			$list = TotalsModel::getDataCoins($user->id);
			$apisM = new ApisModel();
			$apilist = $apisM->getApisListUser($user->id);
			if (!empty($coin_id)){
				if(isset($apilist)){
					foreach($apilist as $api){
						if($api->active){
							switch($api->name){
								case "Cryptopia":
								$this->page['cryptopia']=$this->cryptopiaData($coin_id);
								if($this->page['cryptopia']!=0){		
									$this->page['cryptopiaCheck'] = 1;
								}
								break;
								case "Binance":
								$this->page['binance']=$this->binanceData($coin_id);
								if($this->page['binance']!=0){		
									$this->page['binanceCheck'] = 1;
								}
								break;
								case "HitBTC":
								
								break;
								case "GDAX":
								$this->page['GDAX']=$this->GDAXData($coin_id);
								if($this->page['GDAX']!=0){		
									$this->page['GDAXCheck'] = 1;
								}
								break;
								
							}
						}
					}
				}
			}
			$this->page['apis'] = $apilist;
			if (!empty($coin_id)){
				//if provided get coin data and display it
				$this->page['coinId'] = 1;
				//dd(LedgersModel::getDataCoin($coin_id));
				$this->page['coinLedgers'] = LedgersModel::getDataCoinUser($coin_id, $user->id);
				$this->page['coinTotals'] = TotalsModel::getDataCoinUser($coin_id, $user->id);
			}
			else{
				$this->page['coinId'] = 0;
				$data = $this->getmarketPrice($list);
				/*$coins = coinsModel::get();
				$this->page['coins'] = $coins;	
				$exchanges = exchangesModel::get();
				$this->page['exchanges'] = $exchanges; */
				$this->page['lists'] = $data[0];
			}
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}
	
	public function getmarketPrice($coins){
		$apiData = ApisCtrl::coinmarketcap_api_query();
		$item = array();
		$counter = 0;
		$totalUSD = 0;
		$totalGlob  = 0;
		$coinSupZero = 0;
		
		foreach($coins as $coin) {
			for($x = 0; $x < sizeof($apiData); $x++){
				if ($coin->abv == $apiData[$x]['symbol']) {
					$item[$counter]['symbol'] = $apiData[$x]['symbol'];
					$item[$counter]['coins_id'] = $coin->id;
					$item[$counter]['icon'] = $coin->icon;
					$item[$counter]['price_usd'] = $apiData[$x]['price_usd'];
					$item[$counter]['totalprice_usd'] = $apiData[$x]['price_usd']*$coin->total;
					$item[$counter]['price_btc'] = $apiData[$x]['price_btc'];
					$item[$counter]['percent_change_1h'] = $apiData[$x]['percent_change_1h'];
					$item[$counter]['percent_change_24h'] = $apiData[$x]['percent_change_24h'];
					$item[$counter]['percent_change_7d'] = $apiData[$x]['percent_change_7d'];
					$item[$counter]['total'] = $coin->total;
					/*if(($x == 0)&&($coin->total>0)){
						$totalUSD = $apiData[$x]['price_usd']*$coin->total;
						$totalGlob = $coin->total;
						$coinSupZero = 1;
					}*/
					if ($coin->total>0){
						$totalUSD = $totalUSD + $apiData[$x]['price_usd']*$coin->total;
						$totalGlob  = $totalGlob  + $coin->total;
						$coinSupZero = $coinSupZero+1;
					}
					$counter++;
					//break;
				}
			}
		}
		return array($item, $totalUSD, $totalGlob, $coinSupZero);
	}
	
	public function GDAXData($coin_id){
		$api = new ApisCtrl();
		$tradePairs = $api->gdax_api_query_nokey("products");
		//dd($tradePairs);
		$coinAbv = CoinsModel::where('id',$coin_id)->pluck('abv');
		$counterSym = 0;
		$counterBase = 0;
		$data = array();
		for($x=0; $x<sizeof($tradePairs);$x++){
			if(($tradePairs[$x]["base_currency"] == $coinAbv[0])||($tradePairs[$x]["quote_currency"] == $coinAbv[0])){
				$data[$counterSym] = $api->gdax_api_query_nokey("products/".$tradePairs[$x]["id"]."/book?level=2");
				$data[$counterSym]["pair"] = $tradePairs[$x]["id"];
				$counterSym++;
			}
		}
		if(isset($data[0])){
			return $data;
		}else{
			return 0;
		}
	}
	
	public function binanceData($coin_id){
		$apiBinance = new ApisCtrl();
		$tradePairs = $apiBinance->bookPrices();
		$coinAbv = CoinsModel::where('id',$coin_id)->pluck('abv');
		$counterSym = 0;
		$counterBase = 0;
		$data = array();
		foreach($tradePairs as $key => $value){
			$arr[0] = substr($key, 0, 3);
			$arr[1] = substr($key, -3);
			if(($arr[0] == $coinAbv[0])||($arr[1] == $coinAbv[0])){
				$data[$counterSym] = $apiBinance->depth($key, 10);
				$data[$counterSym]["pair"] = $key;
				$counterSym++;
			}
		}
		if(isset($data[0])){
			return $data;
		}else{
			return 0;
		}
	}
	
	public function cryptopiaData($coin_id){
		//get all trading pair or check all traiding pair with current coin
		$tradePairs = ApisCtrl::cryptopia_api_query("GetTradePairs");
		$coinAbv = CoinsModel::where('id',$coin_id)->pluck('abv');
		$counterSym = 0;
		$counterBase = 0;
		$data = array();
		
		foreach($tradePairs["Data"] as $pair){
			if($pair["Symbol"]==$coinAbv["0"]){
				$pairsSym[$counterSym] = $pair["BaseSymbol"];
				$counterSym++;
			}elseif($pair["BaseSymbol"]==$coinAbv["0"]){
				$pairsBase[$counterBase] = $pair["Symbol"];
				$counterBase++;
			}
		}
		//get open market coin_id/trading pair
		$requestVar = "";
		for($x=0;$x<$counterSym;$x++){
			if($x==($counterSym-1)){
				if($counterBase!=0){
					$requestVar = $requestVar.$coinAbv["0"]."_".$pairsSym[$x]."-";
				}else{
					$requestVar = $requestVar.$coinAbv["0"]."_".$pairsSym[$x];
				}
			}else{
				$requestVar = $requestVar.$coinAbv["0"]."_".$pairsSym[$x]."-";	
			}		
		}
		for($y=0;$y<$counterBase;$y++){
			if($y==($counterBase-1)){
				$requestVar = $requestVar.$pairsBase[$y]."_".$coinAbv["0"];
			}else{
				$requestVar = $requestVar.$pairsBase[$y]."_".$coinAbv["0"]."-";
			}		
		}
		//dd($requestVar);
		$requestVar = $requestVar."/10";
		$data = ApisCtrl::cryptopia_api_query("GetMarketOrderGroups", array( 'Market'=> $requestVar ) );
		//check error
		if(isset($data["Data"])){
			return $data["Data"];
		}else{
			return 0;
		}
	}
	
	function onRefreshTime()
	{
		$this->page['time'] = 10;
		return [
			'#myDiv' => $this->renderPartial('Totals::mytime')
		];
	}
}
