<?php namespace youpiyoupla\ccy\Updates;

use youpiyoupla\ccy\Models\Exchanges;
use youpiyoupla\ccy\Models\Coins;
use October\Rain\Database\Updates\Seeder;

class SeedExchangesTable extends Seeder
{
    public function run()
    {
        Exchanges::create([
            'name' => 'Binance'
        ]);
        Exchanges::create([
            'name' => 'Cryptopia'
        ]);
        Exchanges::create([
            'name' => 'GDAX'
        ]);
        Exchanges::create([
            'name' => 'Coinbase'
        ]);
        Coins::create([
            'name' => 'Euros',
            'abv' => 'EUR'
        ]);
        Coins::create([
            'name' => 'Dollars',
            'abv' => 'USD'
        ]);
    }
}
