@extends('layouts.app')
@section('head')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(initChart);
        function initChart() {
            window.chart = new google.visualization.ComboChart(document.getElementById('chart'));
        }
    </script>
@endsection
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Данные в БД</div>
                <div class="panel-body">
                    <div class="block-left">
                        @if ($db_info['row_cnt'] > 0)
                            <p>
                                Записей по курсом валют в локальной БД: {{$db_info['row_cnt']}}
                            </p>
                            <p>
                                Данные за {{$db_info['days_cnt']}} разных дней, количество валют: {{$db_info['cur_cnt']}}
                            </p>
                            <p>
                                Диапазон дат от {{$db_info['min_day']}} до {{$db_info['max_day']}}
                            </p>
                        @else
                            Пока в Базе Данных записей нет
                        @endif
                    </div>
                    <div class="block-right">
                        <a href="/home/loadform" type="button" class="btn btn-primary">Загрузить через API НБУ</a>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Графики Аналитики</div>
                <div class="panel-body analytic">
                    @if ($db_info['row_cnt'] > 0)
                        <div class="loader-cover no-round">
                            <img src="{{asset('img/hourglass.svg')}}" class="loader-icon">
                            <div class="loader-info-text">
                                Постройка графика
                            </div>
                        </div>
                    <div class="analytic-settings">
                        <form method="POST" action="/analytic/getchartdata" class="chart-constructor">
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Дата начала</label>
                                        <input type="text" class="form-control datepicker" name="date_from" id="date_from" value="{{$default_from}}" placeholder="Начало периода" required />
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Дата конца</label>
                                        <input type="text" class="form-control datepicker" name="date_to" id="date_to" value="{{$default_to}}" placeholder="Конец периода" required/>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Группировка</label>
                                        <select name="group_by" class="form-control">
                                            <option value="day">День</option>
                                            <option value="month" selected>Месяц</option>
                                            <option value="quater">Квартал</option>
                                            <option value="year">Год</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>График</label>
                                        <select name="chart_type" class="form-control">
                                            <option value="line">Линейный</option>
                                            <option value="bars">Колонки</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10 col-sm-8">
                                    <div class="form-group">
                                        <label>Валюта</label>
                                        <select name="currency" class="form-control multi-select" multiple>
                                            @foreach ($currency as $c)
                                                <option value="{{$c['code']}}" {{(in_array($c['code'],$currency_default)) ? 'selected' : ''}}>
                                                    {{$c['code']}}-{{$c['name']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4">
                                    {{ csrf_field() }}
                                    <input type="submit" class="btn btn-primary" value="Построить"/>
                                </div>
                            </div>
                        </form>
                    </div>
                    @else
                        Пока в Базе Данных записей нет
                    @endif
                    <hr/>
                    <div id="chart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
