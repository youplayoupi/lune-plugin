<?php namespace Youpiyoupla\Ccy\Models;

use Model;
use Db;

/**
 * Model
 */
class Apis extends Model
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
    public $table = 'youpiyoupla_ccy_apis';

    public $belongsTo = [
        'user' => ['rainlab\user\Models\User'],
		'exchanges' => ['youpiyoupla\ccy\Models\exchanges'],
    ];
    
    public static function getApisUser($user)
    {
		$query = DB::table('youpiyoupla_ccy_apis')
					->where('user_id', $user)
					->join('youpiyoupla_ccy_exchanges', 'youpiyoupla_ccy_apis.exchanges_id', '=', 'youpiyoupla_ccy_exchanges.id')
					->select('youpiyoupla_ccy_apis.*', 'youpiyoupla_ccy_exchanges.name')
					->get();
		return $query;
	}
    
    public static function getApisListUser($user)
    {
		$query = DB::table('youpiyoupla_ccy_apis')
					->where('user_id', $user)
					->join('youpiyoupla_ccy_exchanges', 'youpiyoupla_ccy_apis.exchanges_id', '=', 'youpiyoupla_ccy_exchanges.id')
					->select('youpiyoupla_ccy_apis.id', 'youpiyoupla_ccy_apis.active', 'youpiyoupla_ccy_exchanges.name')
					->get();
		return $query;
	}
}
