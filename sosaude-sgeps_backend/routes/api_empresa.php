<?php


//Utilizador Empresa
Route::get('utilizador_empresa', 'UtilizadorEmpresaAPIController@index');
Route::get('utilizador_empresa/create', 'UtilizadorEmpresaAPIController@create');
Route::post('utilizador_empresa', 'UtilizadorEmpresaAPIController@store');
Route::put('utilizador_empresa/{id}', 'UtilizadorEmpresaAPIController@update');
Route::get('utilizador_empresa/{id}', 'UtilizadorEmpresaAPIController@show');
Route::delete('utilizador_empresa/{id}', 'UtilizadorEmpresaAPIController@destroy');

Route::get('beneficiarios', 'BeneficiarioAPIController@index');
Route::get('beneficiarios-excel', 'BeneficiarioAPIController@indexBeneficiario');
Route::post('beneficiarios', 'BeneficiarioAPIController@store');
Route::get('beneficiarios/create', 'BeneficiarioAPIController@create');
Route::put('beneficiarios/{id}', 'BeneficiarioAPIController@update');
Route::get('beneficiarios/{id}', 'BeneficiarioAPIController@show');
Route::delete('beneficiarios/{id}', 'BeneficiarioAPIController@destroy');
Route::post('beneficiarios/import-excel', 'BeneficiarioAPIController@importFromExcel');

// Deependente Beneficiario
Route::get('dependente_beneficiarios', 'DependenteBeneficiarioController@index');
Route::get('dependente_beneficiarios_excel', 'DependenteBeneficiarioController@indexBeneficiario');
Route::delete('dependente_beneficiarios/{id}', 'DependenteBeneficiarioController@destroy');
Route::post('dependente_beneficiarios/import-excel', 'DependenteBeneficiarioController@importFromExcel');

// Grupo Beneficiario
Route::get('grupo_beneficiarios', 'GrupoBeneficiarioAPIController@index');
Route::post('grupo_beneficiarios', 'GrupoBeneficiarioAPIController@store');
Route::get('grupo_beneficiarios/create', 'GrupoBeneficiarioAPIController@create');
Route::put('grupo_beneficiarios/{id}', 'GrupoBeneficiarioAPIController@update');
Route::get('grupo_beneficiarios/{id}', 'GrupoBeneficiarioAPIController@show');
Route::delete('grupo_beneficiarios/{id}', 'GrupoBeneficiarioAPIController@destroy');

// Farmacias da empresa
Route::get('farmacias/todas', 'EmpresaAPIController@indexTodasFarmacias');
Route::get('farmacias', 'EmpresaAPIController@indexFarmaciasDaEmpresa');
Route::post('farmacias', 'EmpresaAPIController@associarFarmaciasDaEmpresa');
Route::post('farmacias/desassociar', 'EmpresaAPIController@desassociarFarmaciasDaEmpresa');


// Clinicas da Empresa
Route::get('clinicas/todas', 'EmpresaAPIController@indexTodasClinicas');
Route::get('clinicas', 'EmpresaAPIController@indexClinicasDaEmpresa');
Route::post('clinicas', 'EmpresaAPIController@associarClinicasDaEmpresa');
Route::post('clinicas/desassociar', 'EmpresaAPIController@desassociarClinicasDaEmpresa');

// Unidades Sanitarias da Empresa
Route::get('unidades_sanitarias/todas', 'EmpresaAPIController@indexTodasUnidadesSanitarias');
Route::get('unidades_sanitarias', 'EmpresaAPIController@indexUnidadesSanitariasDaEmpresa');
Route::post('unidades_sanitarias', 'EmpresaAPIController@associarUnidadesSanitariasDaEmpresa');
Route::post('unidades_sanitarias/desassociar', 'EmpresaAPIController@desassociarUnidadesSanitariasDaEmpresa');



// Baixas
// Route::get('baixas_farmacia', 'EmpresaAPIController@indexBaixaFarmacia');
Route::get('baixas', 'BaixaAPIController@indexBaixa');
Route::get('baixas_excel', 'BaixaAPIController@indexBaixaExcel');
Route::post('baixas/confirmar', 'BaixaAPIController@confirmarBaixa');
Route::post('baixas/confirmar/bulk', 'BaixaAPIController@confirmarBaixaBulk');
Route::post('baixas/processar_pagamento', 'BaixaAPIController@processarPagamentoBaixa');
Route::post('baixas/processar_pagamento/bulk', 'BaixaAPIController@processarPagamentoBaixaBulk');
Route::post('baixas/devolver', 'BaixaAPIController@devolverBaixa');
Route::get('baixas/{proveniencia}/{id}/comprovativo/download/{ficheiro}', 'BaixaAPIController@downloadComprovativoBaixa');
Route::get('baixas/pedido_aprovacao', 'BaixaAPIController@indexPedidoAprovacao');
Route::get('baixas/pedido_aprovacao_excel', 'BaixaAPIController@indexPedidoAprovacaoExcel');
Route::post('baixas/aprovar/pedido_aprovacao', 'BaixaAPIController@aprovarPedidoAprovacao');
Route::post('baixas/aprovar/bulk/pedido_aprovacao', 'BaixaAPIController@aprovarPedidoAprovacaoBulk');
Route::post('baixas/rejeitar/pedido_aprovacao', 'BaixaAPIController@rejeitarPedidoAprovacao');

//Pedido de Reembolso
Route::get('pedidos_reembolso', 'PedidoReembolsoAPIController@indexPedidoReembolso');
Route::get('pedidos_reembolso_excel', 'PedidoReembolsoAPIController@indexPedidoReembolsoExcel');
Route::get('pedidos_reembolso/create', 'PedidoReembolsoAPIController@create');
Route::post('pedidos_reembolso/efectuar_pedido', 'PedidoReembolsoAPIController@efectuarPedidoReembolso');
Route::post('pedidos_reembolso/confirmar_pedido', 'PedidoReembolsoAPIController@confirmarPedidoReembolso');
Route::post('pedidos_reembolso/bulk/confirmar_pedido', 'PedidoReembolsoAPIController@confirmarPedidoReembolsoBulk');
// Route::post('pedidos_reembolso', 'PedidoReembolsoAPIController@store'); // Auxiliar, apenas para registar temporariamente os pedidos
Route::post('pedidos_reembolso/processar_pagamento', 'PedidoReembolsoAPIController@processarPagamentoPedidoReembolso');
Route::post('pedidos_reembolso/bulk/processar_pagamento', 'PedidoReembolsoAPIController@processarPagamentoPedidoReembolsoBulk');
Route::post('pedidos_reembolso/devolver', 'PedidoReembolsoAPIController@devolverPedidoReembolso');
Route::get('pedidos_reembolso/{pedido_reembolso_id}/download/{ficheiro}', 'PedidoReembolsoAPIController@downloadComprovativoPedidoReembolso');

// Plano de Saúde
Route::get('plano_saude', 'PlanoSaudeAPIController@index');
Route::get('plano_saude/create', 'PlanoSaudeAPIController@create');
Route::get('plano_saude/padrao', 'PlanoSaudeAPIController@getPlanoSaudePadrao');
Route::post('plano_saude/padrao', 'PlanoSaudeAPIController@setPlanoSaudePadrao');
Route::get('plano_saude/configurar/{grupo_beneficiario_id}', 'PlanoSaudeAPIController@getConfigurarPlanoSaude');
Route::post('plano_saude/configurar', 'PlanoSaudeAPIController@setConfigurarPlanoSaude');
Route::delete('plano_saude/remover/{id}', 'PlanoSaudeAPIController@removerPlanoSaude');
Route::post('plano_saude/redefinir', 'PlanoSaudeAPIController@redefinirPlanoSaude');
// Route::get('plano_saude/{id}/edit', 'PlanoSaudeAPIController@edit');


//Testes
Route::get('download', 'PedidoReembolsoAPIController@testeCustomHelpers');


// Overview Empresa

Route::get('overview', 'OverviewAPIcontroller@index');
Route::get('overview/servicos/{startDate}/{endDate}/{usId}/{farmId}', 'OverviewAPIcontroller@indexServicos');
Route::get('overview/servicos_farmacia/{startDate}/{endDate}/{farmId}/{usId}', 'OverviewAPIcontroller@indexServicosFarm');
Route::get('overview/bene_faixas/{startDate}/{endDate}', 'OverviewAPIcontroller@indexBeneficiario');
Route::get('overview/index_dashboard/{startDate}/{endDate}/{farmId}/{usId}', 'OverviewAPIcontroller@indexDashboard');

// Orcamento Empresa

Route::get('orcamentos', 'OrcamentoEmpresaController@index');
Route::post('orcamento', 'OrcamentoEmpresaController@store');
// Route::get('beneficiarios/create', 'BeneficiarioAPIController@create');
Route::put('orcamento/{id}', 'OrcamentoEmpresaController@update');
Route::get('orcamento/{id}', 'OrcamentoEmpresaController@show');
Route::get('orcamentos/executados', 'OrcamentoEmpresaController@indexOrcamentoExecutado');
Route::delete('orcamento/{id}', 'OrcamentoEmpresaController@destroy');
// Route::delete('beneficiarios/{id}', 'BeneficiarioAPIController@destroy');