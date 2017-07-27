<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NBULoaderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function loadData($date_from,$date_to){  //функция прямой загрузки данных
        $loader = new NBUDataLoader();
        $date_from = date('Y-m-d',strtotime($date_from));
        $date_to = date('Y-m-d',strtotime($date_to));
        $loader->loadDataToFiles($date_from,$date_to);
        $loader->parseToDataBase($date_from,$date_to);
    }
    public function loadDataForm(Request $request){ //загрузка данных через пост запрос формы
        if ($request->isMethod('post')) {
            $date_from = $request->input('date_from');
            $date_to = $request->input('date_to');
            if((!empty($date_from))&&(!empty($date_to))){
                $loader = new NBUDataLoader();
                $date_from = date('Y-m-d',strtotime($date_from));
                $date_to = date('Y-m-d',strtotime($date_to));
                $loader->loadDataToFiles($date_from,$date_to);
                $loader->parseToDataBase($date_from,$date_to);
                $loader->loadDataToFiles($date_from,$date_to);
                $loader->parseToDataBase($date_from,$date_to);
                return 'date_loaded';
            }
        }else{
            return redirect('/');
        }
    }
}
