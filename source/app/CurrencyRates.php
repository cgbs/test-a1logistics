<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyRates extends Model
{
    protected $fillable = [
        'date', 'bank_id', 'currency_id', 'rate', 'created_at', 'updated_at',
    ];
    protected $table = 'currency_rates';
}
