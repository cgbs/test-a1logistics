<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/home/loadform', 'HomeController@loadForm')->name('loadform');
//Route::get('/dataloader/load/{date_from}/{date_to}', 'NBULoaderController@loadData')->name('loader');
Route::post('/dataloader/load/', 'NBULoaderController@loadDataForm')->name('loader_from');
Route::post('/analytic/getchartdata', 'AnalyticsController@BuildChartJSON')->name('build_chart');

