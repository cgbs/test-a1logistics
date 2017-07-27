<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Currency;
use App\CurrencyRates;
use DB;
use PDO;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    private function convertAssocToGoogleChart($name_index,$value_index,$data){   //Это магия конвертации из ассоциативного в формат гугл-чарт
        /*формат данных должен быть такой
          [
                ['Year', 'Sales', 'Expenses'],
                ['2004',  1000,      400],
                ['2005',  1170,      460],
                ['2006',  660,       1120],
                ['2007',  1030,      540]
           ]
        */
        $chart = [
            array('Time')
        ];
        $chart_dates = array('x');
        $chart_cats = array();
        foreach ($data as $a) {
            $a = (array)$a;
            if(!isset($a[$name_index])) $a[$name_index] = $name_index;
            if(!in_array($a[$name_index], $chart[0])){
                $chart[0][] = $a[$name_index];
                $chart_cats[$a[$name_index]] = array_search($a[$name_index],$chart[0]);
            }
        }
        foreach ($data as $a) {
            $a = (array)$a;
            if(!isset($a[$name_index])) $a[$name_index] = $name_index;
            $date = $a['date'];
            if(!isset($chart_dates[$date])){
                $chart_dates[] = $date;
                $chart[] = array_merge(array($date),array_fill(1, count($chart[0]) - 1, 0));
                $chart_dates[$date]=count($chart)-1;
            }
            $index_col = $chart_cats[$a[$name_index]];
            $index_row = $chart_dates[$date];
            if(($index_col != 0)&&($index_row != 0)){
                $chart[$index_row][$index_col] = floatval($a[$value_index]);
            }
        }
        return $chart;
    }
    private function getDBData($date_from, $date_to, $currency, $group_by){ //получение данных из БД согласно параметрам
        //TODO  группировка $group_by
        $data = DB::table('currency_rates')
            ->join('currency', 'currency_rates.currency_id', '=', 'currency.id');
            switch ($group_by){ //группируем в зависимости от заданого параметра
                case 'month':
                    $date_group = "DATE_FORMAT(currency_rates.date, '%m - %Y')";
                    $data = $data->select(DB::raw("$date_group as `date`"),'currency.code','currency.name',DB::raw('AVG(currency_rates.rate) as rate'))
                        ->groupBy(DB::raw($date_group),'currency.code','currency.name');
                    break;
                case 'quater':
                    $date_group = "CONCAT( YEAR(date),' - Q',QUARTER(date) )";
                    $data = $data->select(DB::raw("$date_group as `date`"),'currency.code','currency.name',DB::raw('AVG(currency_rates.rate) as rate'))
                        ->groupBy(DB::raw($date_group),'currency.code','currency.name');
                    break;
                case 'year':
                    $date_group = "DATE_FORMAT(currency_rates.date, '%Y')";
                    $data = $data->select(DB::raw("$date_group as `date`"),'currency.code','currency.name',DB::raw('AVG(currency_rates.rate) as rate'))
                        ->groupBy(DB::raw($date_group),'currency.code','currency.name');
                    break;
                default:    //дени и дефолтное значение
                    $data = $data->select('currency_rates.date','currency.code','currency.name','currency_rates.rate');
                    break;
            }
            $data = $data->whereIn('currency.code',$currency)
            ->whereBetween('currency_rates.date', [$date_from, $date_to])
            ->orderBy('currency_rates.date')
            ->get()->toArray();
        return $data;
    }
    public function BuildChartJSON(Request $request){   //формирование json результата для google charts
        if ($request->isMethod('post')) {
            $date_from = $request->input('date_from');
            $date_to = $request->input('date_to');
            $group_by = $request->input('group_by');
            $chart_type = $request->input('chart_type');
            $currency = $request->input('currency');

            $data = $this->getDBData($date_from,$date_to,$currency,$group_by);
            $chart_data = $this->convertAssocToGoogleChart('name','rate',$data);
            $response = [
                'options' => [
                    'title' => 'Курсы валют НБУ',
                    'curveType' => 'function',
                    'legend' => [ 'position' => 'bottom' ],
                    'seriesType' => $chart_type,
                    'height' => 500
                ],
                'data' => $chart_data,
            ];
            return response()->json($response);

        }else{
            return redirect('/');
        }
    }
}
