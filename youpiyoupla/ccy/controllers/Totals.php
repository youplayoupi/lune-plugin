<?php namespace Youpiyoupla\Ccy\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Db;
use youpiyoupla\ccy\Models\Totals as TotalsModel;
use youpiyoupla\ccy\Models\Buys as BuysModel;
use youpiyoupla\ccy\Models\Sells as SellsModel;

class Totals extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController'    ];
    
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
    }
    
    public static function updateOrCreate($entry, $rate1, $rate2){
		//if coins for specified user already exist, update
		//rate avg = (rate DB * total DB + rate current*total current)/(total DB + total current)
		//total USD = total USD DB - current total USD
		$coin = Db::table('youpiyoupla_ccy_total')
		->where('coins_id', $entry->coins_id)
		->where('user_id', $entry->user_id)
		->first();
		$currency = Db::table('youpiyoupla_ccy_total')
		->where('coins_id', $entry->currency_id)
		->where('user_id', $entry->user_id)
		->first();
		
		if($coin!=null){
			if($coin->total+$entry->total != 0){
				$totaldivide = $coin->total+$entry->total;
			}else{
				$totaldivide = 1;
			}
			$rate_avg = (($coin->total*$coin->rate_avg) + ($rate1*$entry->total))/$totaldivide;
			$amount_usd = $coin->amount_usd + ($rate1*$entry->total);
			//if buy
			if($entry->buyorsell){
				//get buy data
				$buy = Db::table('youpiyoupla_ccy_buy')
				->where('coins_id', $entry->coins_id)
				->where('user_id', $entry->user_id)
				->first();
				
				//update total table
				$newTotal = $coin->total + $entry->total;
				Db::table('youpiyoupla_ccy_total')
				->where('user_id', $entry->user_id)
				->where('coins_id', $entry->coins_id)
				->update(['total' => $newTotal], ['amount_usd' => $amount_usd], ['rate_avg' => $rate_avg], ['updated_at' => $entry->updated_at]);
				
				//update buy table
				if($buy!=null){
					$newbuy = $buy->total + $entry->total;
					$buyrate_avg = (($buy->total*$buy->rate_avg) + ($rate1*$entry->total))/($buy->total+$entry->total);
					$buyamount_usd = $buy->amount_usd + ($rate1*$entry->total);
					Db::table('youpiyoupla_ccy_buy')
					->where('user_id', $entry->user_id)
					->where('coins_id', $entry->coins_id)
					->update(['total' => $newbuy], ['amount_usd' => $buyamount_usd], ['rate_avg' => $buyrate_avg], ['updated_at' => $entry->updated_at]);
				}else{
					$datab = new BuysModel();
					$datab->user_id = $entry->user_id;
					$datab->coins_id = $entry->coins_id;
					$datab->total = $entry->total;
					$datab->created_at = $entry->created_at;
					$datab->updated_at = $entry->updated_at;
					$datab->rate_avg = $rate1;
					$datab->amount_usd = $rate1*$entry->total;
					$datab->save();
				}
			}
			//if sell
			else{
				//get sell data
				$sell = Db::table('youpiyoupla_ccy_sell')
				->where('coins_id', $entry->coins_id)
				->where('user_id', $entry->user_id)
				->first();
				
				//update total table
				$newTotal = $coin->total - $entry->total;
				Db::table('youpiyoupla_ccy_total')
				->where('user_id', $entry->user_id)
				->where('coins_id', $entry->coins_id)
				->update(['total' => $newTotal], ['amount_usd' => $amount_usd], ['rate_avg' => $rate_avg], ['updated_at' => $entry->updated_at]);
				
				//update sell table
				if($sell!=null){
					$newsell = $sell->total + $entry->total;
					$sellrate_avg = (($sell->total*$sell->rate_avg) + ($rate1*$entry->total))/($sell->total+$entry->total);
					$sellamount_usd = $sell->amount_usd + ($rate1*$entry->total);
					Db::table('youpiyoupla_ccy_sell')
					->where('user_id', $entry->user_id)
					->where('coins_id', $entry->coins_id)
					->update(['total' => $newsell], ['amount_usd' => $sellamount_usd], ['rate_avg' => $sellrate_avg], ['updated_at' => $entry->updated_at]);
				}else{
					$datas = new SellsModel();
					$datas->user_id = $entry->user_id;
					$datas->coins_id = $entry->coins_id;
					$datas->total = $entry->total;
					$datas->created_at = $entry->created_at;
					$datas->updated_at = $entry->updated_at;
					$datas->rate_avg = $rate1;
					$datas->amount_usd = $rate1*$entry->total;
					$datas->save();
				}
			}
		//if coins does not exist, create
		}else{
			//create total
			$data = new TotalsModel();
            $data->user_id = $entry->user_id;
            $data->coins_id = $entry->coins_id;
            $data->total = $entry->total;
			$data->created_at = $entry->created_at;
			$data->updated_at = $entry->updated_at;
			$data->rate_avg = $rate1;
			$data->amount_usd = $rate1*$entry->total;
			$data->save();
			//create buy && create sell
			if($entry->buyorsell){
				//buy = date
				$datab = new BuysModel();
				$datab->user_id = $entry->user_id;
				$datab->coins_id = $entry->coins_id;
				$datab->total = $entry->total;
				$datab->created_at = $entry->created_at;
				$datab->updated_at = $entry->updated_at;
				$datab->rate_avg = $rate1;
				$datab->amount_usd = $rate1*$entry->total;
				$datab->save();
				//sell = 0
				/**$datas = new SellsModel();
				$datas->user_id = $entry->user_id;
				$datas->coins_id = $entry->coins_id;
				$datas->total = 0;
				$datas->created_at = $entry->created_at;
				$datas->updated_at = $entry->updated_at;
				$datas->rate_avg = 0;
				$datas->amount_usd = 0;
				$datas->save();**/
				
			}else{
				//buy = 0
				/**$datab = new BuysModel();
				$datab->user_id = $entry->user_id;
				$datab->coins_id = $entry->coins_id;
				$datab->total = 0;
				$datab->created_at = $entry->created_at;
				$datab->updated_at = $entry->updated_at;
				$datab->rate_avg = 0;
				$datab->amount_usd = 0;
				$datab->save();**/
				//sell = data
				$datas = new SellsModel();
				$datas->user_id = $entry->user_id;
				$datas->coins_id = $entry->coins_id;
				$datas->total = $entry->total;
				$datas->created_at = $entry->created_at;
				$datas->updated_at = $entry->updated_at;
				$datas->rate_avg = $rate1;
				$datas->amount_usd = $rate1*$entry->total;
				$datas->save();
				
			}
		}
		if($currency!=null){
			if($coin->total+$entry->rate != 0){
				$totaldivide = $coin->total+$entry->rate;
			}else{
				$totaldivide = 1;
			}
			$rate_avg = (($currency->total*$currency->rate_avg) + ($rate2*$entry->rate))/$totaldivide;
			$amount_usd = $currency->amount_usd + ($rate2*$entry->rate);
			//if buy coin 1 sell coin 2
			if($entry->buyorsell){
				//get sell data
				$sell = Db::table('youpiyoupla_ccy_sell')
				->where('coins_id', $entry->coins_id)
				->where('user_id', $entry->user_id)
				->first();
				//update total
				$newTotal = $currency->total - $entry->rate;
				
				/**
				var_dump($coin->total);
				var_dump($entry->rate);
				dd($newTotal);
				**/
				Db::table('youpiyoupla_ccy_total')
				->where('user_id', $entry->user_id)
				->where('coins_id', $entry->currency_id)
				->update(['total' => $newTotal], ['amount_usd' => $amount_usd], ['rate_avg' => $rate_avg], ['updated_at' => $entry->updated_at]);
				
				//update sell table
				if($sell!=null){
					$newsell = $sell->total + $entry->rate;
					$sellrate_avg = (($sell->total*$sell->rate_avg) + ($rate2*$entry->rate))/($sell->total+$entry->rate);
					$sellamount_usd = $sell->amount_usd + ($rate2*$entry->rate);
					Db::table('youpiyoupla_ccy_sell')
					->where('user_id', $entry->user_id)
					->where('coins_id', $entry->currency_id)
					->update(['total' => $newsell], ['amount_usd' => $sellamount_usd], ['rate_avg' => $sellrate_avg], ['updated_at' => $entry->updated_at]);
				}else{
					$datas = new SellsModel();
					$datas->user_id = $entry->user_id;
					$datas->coins_id = $entry->currency_id;
					$datas->total = $entry->rate;
					$datas->created_at = $entry->created_at;
					$datas->updated_at = $entry->updated_at;
					$datas->rate_avg = $rate1;
					$datas->amount_usd = $rate1*$entry->rate;
					$datas->save();
				}
			}
			//if sell coin 1 buy coin 2
			else{
				//get buy data
				$buy = Db::table('youpiyoupla_ccy_buy')
				->where('coins_id', $entry->coins_id)
				->where('user_id', $entry->user_id)
				->first();
				
				//update total
				$newTotal = $coin->total + $entry->rate;
				Db::table('youpiyoupla_ccy_total')
				->where('user_id', $entry->user_id)
				->where('coins_id', $entry->currency_id)
				->update(['total' => $newTotal], ['amount_usd' => $amount_usd], ['rate_avg' => $rate_avg], ['updated_at' => $entry->updated_at]);
				
				//update buy table
				if($buy!=null){
					$newbuy = $buy->total + $entry->rate;
					$buyrate_avg = (($buy->total*$buy->rate_avg) + ($rate2*$entry->rate))/($buy->total+$entry->rate);
					$buyamount_usd = $buy->amount_usd + ($rate2*$entry->rate);
					Db::table('youpiyoupla_ccy_buy')
					->where('user_id', $entry->user_id)
					->where('coins_id', $entry->currency_id)
					->update(['total' => $newbuy], ['amount_usd' => $buyamount_usd], ['rate_avg' => $buyrate_avg], ['updated_at' => $entry->updated_at]);
				}else{
					$datab = new BuysModel();
					$datab->user_id = $entry->user_id;
					$datab->coins_id = $entry->currency_id;
					$datab->total = $entry->rate;
					$datab->created_at = $entry->created_at;
					$datab->updated_at = $entry->updated_at;
					$datab->rate_avg = $rate1;
					$datab->amount_usd = $rate1*$entry->rate;
					$datab->save();
				}
			}
		//if coins does not exist, create
		}else{
			$data = new TotalsModel();
            $data->user_id = $entry->user_id;
            $data->coins_id = $entry->currency_id;
            $data->total = -($entry->rate);
			$data->created_at = $entry->created_at;
			$data->updated_at = $entry->updated_at;
			$data->rate_avg = $rate2;
			$data->amount_usd = -($rate2*$entry->rate);
			$data->save();
			
			//create buy && create sell
			if($entry->buyorsell){
				//buy = date
				$data = new SellsModel();
				$data->user_id = $entry->user_id;
				$data->coins_id = $entry->currency_id;
				$data->total = $entry->rate;
				$data->created_at = $entry->created_at;
				$data->updated_at = $entry->updated_at;
				$data->rate_avg = $rate2;
				$data->amount_usd = $rate2*$entry->rate;
				$data->save();
				//sell = 0
				/**$data = new BuysModel();
				$data->user_id = $entry->user_id;
				$data->coins_id = $entry->currency_id;
				$data->total = 0;
				$data->created_at = $entry->created_at;
				$data->updated_at = $entry->updated_at;
				$data->rate_avg = 0;
				$data->amount_usd = 0;
				$data->save();**/
				
			}else{
				//buy = 0
				/**$data = new SellsModel();
				$data->user_id = $entry->user_id;
				$data->coins_id = $entry->currency_id;
				$data->total = 0;
				$data->created_at = $entry->created_at;
				$data->updated_at = $entry->updated_at;
				$data->rate_avg = 0;
				$data->amount_usd = 0;
				$data->save();**/
				//sell = data
				$data = new BuysModel();
				$data->user_id = $entry->user_id;
				$data->coins_id = $entry->currency_id;
				$data->total = $entry->rate;
				$data->created_at = $entry->created_at;
				$data->updated_at = $entry->updated_at;
				$data->rate_avg = $rate2;
				$data->amount_usd = $rate2*$entry->rate;
				$data->save();
				
			}
		}
		return 1;
	}
}
