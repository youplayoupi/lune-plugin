<?php namespace Youpiyoupla\Ccy\Models;

use Model;
use Db;
use youpiyoupla\ccy\Models\Sells as SellsModel;

/**
 * Model
 */
class Sells extends Model
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
    public $table = 'youpiyoupla_ccy_sell';
    
    public static function bulkUpdateOrCreate($entry){
		//dd($entry);
		//if coins for specified user already exist, update
		for ($x=0; $x < sizeof($entry); $x++){
			$coin = Db::table('youpiyoupla_ccy_sell')
			->where('coins_id', $entry[$x]['coins_id'])
			->where('user_id', $entry[$x]['user_id'])
			->first();
			
			//if coin was bought
			if(isset($entry[$x]['totalsell'])){
				if($coin!=null){
					$myArr['total'] = $coin->total + $entry[$x]['totalsell'];
					$myArr['amount_usd'] = $coin->amount_usd + $entry[$x]['amount_usdsell'];
					$myArr['rate_avg'] = ($coin->rate_avg * $coin->total + $entry[$x]['totalsell']*$entry[$x]['amount_usdsell'])/$myArr['total'];
					Db::table('youpiyoupla_ccy_sell')
					->where('user_id', $entry[$x]['user_id'])
					->where('coins_id', $entry[$x]['coins_id'])
					->update($myArr);
				//if coins does not exist, create
				}else{
					$data = new SellsModel();
					$data->user_id = $entry[$x]['user_id'];
					$data->coins_id = $entry[$x]['coins_id'];
					$data->total = $entry[$x]['totalsell'];
					$data->created_at = $entry[$x]['created_at'];
					$data->updated_at = $entry[$x]['updated_at'];
					$data->amount_usd = $entry[$x]['amount_usdsell'];
					$data->rate_avg = $entry[$x]['rate_avgsell'];
					$save = $data->save();
				}
			}else{
				//nothing
			}
		}
		return 1;
	}
	
    public static function getDataCoins($user)
    {
		$query = DB::table('youpiyoupla_ccy_sell')
					->where('user_id', $user)
					->where('total', '>', 0)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_sell.coins_id', '=', 'c1.id')
					->select('youpiyoupla_ccy_sell.*', 'c1.abv')
					->get();
		return $query;
	}
}
