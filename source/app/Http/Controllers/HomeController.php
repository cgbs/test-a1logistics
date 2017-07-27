<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Currency;
use App\CurrencyRates;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dbInfo(){   //информация о данных в БД
        return [
            'row_cnt'   => CurrencyRates::count(),
            'days_cnt'  => CurrencyRates::distinct('date')->count('date'),
            'cur_cnt'   => Currency::count(),
            'min_day'   => CurrencyRates::min('date'),
            'max_day'   => CurrencyRates::max('date'),
        ];
    }
    public function loadForm(){ //форма загрузки новый данных с сервера НБУ
        return view('load-data',['db_info' => $this->dbInfo()]);
    }
    public function index()
    {
        return view('home',[
            'db_info' => $this->dbInfo(),
            'currency' => Currency::where('code','!=','   ')->orderBy('code')->get()->toArray(),
            'currency_default' => ['USD','EUR','PLN','GBP','RUB'],
            'default_from' => '2015-01-01',
            'default_to' => '2017-01-01',
        ]);
    }
}
