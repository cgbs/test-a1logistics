<?php

namespace App\Http\Controllers;
use App\Currency;
use App\CurrencyRates;
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Facades\Storage;
use MCurl\Client;

class NBUDataLoader
{
    private $API_URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';
    private $storage_path = 'NBUData/';
    public function loadDataToFiles($date_from, $date_to) //Загружает файлы в локальный кеш с API НБУ за указаный период
    {
        $urls = $this->getDaysLinksArray($date_from,$date_to); //получаем URL для json api ля всех дней
        if(count($urls) > 0){
            $url_blocks = array_chunk($urls,30);    //разбиваем по парралельным потокам для MCurl
            $client = new Client();
            foreach ($url_blocks as $ub){
                $results = $client->get($ub);
                foreach($results as $result) {
                    $result_assoc = json_decode($result,true);
                    if(isset($result_assoc[0]['exchangedate'])){
                        $filename = date('Y-m-d',strtotime($result_assoc[0]['exchangedate'])).'.json';
                        Storage::disk('local')->put($this->storage_path.$filename,$result);
                    }
                }
            }
        }
    }
    private function getDaysLinksArray($from, $to){ //возвращает массив ссылок для запроса
        $period = new DatePeriod(
            new DateTime($from),
            new DateInterval('P1D'),
            new DateTime($to)
        );
        $urls = [];
        foreach($period as $date){
            $check_filename = $date->format("Y-m-d").'.json';
            if(!Storage::disk('local')->exists($this->storage_path.$check_filename)){   //не загружать файлы которые уже есть в локальном кеше
                $urls[] = $this->API_URL.'&date='.$date->format("Ymd");
            }
        }
        return $urls;
    }
    public function getCurrencyNameId(){ //возвращает assoc массив code => id
        $map = Currency::all()->mapWithKeys(function ($item) {
            return [$item['code'] => $item['id']];
        })->ToArray();
        return $map;

    }
    public function addNewCurrency($name,$code){    //вставляет новую валюту в БД, возвращает айди
        return Currency::create([
            'name' => $name,
            'code' => $code,
        ])->id;
    }
    public function parseToDataBase($date_from, $date_to){  //парсит загруженные в локальный кеш файлы и пишет данные в БД
        $date_from = strtotime($date_from);
        $date_to = strtotime($date_to);
        $files = Storage::disk('local')->files($this->storage_path);
        foreach($files as $file){
            $filename = pathinfo($file, PATHINFO_FILENAME);
            if((strtotime($filename) >= $date_from)&&(strtotime($filename) <= $date_to )){    //проверка чтобы файл был в диапазоне дат
                if(!CurrencyRates::where(['date' => $filename])->take(1)->count()){ //проверка на то, что записей еще нет в БД
                    //парсим json и вставляем данные в БД
                    $data = Storage::disk('local')->get($file);
                    $data = json_decode($data,true);
                    $currency_map = $this->getCurrencyNameId();
                    $inserts_day = [];      //массив для вставки всех валют за день одним запросом
                    foreach ($data as $cur){
                        $cur_id = 0;
                        if(isset($currency_map[$cur['cc']])){     //Если валюта есть в базе - получаем id
                            $cur_id = $currency_map[$cur['cc']];
                        }else{                                      //если валюты нет в базе, добавляем и обновляем карту code => id
                            $cur_id = $this->addNewCurrency($cur['txt'],$cur['cc']);
                            $currency_map = $this->getCurrencyNameId();
                        }
                        $inserts_day[] = [
                            'date' => date('Y-m-d',strtotime($cur['exchangedate'])),
                            'currency_id' => $cur_id,
                            'rate' => $cur['rate'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    CurrencyRates::insert($inserts_day);
                }
            }
        }
    }
}