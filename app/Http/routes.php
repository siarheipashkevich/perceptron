<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');

Route::post('/image/upload', 'HomeController@uploadImage');

Route::post('/probe/upload', 'HomeController@uploadProbes');

Route::post('/recognition/upload', 'RecognitionController@upload');
Route::post('/recognition/convert', 'RecognitionController@convert');
Route::post('/recognition/recognition', 'RecognitionController@recognition');
Route::post('/recognition/binary', 'RecognitionController@binary');


Route::post('api/upload', 'ApiController@upload');
Route::post('api/saveData', 'ApiController@saveData');

Route::get('api/getData', 'ApiController@getData');
Route::post('api/convertGrayscale', 'ApiController@convertGrayscale');
Route::post('api/convertBinary', 'ApiController@convertBinary');

Route::post('api/horizontalChart', 'ApiController@horizontalChart');
Route::post('api/cutLines', 'ApiController@cutLines');
Route::post('api/cutCharacters', 'ApiController@cutCharacters');
