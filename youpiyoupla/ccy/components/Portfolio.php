<?php namespace youpiyoupla\ccy\Components;

use Auth;
use Request;
use Redirect;
use Paginator;
use Cms\Classes\Page;
use October\Rain\Exception\ApplicationException;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use youpiyoupla\ccy\Models\Totals as TotalsModel;
use youpiyoupla\ccy\Models\Buys as BuysModel;
use youpiyoupla\ccy\Models\Sells as SellsModel;
use youpiyoupla\ccy\Models\Coins as CoinsModel;
use youpiyoupla\ccy\Controllers\Apis as ApisCtrl;

class Portfolio extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Portfolio component',
            'description' => 'Display portfolio',
        ];
    }
    
	public function onRun()
	{
        try {
			if (!$user = Auth::getUser()) {
			   return Redirect::to('account')->with('message', 'Need to login');
			}

			$user = Auth::getUser();
			$coinsTotal = TotalsModel::getDataCoins($user->id);
			$coinsBuy = BuysModel::getDataCoins($user->id);
			$coinsSell = SellsModel::getDataCoins($user->id);
			$apiData = ApisCtrl::coinmarketcap_api_query();
			$mappingsTotal = $this->mapCoinApiTotal($coinsTotal, $apiData);
			$mappingsBuy = $this->mapCoinApiBuy($coinsBuy, $mappingsTotal[0]);
			$mappingsSell = $this->mapCoinApiSell($coinsSell, $mappingsBuy[0]);

			usort($mappingsSell[0], function($a,$b) {
				if($a['totalprice_usd'] == $b['totalprice_usd']) return 0;
				return ($a['totalprice_usd'] > $b['totalprice_usd']) ? -1 : 1;
			});	
				
			$this->page['coinTotals'] = $coinsTotal;
			$this->page['mappings'] = $mappingsSell[0];
			$this->page['totalUSD'] = $mappingsTotal[1];
			$this->page['totalBuyUSD'] = $mappingsBuy[1];
			$this->page['totalSellUSD'] = $mappingsSell[1];
			$this->page['totalGlob'] = $mappingsTotal[2];
			$this->page['graph1s'] =$this->genGraph1($mappingsTotal[0], $mappingsTotal[2], $mappingsTotal[3]);
			$this->page['graph2s'] =$this->genGraph2($mappingsTotal[0], $mappingsTotal[1], $mappingsTotal[3]);
			$this->page['graph3s'] =$this->genGraph3($mappingsSell[0], $mappingsTotal[1], $mappingsTotal[3]);
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}
	
	public function mapCoinApiBuy($coinsBuy, $mappingsTotal){
		$totalBuyUSD = 0;
		
		foreach($coinsBuy as $coin) {
			for($x = 0; $x < sizeof($mappingsTotal); $x++){
				if ($coin->abv == $mappingsTotal[$x]['symbol']) {
					$mappingsTotal[$x]['BuyUSD'] = $coin->amount_usd;
					$totalBuyUSD = $totalBuyUSD + $coin->amount_usd;
				}
			}
		}
		return array($mappingsTotal, $totalBuyUSD);
	}
	
	public function mapCoinApiSell($coinsSell, $mappingsTotal){
		$totalSellUSD = 0;
		foreach($coinsSell as $coin) {
			for($x = 0; $x < sizeof($mappingsTotal); $x++){
				if ($coin->abv == $mappingsTotal[$x]['symbol']) {
					$mappingsTotal[$x]['SellUSD'] = $coin->amount_usd;
					$totalSellUSD = $totalSellUSD + $coin->amount_usd;
				}
			}
		}
		return array($mappingsTotal, $totalSellUSD);
	}
	
	public function mapCoinApiTotal($coins, $apiData)
	{
		$item = array();
		$counter = 0;
		$totalUSD = 0;
		$totalGlob  = 0;
		$coinSupZero = 0;
		
		foreach($coins as $coin) {
			for($x = 0; $x < sizeof($apiData); $x++){
				if ($coin->abv == $apiData[$x]['symbol']) {
					$item[$counter]['symbol'] = $apiData[$x]['symbol'];
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
	
	//pie chart total coin
	public function genGraph1($mappings, $totalGlob, $coinSupZero){
		$item = array();
		$counter = 0;
		foreach($mappings as $mapping) {
			if(($mapping['total']>0)&&($counter < $coinSupZero - 1)){
				$item[$counter]['js'] = '{ y: '.$mapping['total']/$totalGlob*100 . ', label: "'.$mapping['symbol'] .'"},';
				$counter++;
			}elseif ($mapping['total']>0){
				$item[$counter]['js'] = '{ y: '.$mapping['total']/$totalGlob*100 . ', label: "'.$mapping['symbol'] .'"}';
				$counter++;
			}
		}
		return $item;
	}
	
	//pie chart current USD value
	public function genGraph2($mappings, $totalGlob, $coinSupZero){
		$item = array();
		$counter = 0;
		foreach($mappings as $mapping) {
			if(($mapping['totalprice_usd']>0)&&($counter < $coinSupZero - 1)){
				$item[$counter]['js'] = '{ y: '.$mapping['totalprice_usd']/$totalGlob*100 . ', label: "'.$mapping['symbol'] .'"},';
				$counter++;
			}elseif ($mapping['totalprice_usd']>0){
				$item[$counter]['js'] = '{ y: '.$mapping['totalprice_usd']/$totalGlob*100 . ', label: "'.$mapping['symbol'] .'"}';
				$counter++;
			}
		}
		return $item;
	}
	
	//bar chart
	public function genGraph3($mappings, $totalGlob, $coinSupZero){
		$item = array();
		$counter = 0;
		foreach($mappings as $mapping) {
			if($counter < 8){
				if(($mapping['totalprice_usd']>0)&&($counter < $coinSupZero - 1)){
					$item[$counter]['js1'] = '{ y: '.$mapping['totalprice_usd'] . ', label: "'.$mapping['symbol'] .'"},';
				}elseif ($mapping['totalprice_usd']>0){
					$item[$counter]['js1'] = '{ y: '.$mapping['totalprice_usd'] . ', label: "'.$mapping['symbol'] .'"}';
				}
				if(isset($mapping['SellUSD'])){
					if($counter < $coinSupZero - 1){
						$item[$counter]['js2'] = '{ y: '.$mapping['SellUSD'] . ', label: "'.$mapping['symbol'] .'"},';
					}else{
						$item[$counter]['js2'] = '{ y: '.$mapping['SellUSD'] . ', label: "'.$mapping['symbol'] .'"}';
					}
				}else{
					if($counter < $coinSupZero - 1){
						$item[$counter]['js2'] = '{ y: 0, label: "'.$mapping['symbol'] .'"},';
					}else{
						$item[$counter]['js2'] = '{ y: 0, label: "'.$mapping['symbol'] .'"}';
					}
				}
				if(isset($mapping['BuyUSD'])){
					if($counter < $coinSupZero - 1){
						$item[$counter]['js3'] = '{ y: '.$mapping['BuyUSD'] . ', label: "'.$mapping['symbol'] .'"},';
					}else{
						$item[$counter]['js3'] = '{ y: '.$mapping['BuyUSD'] . ', label: "'.$mapping['symbol'] .'"}';
					}
				}else{
					if($counter < $coinSupZero - 1){
						$item[$counter]['js3'] = '{ y: 0, label: "'.$mapping['symbol'] .'"},';
					}else{
						$item[$counter]['js3'] = '{ y: 0, label: "'.$mapping['symbol'] .'"}';
					}
				}
				$counter++;
			}
		}
		return $item;
	}
}
