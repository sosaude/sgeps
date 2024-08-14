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

//Farmacias
Route::get('farmacias/edit', 'FarmaciaAPIController@edit'); // farmacia_id é pego no request
Route::put('farmacias/{id}', 'FarmaciaAPIController@update');

//Utilizador Farmacia
Route::get('utilizador_farmacia', 'UtilizadorFarmaciaAPIController@index');
Route::get('utilizador_farmacia/create', 'UtilizadorFarmaciaAPIController@create');
Route::post('utilizador_farmacia', 'UtilizadorFarmaciaAPIController@store');
Route::put('utilizador_farmacia/{id}', 'UtilizadorFarmaciaAPIController@update');
Route::get('utilizador_farmacia/{id}', 'UtilizadorFarmaciaAPIController@show');
Route::delete('utilizador_farmacia/{id}', 'UtilizadorFarmaciaAPIController@destroy');

//Medicamentos e Marcas
Route::get('marcas_medicamentos', 'StockFarmaciaAPIController@getMarcasMedicamentosAdministracao');
Route::get('stock', 'StockFarmaciaAPIController@getStock');
Route::post('stock/novo', 'StockFarmaciaAPIController@setMarcasMedicamentosFarmacia');
Route::post('stock/actualizar', 'StockFarmaciaAPIController@actualizarStock');
Route::post('stock/remover/{marca_medicamento_id}', 'StockFarmaciaAPIController@removeMarcasMedicamentoFarmacia');

Route::get('stock/iniciar_venda/{id}/beneficiario', 'StockFarmaciaAPIController@getStockIniciarVenda');
Route::get('stock/iniciar_pedido_aprovacao/{id}/beneficiario', 'StockFarmaciaAPIController@getStockIniciarPedidoAprovacao');

// Beneficiario
Route::any('baixas/beneficiario/verificar', 'BaixaFarmaciaAPIController@verificarBeneficiario');


// Baixas
Route::get('baixas', 'BaixaFarmaciaAPIController@getBaixasFarmacia');
Route::get('baixas_excel', 'BaixaFarmaciaAPIController@getBaixasFarmaciaExcel');
Route::post('baixas/efectuar', 'BaixaFarmaciaAPIController@efectuarBaixa');
Route::get('baixas/{baixa_id}/comprovativo/download/{ficheiro}', 'BaixaFarmaciaAPIController@downloadComprovativoBaixa');


// Ordens Reserva
Route::get('baixas/ordens_reserva', 'BaixaFarmaciaAPIController@getOrdemReserva');
Route::get('baixas/ordens_reserva/{beneficiario_id}/beneficiario', 'BaixaFarmaciaAPIController@getOrdemReservaBeneficiario');


// Pedidos Reembolso
Route::get('pedidos_aprovacao', 'BaixaFarmaciaAPIController@getPedidoAprovacao');
Route::get('pedidos_aprovacao_excel', 'BaixaFarmaciaAPIController@getPedidoAprovacaoExcel');
Route::post('pedidos_aprovacao', 'BaixaFarmaciaAPIController@submeterPedidoAprovacao');


// Sugestoes
Route::get('sugestoes', 'UtilitarioFarmaciaAPIController@getSugestao');
Route::post('sugestoes', 'UtilitarioFarmaciaAPIController@storeSugestao');

// Overview - Resumos
Route::get('overview', 'OverviewController@getOverview');
Route::get('overviewfiltered/{startDate}/{endDate}/{companyId}', 'OverviewController@getOverviewFiltered');
Route::get('overview/baixas', 'OverviewController@getBaixasFarmacia');

