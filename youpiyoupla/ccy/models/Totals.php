<?php namespace Youpiyoupla\Ccy\Models;

use Model;
use DB;
use youpiyoupla\ccy\Models\Totals as TotalsModel;

/**
 * Model
 */
class Totals extends Model
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
    public $table = 'youpiyoupla_ccy_total';

    public $belongsTo = [
        'user' => ['rainlab\user\Models\User'],
		'coins' => ['youpiyoupla\ccy\Models\coins'],
		'exchanges' => ['youpiyoupla\ccy\Models\exchanges'],
    ];
	
	public static function saveBulk($data)
    {
		$table = 'youpiyoupla_ccy_total';
		$column_id = DB::table($table)->insert($data);
		$last_id = DB::table($table)->orderBy('id', 'desc')->first();
		return $last_id;
    }
    
    public static function bulkUpdateOrCreate($entry){
		//dd($entry);
		//if coins for specified user already exist, update
		for ($x=0; $x < sizeof($entry); $x++){
			$coin = Db::table('youpiyoupla_ccy_total')
			->where('coins_id', $entry[$x]['coins_id'])
			->where('user_id', $entry[$x]['user_id'])
			->first();
			if($coin!=null){
				$myArr['total'] = $coin->total + $entry[$x]['total'];
				$myArr['amount_usd'] = $coin->amount_usd + $entry[$x]['amount_usd'];
				$myArr['rate_avg'] = ($coin->rate_avg * $coin->total + $entry[$x]['total']*$entry[$x]['amount_usd'])/$myArr['total'];
				Db::table('youpiyoupla_ccy_total')
				->where('user_id', $entry[$x]['user_id'])
				->where('coins_id', $entry[$x]['coins_id'])
				->update($myArr);
			//if coins does not exist, create
			}else{
				$data = new TotalsModel();
				$data->user_id = $entry[$x]['user_id'];
				$data->coins_id = $entry[$x]['coins_id'];
				$data->total = $entry[$x]['total'];
				$data->created_at = $entry[$x]['created_at'];
				$data->updated_at = $entry[$x]['updated_at'];
				$data->amount_usd = $entry[$x]['amount_usd'];
				$data->rate_avg = $entry[$x]['rate_avg'];
				$save = $data->save();
			}
		}
		return 1;
	}
    
    /*
     * Get paginated coin list for one user
     * 
     **/
    public static function getDataPaginated($user)
    {
		$query = DB::table('youpiyoupla_ccy_total')
					->where('user_id', $user)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_total.coins_id', '=', 'c1.id')
					->select('youpiyoupla_ccy_total.*', 'c1.abv', 'c1.name')
					->paginate(20);
		return $query;
	}
    
    /*
     * Get all data for one coin
     * 
     **/
    public static function getDataCoin($coin)
    {
		$query = DB::table('youpiyoupla_ccy_total')
					->where('coins_id', $coin)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_total.coins_id', '=', 'c1.id')
					->select('youpiyoupla_ccy_total.*', 'c1.abv')
					->first();
		return $query;
	}
    
    /*
     * Get all data for one coin
     * 
     **/
    public static function getDataCoinUser($coin, $user)
    {
		$query = DB::table('youpiyoupla_ccy_total')
					->where('coins_id', $coin)
					->where('user_id', $user)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_total.coins_id', '=', 'c1.id')
					->select('youpiyoupla_ccy_total.*', 'c1.abv')
					->first();
		return $query;
	}
    
    /*
     * Get full coin list for one user
     * 
     **/
    public static function getDataCoins($user)
    {
		$query = DB::table('youpiyoupla_ccy_total')
					->where('user_id', $user)
					->where('total', '>', 0)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_total.coins_id', '=', 'c1.id')
					->select('youpiyoupla_ccy_total.*', 'c1.abv', 'c1.icon')
					->get();
		return $query;
	}
   
}
