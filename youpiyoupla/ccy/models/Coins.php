<?php namespace Youpiyoupla\Ccy\Models;

use Model;

/**
 * Model
 */
class Coins extends Model
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
    public $table = 'youpiyoupla_ccy_coins';	    

}
