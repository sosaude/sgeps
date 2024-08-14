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

//Unidades Sanitarias
Route::get('unidades_sanitarias/edit', 'UnidadeSanitariaAPIController@edit'); // unidade_sanitaria_id é pego no request
Route::put('unidades_sanitarias/{id}', 'UnidadeSanitariaAPIController@update');


//Utilizador UnidaddeSanitaria
Route::get('utilizador_unidade_sanitaria', 'UtilizadorUnidadeSanitariaAPIController@index');
Route::get('utilizador_unidade_sanitaria/create', 'UtilizadorUnidadeSanitariaAPIController@create');
Route::post('utilizador_unidade_sanitaria', 'UtilizadorUnidadeSanitariaAPIController@store');
Route::put('utilizador_unidade_sanitaria/{id}', 'UtilizadorUnidadeSanitariaAPIController@update');
Route::get('utilizador_unidade_sanitaria/{id}', 'UtilizadorUnidadeSanitariaAPIController@show');
Route::delete('utilizador_unidade_sanitaria/{id}', 'UtilizadorUnidadeSanitariaAPIController@destroy');

//Servicos
Route::get('servicos/globais', 'ServicoUnidadeSanitariaAPIController@getServicosAdministracao');
Route::get('servicos', 'ServicoUnidadeSanitariaAPIController@getServicosUnidadeSanitaria');
Route::post('servicos/novo', 'ServicoUnidadeSanitariaAPIController@setServicosUnidadeSanitaria');
Route::post('servicos/actualizar', 'ServicoUnidadeSanitariaAPIController@actualizarStock');
Route::post('servicos/remover/{servico_id}', 'ServicoUnidadeSanitariaAPIController@removerServicoUnidadeSanitaria');

Route::get('servicos/iniciar_venda/{id}/beneficiario', 'ServicoUnidadeSanitariaAPIController@getStockIniciarVenda');
Route::get('servicos/iniciar_pedido_aprovacao/{id}/beneficiario', 'ServicoUnidadeSanitariaAPIController@getStockIniciarPedidoAprovacao');



// Beneficiario
Route::any('baixas/beneficiario/verificar', 'BaixaUnidadeSanitariaAPIController@verificarBeneficiario');

// Baixas
Route::get('baixas', 'BaixaUnidadeSanitariaAPIController@getBaixasUnidadeSanitaria');
Route::get('baixas_excel', 'BaixaUnidadeSanitariaAPIController@getBaixasUnidadeSanitariaExcel');
Route::post('baixas/efectuar', 'BaixaUnidadeSanitariaAPIController@efectuarBaixa');
Route::get('baixas/{baixa_id}/comprovativo/download/{ficheiro}', 'BaixaUnidadeSanitariaAPIController@downloadComprovativoBaixa');


// Pedidos Reembolso
Route::get('pedidos_aprovacao', 'BaixaUnidadeSanitariaAPIController@getPedidoAprovacao');
Route::get('pedidos_aprovacao_excel', 'BaixaUnidadeSanitariaAPIController@getPedidoAprovacaoExcel');
Route::post('pedidos_aprovacao', 'BaixaUnidadeSanitariaAPIController@submeterPedidoAprovacao');


// Sugestoes
Route::get('sugestoes', 'UtilitarioUnidadeSanitariaAPIController@getSugestao');
Route::post('sugestoes', 'UtilitarioUnidadeSanitariaAPIController@storeSugestao');

// Overview
Route::get('overview', 'OverviewController@getOverview');
Route::get('overviewfiltered/{startDate}/{endDate}/{companyId}', 'OverviewController@getOverviewFiltered');
Route::get('overview/baixas', 'OverviewController@getBaixasFarmacia');

