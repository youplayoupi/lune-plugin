<?php namespace Youpiyoupla\Ccy\Models;

use Model;
use Db;
use youpiyoupla\ccy\Models\Buys as BuysModel;

/**
 * Model
 */
class Buys extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'youpiyoupla_ccy_buy';
    
    public static function bulkUpdateOrCreate($entry){
		//dd($entry);
		//if coins for specified user already exist, update
		for ($x=0; $x < sizeof($entry); $x++){
			$coin = Db::table('youpiyoupla_ccy_buy')
			->where('coins_id', $entry[$x]['coins_id'])
			->where('user_id', $entry[$x]['user_id'])
			->first();
			
			//if coin was bought
			if(isset($entry[$x]['totalbuy'])){
				if($coin!=null){
					$myArr['total'] = $coin->total + $entry[$x]['totalbuy'];
					$myArr['amount_usd'] = $coin->amount_usd + $entry[$x]['amount_usdbuy'];
					$myArr['rate_avg'] = ($coin->rate_avg * $coin->total + $entry[$x]['totalbuy']*$entry[$x]['amount_usdbuy'])/$myArr['total'];
					Db::table('youpiyoupla_ccy_buy')
					->where('user_id', $entry[$x]['user_id'])
					->where('coins_id', $entry[$x]['coins_id'])
					->update($myArr);
				//if coins does not exist, create
				}else{
					$data = new BuysModel();
					$data->user_id = $entry[$x]['user_id'];
					$data->coins_id = $entry[$x]['coins_id'];
					$data->total = $entry[$x]['totalbuy'];
					$data->created_at = $entry[$x]['created_at'];
					$data->updated_at = $entry[$x]['updated_at'];
					$data->amount_usd = $entry[$x]['amount_usdbuy'];
					$data->rate_avg = $entry[$x]['rate_avgbuy'];
					$save = $data->save();
				}
			}else{
				//nothing?
			}
		}
		return 1;
	}
	
    public static function getDataCoins($user)
    {
		$query = DB::table('youpiyoupla_ccy_buy')
					->where('user_id', $user)
					->where('total', '>', 0)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_buy.coins_id', '=', 'c1.id')
					->select('youpiyoupla_ccy_buy.*', 'c1.abv')
					->get();
		return $query;
	}
}
