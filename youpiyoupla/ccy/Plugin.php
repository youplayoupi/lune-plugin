<?php namespace Youpiyoupla\Ccy;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
		return [
			'\youpiyoupla\ccy\components\Coins' => 'Coins',
			'\youpiyoupla\ccy\components\Apis' => 'Apis',
			'\youpiyoupla\ccy\components\Exchanges' => 'Exchanges',
			'\youpiyoupla\ccy\components\Totals' => 'Totals',
			'\youpiyoupla\ccy\components\Portfolio' => 'Portfolio',
			'\youpiyoupla\ccy\components\Accountoptions' => 'Accountoptions',
			'\youpiyoupla\ccy\components\Ledgers' => 'Ledgers'
		];
    }

    public function registerSettings()
    {
    }
}
