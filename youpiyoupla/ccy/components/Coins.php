<?php namespace youpiyoupla\ccy\Components;

use Auth;
use Input;
use Request;
use Redirect;
use Cms\Classes\Page;
use October\Rain\Exception\ApplicationException;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\User as UserModel;
use RainLab\Forum\Models\Member as MemberModel;
use youpiyoupla\ccy\Models\Coins as CoinsModel;

class Coins extends ComponentBase
{
	
    public function componentDetails()
    {
        return [
            'name'        => 'Coins component',
            'description' => 'Display list of coins',
        ];
    }
    
	public function onRun()
	{
        try {
            if (!$user = Auth::getUser()) {
               // return Redirect::to('account')->with('message', 'Need to login');
            }
			//Check if user has own coins
			/*$club = $this->checkUserClub();
			if(!is_object($club))
			{
				$countriesModel = new CountriesModel();
				$countries = $countriesModel->getCountriesName();
				$this->page['countries'] = $countries;
				$this->page['displayCreateClub'] = "true";
			}
			else
			{
				$clubPlayers = PlayersModel::where('clubs_id', $club[0]->id)->get();
				$this->page['displayCreateClub'] = "false";
				$this->page['club'] = $club[0];
				$this->page['players'] = $clubPlayers;
			}*/
			//$coinlist = new CoinsModel();
			$list = CoinsModel::all();
			//dd($list);
			$this->page['lists'] = $list; 
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}

	public function onCreate()
    {
        try {
            if (!$user = Auth::getUser()) {
                throw new ApplicationException('You should be logged in.');
            }

            $member = $this->getMember();
            $playerController = new PlayersController();

            if ($member->is_banned) {
                throw new ApplicationException('You cannot create new topics: Your account is banned.');
            }
            $countryID = Input::get('countrytest');
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
	}

 
 /*
	//check if user already have a club
	//return club object if has a club, return 1 if no club
	public function checkUserClub()
	{
		try {
			$userid = Auth::getUser()->id;
			$club = ClubsModel::where('users_id', $userid)->get();
			return($club);
		}
		catch (Exception $ex) {
			return(1);
		}
	}
*/
}
