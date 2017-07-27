<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name', 'code',
    ];
    protected $table = 'currency';
}
