@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="loader-cover">
                    <img src="{{asset('img/hourglass.svg')}}" class="loader-icon">
                    <div class="loader-info-text">
                        Начало загрузки данных
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Загрузить данные в БД</div>
                    <div class="panel-body">
                        <form class="form-loader" action="/dataloader/load" method="POST">
                            <div class="form-group">
                                <p>
                                    Локальные данные в диапазоне {{$db_info['min_day']}} до {{$db_info['max_day']}}
                                </p>
                                <p>
                                    Выберите диапазон для загрузки, данные на сервере НБУ начинаются с 1997-01-01
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="date_from" class=control-label">От</label>
                                <input type="text" class="form-control datepicker" name="date_from" id="date_from" placeholder="Начало периода" required />
                            </div>
                            <div class="form-group">
                                <label for="date_to" class="control-label">До</label>
                                <input type="text" class="form-control datepicker" name="date_to" id="date_to" placeholder="Конец периода" required />
                            <p class="help-block">
                                Диапазон может пересекаться с загруженными данными, система просто загрузит недостающие
                            </p>
                            <p>
                                {{ csrf_field() }}
                                <input type="submit" type="button" class="btn btn-primary" value="Загрузить"/>
                            </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection