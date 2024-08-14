<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */
// Route::post('register', 'AuthAPIController@register');
Route::post('login', 'AuthAPIController@login');
Route::post('logout', 'AuthAPIController@logout'); // Has to be in all routes files
Route::post('change_password', 'LoginAPIController@changePassword');
Route::post('forgot_password', 'LoginAPIController@forgotPassword');
Route::get('teste_api', 'TesteController@testeApi');


/* 
Route::resource('roles', 'RoleAPIController');

Route::resource('seccaos', 'SeccaoAPIController');

Route::resource('utilizador_farmacias', 'UtilizadorFarmaciaAPIController'); 


Route::resource('marca_medicamentos', 'MarcaMedicamentoAPIController');

Route::resource('perfil_utilizador_farmacias', 'PerfilUtilizadorFarmaciaAPIController');

Route::resource('categoria_empresas', 'CategoriaEmpresaAPIController');

Route::resource('empresas', 'EmpresaAPIController');

Route::resource('utilizador_empresas', 'UtilizadorEmpresaAPIController');

Route::resource('utilizador_administracaos', 'UtilizadorAdministracaoAPIController');

Route::resource('sugestaos', 'SugestaoAPIController');

Route::resource('clinicas', 'ClinicaAPIController');

Route::resource('utilizador_clinicas', 'UtilizadorClinicaAPIController');

Route::resource('forma_marca_medicamentos', 'FormaMarcaMedicamentoAPIController');

Route::resource('servicos', 'ServicoAPIController');
*/

