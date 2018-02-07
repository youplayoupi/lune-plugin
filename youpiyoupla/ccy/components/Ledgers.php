<?php namespace youpiyoupla\ccy\Components;

use Auth;
use BackendAuth;
use Input;
use File;
use Request;
use Storage;
use Flash;
use MakeRedirect;
use Redirect;
use Paginator;
use Cms\Classes\Page;
use October\Rain\Exception\ApplicationException;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use RainLab\Forum\Models\Member as MemberModel;
use youpiyoupla\ccy\Models\Ledgers as LedgersModel;
use youpiyoupla\ccy\Models\Totals as TotalsModel;
use youpiyoupla\ccy\Models\Buys as BuysModel;
use youpiyoupla\ccy\Models\Sells as SellsModel;
use youpiyoupla\ccy\Models\Coins as CoinsModel;
use youpiyoupla\ccy\Models\Exchanges as ExchangesModel;
use youpiyoupla\ccy\Controllers\Totals as totalCtrl;
use youpiyoupla\ccy\Controllers\Apis as ApisCtrl;

class Ledgers extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Ledgers component',
            'description' => 'Display list of Ledgers',
        ];
    }
    
	public function onRun()
	{
		$this->listItems();
	}
	
    protected function listItems()
    {
        try {
            if (!$user = Auth::getUser()) {
               return Redirect::to('account')->with('message', 'Need to login');
            }

			$adminLog =  BackendAuth::getUser();
			if(isset($adminLog)){
				if($adminLog->attributes["role_id"] == 2)
				{
					$this->page['admin'] = 1;
				}
			}
			else{
				$this->page['admin'] = 0;
			}
			$user = Auth::getUser();
			$list = LedgersModel::getDataPaginatedUser($user->id);
			$coins = coinsModel::orderBy('name', 'asc')->get();
			$this->page['coins'] = $coins;	
			$exchanges = exchangesModel::get();
			$this->page['exchanges'] = $exchanges;
			$this->page['lists'] = $list; 
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
		return $this->renderPartial('@default');
    }
	
    public function onDelete()
    {
		$user = Auth::getUser();
		//get ledger
		$line = LedgersModel::where('id', Input::get('list_id'))->first();
		
		//if exist
		if(isset($line)){
			//if user post == user ledger
			if($line->user_id == $user->id){	
			//if buy
				if($line->buyorsell){
					$lineBuy = BuysModel::where('coins_id', $line->coins_id)->first();
					$lineSell = SellsModel::where('coins_id', $line->currency_id)->first();
					//update total
					TotalsModel::where('user_id', $user->id)
								->where('coins_id', $line->coins_id)
								->decrement('total', floatval($line->amount));
					BuysModel::where('user_id', $user->id)
								->where('coins_id', $line->coins_id)
								->decrement('total', floatval($line->amount));
					BuysModel::where('user_id', $user->id)
								->where('coins_id', $line->coins_id)
								->decrement('amount_usd', floatval($lineBuy->rate_avg)*floatval($line->amount));
					TotalsModel::where('user_id', $user->id)
								->where('coins_id', $line->currency_id)
								->increment('total', floatval($line->total));
					SellsModel::where('user_id', $user->id)
								->where('coins_id', $line->currency_id)
								->decrement('total', floatval($line->total));
					SellsModel::where('user_id', $user->id)
								->where('coins_id', $line->currency_id)
								->decrement('amount_usd', floatval($lineSell->rate_avg)*floatval($line->total));
				//update buy
				}else{
					$lineBuy = BuysModel::where('coins_id', $line->currency_id)->first();
					$lineSell = SellsModel::where('coins_id', $line->coins_id)->first();
					//if sell
					//update total
					TotalsModel::where('user_id', $user->id)
								->where('coins_id', $line->coins_id)
								->increment('total', floatval($line->amount));
					SellsModel::where('user_id', $user->id)
								->where('coins_id', $line->coins_id)
								->decrement('total', floatval($line->amount));
					SellsModel::where('user_id', $user->id)
								->where('coins_id', $line->coins_id)
								->decrement('amount_usd', floatval($lineSell->rate_avg)*floatval($line->amount));
					TotalsModel::where('user_id', $user->id)
								->where('coins_id', $line->currency_id)
								->decrement('total', floatval($line->total));
					BuysModel::where('user_id', $user->id)
								->where('coins_id', $line->currency_id)
								->decrement('total', floatval($line->total));
					BuysModel::where('user_id', $user->id)
								->where('coins_id', $line->currency_id)
								->decrement('amount_usd', floatval($lineBuy->rate_avg)*floatval($line->total));
				}
				
				//delete ledger
				$model = LedgersModel::where('id', Input::get('list_id'))->forceDelete();
				\Flash::success("Team deleted successfully");
			}
		}
    }
	
	public function importCoins()
    {
        try {
            if (!$user = BackendAuth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }
            //get all coins from cryptocompare
            $coins = ApisCtrl::cryptocompare_coinList();
            //get current coins in DB
            $currentCoins = coinsModel::all();
            $curCoins = array();

			//if we already have coins in DB
			if(isset($currentCoins)){
				$counterc = 0;
				 foreach($currentCoins as $coin){
					if(isset($coin->icon)){
						$curCoins[$counterc]['icon'] = $coin->icon;
					}
					$curCoins[$counterc]['name'] = $coin->name;
					$curCoins[$counterc]['abv'] = $coin->abv;
					$counterc++;
				}
				
				$counter = 0;
				foreach($coins["Data"] as $coin){
					//$entry = new coinsModel();
					if(isset($coin["ImageUrl"])){
						$newCoins[$counter]['icon'] = "https://www.cryptocompare.com/".$coin["ImageUrl"];
					}
					$newCoins[$counter]['name'] = $coin["CoinName"];
					$newCoins[$counter]['abv'] = $coin["Symbol"];
					$counter++;
				}
				$result = array_filter($newCoins, function ($element) use ($curCoins) {
					return !in_array($element, $curCoins);
				});
				
				if(isset($result)){
					foreach($result as $coin){
						$entry = new coinsModel();
						if(isset($coin["icon"])){
							$entry->icon = $coin["icon"];
						}
						$entry->name = $coin["name"];
						$entry->abv = $coin["abv"];
						$entry->save();
					}
				}
			}else{
				foreach($coins["Data"] as $coin){
					$entry = new coinsModel();
					if(isset($coin["ImageUrl"])){
						$entry->icon = "https://www.cryptocompare.com/".$coin["ImageUrl"];
					}
					$entry->name = $coin["CoinName"];
					$entry->abv = $coin["Symbol"];
					$entry->save();
				}				
			}
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
		
	}
	
	public function onDelAll()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }
            
            $deletedLedgers = LedgersModel::where('user_id', $user->id)->forceDelete();
            $deletedBuys = BuysModel::where('user_id', $user->id)->forceDelete();
            $deletedSells = SellsModel::where('user_id', $user->id)->forceDelete();
            $deletedTotals = TotalsModel::where('user_id', $user->id)->forceDelete();
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
		
	}

    /**
    * 
    * adding manual entry
    * 
    **/ 
	public function onCreate()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }
            $entry = new ledgersModel();
            $entry->user_id = $user->id;
            $entry->coins_id = Input::get('coin');
            $entry->total = Input::get('amount');
            $entry->buyorsell = Input::get('buy');
            $entry->rate = $entry->total/Input::get('rate');
            $entry->amount = Input::get('rate');
            $date = strtotime(Input::get('date'));
            $entry->date = date('Y-m-d H:i:s', $date);
            //$entry->date = Input::get('date');
			$dateNow = date('Y-m-d H:i:s');
			$entry->created_at = $dateNow;
			$entry->updated_at = $dateNow;
            $entry->exchanges_id = Input::get('exchange');
            $entry->currency_id = Input::get('currency');
            $entry->save();
            
            //calculate USD rate & value
            $ccy1 = CoinsModel::where('id', $entry->coins_id)->get();
            $rate1 = ApisCtrl::cryptocompare_api_query($ccy1[0]->abv, $date);
            $ccy2 = CoinsModel::where('id', $entry->currency_id)->get();
            
            $rate2 = ApisCtrl::cryptocompare_api_query2($ccy2[0]->abv, $date);
            
            //update total, buy & sell tables
            //to be moved to ledger ctrl instead of total ctrl
            $sumup = totalCtrl::updateOrCreate($entry, $rate1[$ccy1[0]->abv]['USD'], $rate2[$ccy2[0]->abv]['USD']);
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}

}
