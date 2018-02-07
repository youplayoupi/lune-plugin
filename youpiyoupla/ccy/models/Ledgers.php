<?php namespace Youpiyoupla\Ccy\Models;

use Model;
use DB;

/**
 * Model
 */
class Ledgers extends Model
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
    public $table = 'youpiyoupla_ccy_ledger';

    public $belongsTo = [
        'user' => ['rainlab\user\Models\User'],
		'coins' => ['youpiyoupla\ccy\Models\coins'],
		'exchanges' => ['youpiyoupla\ccy\Models\exchanges'],
    ];
	
	public static function addData($data)
    {
		$table = 'youpiyoupla_ccy_ledger';
		$column_id = DB::table($table)->insert($data);
		$last_id = DB::table($table)->orderBy('id', 'desc')->first();
		return $last_id;
    }
    
    public static function getDataPaginatedUser($user)
    {
		$query = DB::table('youpiyoupla_ccy_ledger')
					->where('user_id', $user)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_ledger.coins_id', '=', 'c1.id')
					->join('youpiyoupla_ccy_coins as c2', 'youpiyoupla_ccy_ledger.currency_id', '=', 'c2.id')
					->join('youpiyoupla_ccy_exchanges', 'youpiyoupla_ccy_ledger.exchanges_id', '=', 'youpiyoupla_ccy_exchanges.id')
					->select('youpiyoupla_ccy_ledger.*', 'c1.abv','c2.abv as ccy', 'youpiyoupla_ccy_exchanges.name')
					->paginate(20);
		return $query;
	}
    
    public static function getDataCoinUser($coin, $user)
    {
		$query = DB::table('youpiyoupla_ccy_ledger')
					->where('coins_id', $coin)
					->orWhere('currency_id', $coin)
					->where('user_id', $user)
					->join('youpiyoupla_ccy_coins as c1', 'youpiyoupla_ccy_ledger.coins_id', '=', 'c1.id')
					->join('youpiyoupla_ccy_coins as c2', 'youpiyoupla_ccy_ledger.currency_id', '=', 'c2.id')
					->join('youpiyoupla_ccy_exchanges', 'youpiyoupla_ccy_ledger.exchanges_id', '=', 'youpiyoupla_ccy_exchanges.id')
					->select('youpiyoupla_ccy_ledger.*', 'c1.abv','c2.abv as ccy', 'youpiyoupla_ccy_exchanges.name')
					->orderBy('date', 'desc')
					->paginate(20);
		return $query;
	}
}
