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
use youpiyoupla\ccy\Models\Accountoptions as AccountoptionsModel;
use youpiyoupla\ccy\Models\Coins as CoinsModel;
use youpiyoupla\ccy\Models\Exchanges as ExchangesModel;
use youpiyoupla\ccy\Controllers\Accountoptions as totalCtrl;

class Accountoptions extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Accountoptions component',
            'description' => 'Display list of coin total',
        ];
    }
    
	public function onRun()
	{
        try {
			if (!$user = Auth::getUser()) {
			   return Redirect::to('account')->with('message', 'Need to login');
			}

			//get coin ID if provided -- not required
			$coin_id = $this->param('coin_id');
			$user = Auth::getUser();
			$list = AccountoptionsModel::getDataPaginated($user->id);
			if (!empty($coin_id)){
				//if provided get coin data and display it
				$this->page['coinId'] = 1;
				//dd(LedgersModel::getDataCoin($coin_id));
				$this->page['coinLedgers'] = LedgersModel::getDataCoinUser($coin_id, $user->id);
				$this->page['coinAccountoptions'] = AccountoptionsModel::getDataCoinUser($coin_id, $user->id);
			}
			else{
				$this->page['coinId'] = 0;
				/*$coins = coinsModel::get();
				$this->page['coins'] = $coins;	
				$exchanges = exchangesModel::get();
				$this->page['exchanges'] = $exchanges; */
				$this->page['lists'] = $list;
			}
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}

}
