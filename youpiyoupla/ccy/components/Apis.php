<?php namespace youpiyoupla\ccy\Components;

use Auth;
use Input;
use Request;
use Redirect;
use Flash;
use Cms\Classes\Page;
use October\Rain\Exception\ApplicationException;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use RainLab\Forum\Models\Member as MemberModel;
use youpiyoupla\ccy\Models\Apis as ApisModel;
use youpiyoupla\ccy\Controllers\Apis as ApisCtrl;
use youpiyoupla\ccy\Models\Exchanges as ExchangesModel;

class Apis extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Apis component',
            'description' => 'Display list of Apis',
        ];
    }
    
	public function onRun()
	{
        try {
            if (!$user = Auth::getUser()) {
               return Redirect::to('account')->with('message', 'Need to login');
            }

			$apis = new ApisModel();
			$exchanges = ExchangesModel::get();
			$this->page['exchanges'] = $exchanges;
			$apilist = $apis ->getApisUser($user->id);
			$this->page['apis'] = $apilist;
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}

	public function onUpdateApi()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }

            //
            $ccy = "ETH";
            $time = strtotime("now");
            $data = ApisCtrl::cryptocompare_api_query($ccy, $time);
            var_dump($data[$ccy]['USD']);
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}
	
	//add API keys
	public function onCreate()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }
            $entry = new ApisModel();
            $entry->user_id = $user->id;
            $entry->publickey = Input::get('pkey');
            $entry->privatekey = Input::get('prkey');
            if(Input::get('exchange')==3){
				$entry->passphrase = Input::get('passphrase');
			}
			$dateNow = date('Y-m-d H:i:s');
			$entry->created_at = $dateNow;
			$entry->updated_at = $dateNow;
            $entry->exchanges_id = Input::get('exchange');
            $entry->save();
            
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}
	
    public function onDelete()
    {
		$user = Auth::getUser();
		//get ledger
		$line = ApisModel::where('id', Input::get('list_id'))->first();
		
		//if exist
		if(isset($line)){
			//if user post == user ledger
			if($line->user_id == $user->id){			
				//delete ledger
				$model = ApisModel::where('id', Input::get('list_id'))->forceDelete();
				\Flash::success("API deleted successfully");
			}
		}
    }
	
    public function onActivate()
    {
		$user = Auth::getUser();
		//get ledger
		$line = ApisModel::where('id', Input::get('list_id'))->first();
		$check = ApisModel::where('exchanges_id', $line->exchanges_id)->get();
		//if exist
		if(isset($line)){
			if(sizeof($check)<2){
				//if user post == user ledger
				if($line->user_id == $user->id){			
					//delete ledger
					$model = ApisModel::where('id', Input::get('list_id'))->update(['active' => 1]);
					\Flash::success("API activated successfully");
				}
			}else{
				\Flash::success("An API is already activated");
			}
		}
    }
	
    public function onDeactivate()
    {
		$user = Auth::getUser();
		//get ledger
		$line = ApisModel::where('id', Input::get('list_id'))->first();
		
		//if exist
		if(isset($line)){
			//if user post == user ledger
			if($line->user_id == $user->id){			
				//delete ledger
				$model = ApisModel::where('id', Input::get('list_id'))->update(['active' => 0]);
				\Flash::success("API activated successfully");
			}
		}
    }

}
