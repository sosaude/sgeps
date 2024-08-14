<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Mobile Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('criar', 'AuthMobileAPIController@criar');
Route::post('login', 'AuthMobileAPIController@login');
Route::post('recuperar_senha', 'AuthMobileAPIController@recuperarSenha');
// Route::post('auto_registar', 'AuthMobileAPIController@autoRegistar');
Route::post('auto_registar/fase_um', 'AuthMobileAPIController@autoRegistarFaseUm');

Route::post('verificar_beneficiario', 'AuthMobileAPIController@verificarBeneficiario');


Route::group(['middleware' => 'auth.jwt'], function () {
    // Route::get('me', 'AuthMobileAPIController@me');
    Route::post('auto_registar/fase_dois', 'AuthMobileAPIController@autoRegistarFaseDois');
    // Route::post('auto_registar/fase_dois/novo', 'AuthMobileAPIController@autoRegistarFaseDoisNovo');
    Route::post('auto_registar/fase_tres', 'AuthMobileAPIController@autoRegistarFaseTres');
    Route::post('logout', 'AuthMobileAPIController@logout');
    Route::post('trocar_senha', 'AuthMobileAPIController@trocarSenha');

    // Associar Beneficiario ao Cliente
    Route::post('associar_beneficiario', 'AuthMobileAPIController@associarBeneficiario');
    Route::post('desassociar_beneficiario', 'AuthMobileAPIController@desassociarBeneficiario');

    // Upload Ficheiro
    Route::post('upload/foto_perfil', 'AuthMobileAPIController@uploadFotoPerfil');

    Route::get('mapa_unidades_sanitarias', 'MobileAPIController@mapaUnidadesSanitarias');
    Route::get('pesquisa_geral/{filtro?}', 'MobileAPIController@pesquisaGeral');
    Route::get('pesquisar/marcas_medicamentos/{filtro?}', 'MobileAPIController@pesquisarMedicamento');

    Route::get('unidades_sanitarias', 'MobileAPIController@getUnidadesSanitariasConvenio');
    Route::get('servicos_medicamentos', 'MobileAPIController@getServicosMedicamentosConvenio');
    
    // Historico de Consumo
    Route::get('historico_consumo', 'MobileAPIController@historicoConsumo');

    //Sugestao
    Route::get('sugestoes', 'SugestaoAPIController@index');
    Route::post('sugestoes', 'SugestaoAPIController@store');


    //Pedido de Reembolso
    // Route::get('pedidos_reembolso/{estado_pedido_reembolso?}', 'PedidoReembolsoAPIController@index');
    Route::get('pedidos_reembolso', 'PedidoReembolsoAPIController@index');// actualizar para buscar todos
    // Route::get('pedidos_reembolso/{estado_pedido_reembolso?}', 'PedidoReembolsoAPIController@indexPedidoReembolsoByEstado');
    Route::post('pedidos_reembolso', 'PedidoReembolsoAPIController@store');
    Route::get('pedidos_reembolso/{id}', 'PedidoReembolsoAPIController@show');
    Route::post('pedidos_reembolso/re_submeter/{id}', 'PedidoReembolsoAPIController@resubmeterPedidoReembolso');
    Route::post('pedidos_reembolso/{id}/remover_ficheiro/{ficheiro}', 'PedidoReembolsoAPIController@removerFicheiro');

    // Pedido de Aprovacao
    Route::get('pedidos_aprovacao', 'BaixaFarmaciaMobileController@getPedidosAprovacao');



    // Ordem Reserva
    Route::get('ordem_reserva', 'BaixaFarmaciaMobileController@getOrdemReserva');
    Route::post('ordem_reserva', 'BaixaFarmaciaMobileController@efectuarOrdemReserva');


    // Desactivar Beneficiario
    Route::post('desactivar_dependentes', 'BeneficiarioMobileController@desactivarDependenteBeneficiario');
    Route::get('dependentes_beneficiario', 'BeneficiarioMobileController@getDependenteBeneficiario');


    //G Gasto do Pedido de Reembolso
    Route::get('gastos_reembolso', 'PedidoReembolsoAPIController@getGastoPedidoReembolso');
});
