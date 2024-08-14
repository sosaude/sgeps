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

Route::post('register', 'AuthAPIController@register');

// Farmacias
Route::get('farmacias', 'FarmaciaAPIController@index');
Route::post('farmacias', 'FarmaciaAPIController@store');
Route::get('farmacias/{id}', 'FarmaciaAPIController@show');
Route::delete('farmacias/{id}', 'FarmaciaAPIController@destroy');
Route::put('farmacias/{id}', 'FarmaciaAPIController@update');
Route::get('farmacias/utilizadores/{id}', 'FarmaciaAPIController@utilizadores');

//Utilizador Farmacia
Route::get('utilizador_farmacia', 'UtilizadorFarmaciaAPIController@index');
Route::get('utilizador_farmacia/create', 'UtilizadorFarmaciaAPIController@create');
Route::post('utilizador_farmacia', 'UtilizadorFarmaciaAPIController@store');
Route::put('utilizador_farmacia/{id}', 'UtilizadorFarmaciaAPIController@update');
Route::get('utilizador_farmacia/{id}', 'UtilizadorFarmaciaAPIController@show');
Route::delete('utilizador_farmacia/{id}', 'UtilizadorFarmaciaAPIController@destroy');

// Empresas
Route::get('empresas', 'EmpresaAPIController@index');
Route::get('empresas/create', 'EmpresaAPIController@create');
Route::post('empresas', 'EmpresaAPIController@store');
Route::get('empresas/{id}', 'EmpresaAPIController@show');
Route::delete('empresas/{id}', 'EmpresaAPIController@destroy');
Route::put('empresas/{id}', 'EmpresaAPIController@update');
Route::get('empresas/{id}/utilizadores', 'EmpresaAPIController@utilizadores');

//Utilizador Empresa
Route::get('utilizador_empresa', 'UtilizadorEmpresaAPIController@index');
Route::get('utilizador_empresa/create', 'UtilizadorEmpresaAPIController@create');
Route::post('utilizador_empresa', 'UtilizadorEmpresaAPIController@store');
Route::put('utilizador_empresa/{id}', 'UtilizadorEmpresaAPIController@update');
Route::get('utilizador_empresa/{id}', 'UtilizadorEmpresaAPIController@show');
Route::delete('utilizador_empresa/{id}', 'UtilizadorEmpresaAPIController@destroy');

// Clinicas
Route::get('clinicas', 'ClinicaAPIController@index');
Route::post('clinicas', 'ClinicaAPIController@store');
Route::get('clinicas/{id}', 'ClinicaAPIController@show');
Route::delete('clinicas/{id}', 'ClinicaAPIController@destroy');
Route::put('clinicas/{id}', 'ClinicaAPIController@update');
Route::get('clinicas/utilizadores/{id}', 'ClinicaAPIController@utilizadores');

//Utilizador Clinica
Route::get('utilizador_clinica', 'UtilizadorClinicaAPIController@index');
Route::get('utilizador_clinica/create', 'UtilizadorClinicaAPIController@create');
Route::post('utilizador_clinica', 'UtilizadorClinicaAPIController@store');
Route::put('utilizador_clinica/{id}', 'UtilizadorClinicaAPIController@update');
Route::get('utilizador_clinica/{id}', 'UtilizadorClinicaAPIController@show');
Route::delete('utilizador_clinica/{id}', 'UtilizadorClinicaAPIController@destroy');

// UnidadeSanitaria
Route::get('unidades_sanitarias', 'UnidadeSanitariaAPIController@index');
Route::get('unidades_sanitarias/create', 'UnidadeSanitariaAPIController@create');
Route::post('unidades_sanitarias', 'UnidadeSanitariaAPIController@store');
Route::get('unidades_sanitarias/{id}', 'UnidadeSanitariaAPIController@show');
Route::delete('unidades_sanitarias/{id}', 'UnidadeSanitariaAPIController@destroy');
Route::put('unidades_sanitarias/{id}', 'UnidadeSanitariaAPIController@update');
Route::get('unidades_sanitarias/{id}/utilizadores', 'UnidadeSanitariaAPIController@utilizadores');

//Utilizador UnidaddeSanitaria
Route::get('utilizador_unidade_sanitaria', 'UtilizadorUnidadeSanitariaAPIController@index');
Route::get('utilizador_unidade_sanitaria/create', 'UtilizadorUnidadeSanitariaAPIController@create');
Route::post('utilizador_unidade_sanitaria', 'UtilizadorUnidadeSanitariaAPIController@store');
Route::put('utilizador_unidade_sanitaria/{id}', 'UtilizadorUnidadeSanitariaAPIController@update');
Route::get('utilizador_unidade_sanitaria/{id}', 'UtilizadorUnidadeSanitariaAPIController@show');
Route::delete('utilizador_unidade_sanitaria/{id}', 'UtilizadorUnidadeSanitariaAPIController@destroy');



//Utilizador Administracao
Route::get('utilizador_admin', 'UtilizadorAdministracaoAPIController@index');
Route::get('utilizador_admin/create', 'UtilizadorAdministracaoAPIController@create');
Route::post('utilizador_admin', 'UtilizadorAdministracaoAPIController@store');
Route::put('utilizador_admin/{id}', 'UtilizadorAdministracaoAPIController@update');
Route::get('utilizador_admin/{id}', 'UtilizadorAdministracaoAPIController@show');
Route::delete('utilizador_admin/{id}', 'UtilizadorAdministracaoAPIController@destroy');
Route::post('utilizador_admin/test_mail', 'UtilizadorAdministracaoAPIController@testMail'); // teste de email teste

//Medicamento
Route::get('medicamentos', 'MedicamentoAPIController@index');
Route::get('medicamentos/create', 'MedicamentoAPIController@create');
Route::post('medicamentos', 'MedicamentoAPIController@store');
Route::get('medicamentos/{id}', 'MedicamentoAPIController@show');
Route::put('medicamentos/{id}', 'MedicamentoAPIController@update');
Route::delete('medicamentos/{id}', 'MedicamentoAPIController@destroy');
Route::get('medicamentos/marcas/{id}', 'MedicamentoAPIController@mracasMedicamento');

// Medicamento Nome Generico
Route::get('nome_generico_medicamentos', 'NomeGenericoMedicamentoAPIController@index');
Route::post('nome_generico_medicamentos', 'NomeGenericoMedicamentoAPIController@store');
Route::get('nome_generico_medicamentos/{id}', 'NomeGenericoMedicamentoAPIController@show');
Route::put('nome_generico_medicamentos/{id}', 'NomeGenericoMedicamentoAPIController@update');
Route::delete('nome_generico_medicamentos/{id}', 'NomeGenericoMedicamentoAPIController@destroy');

// Medicamento Grupos
Route::get('grupos_medicamentos', 'GrupoMedicamentoAPIController@index');
Route::post('grupos_medicamentos', 'GrupoMedicamentoAPIController@store');
Route::get('grupos_medicamentos/{id}', 'GrupoMedicamentoAPIController@show');
Route::put('grupos_medicamentos/{id}', 'GrupoMedicamentoAPIController@update');
Route::delete('grupos_medicamentos/{id}', 'GrupoMedicamentoAPIController@destroy');

// Medicamento Sub-Grupos
Route::get('sub_grupos_medicamentos', 'SubGrupoMedicamentoAPIController@index');
Route::post('sub_grupos_medicamentos', 'SubGrupoMedicamentoAPIController@store');
Route::get('sub_grupos_medicamentos/{id}', 'SubGrupoMedicamentoAPIController@show');
Route::put('sub_grupos_medicamentos/{id}', 'SubGrupoMedicamentoAPIController@update');
Route::delete('sub_grupos_medicamentos/{id}', 'SubGrupoMedicamentoAPIController@destroy');

// Medicamento Sub-Grupos
Route::get('sub_classes_medicamentos', 'SubClasseMedicamentoAPIController@index');
Route::post('sub_classes_medicamentos', 'SubClasseMedicamentoAPIController@store');
Route::get('sub_classes_medicamentos/{id}', 'SubClasseMedicamentoAPIController@show');
Route::put('sub_classes_medicamentos/{id}', 'SubClasseMedicamentoAPIController@update');
Route::delete('sub_classes_medicamentos/{id}', 'SubClasseMedicamentoAPIController@destroy');

// Medicamento Formas
Route::get('formas_medicamentos', 'FormaMedicamentoAPIController@index');
Route::post('formas_medicamentos', 'FormaMedicamentoAPIController@store');
Route::get('formas_medicamentos/{id}', 'FormaMedicamentoAPIController@show');
Route::put('formas_medicamentos/{id}', 'FormaMedicamentoAPIController@update');
Route::delete('formas_medicamentos/{id}', 'FormaMedicamentoAPIController@destroy');

//Marca-Medicamento
Route::get('marca_medicamentos', 'MarcaMedicamentoAPIController@index');
Route::get('marca_medicamentos/create', 'MarcaMedicamentoAPIController@create');
Route::post('marca_medicamentos', 'MarcaMedicamentoAPIController@store');
Route::get('marca_medicamentos/{id}', 'MarcaMedicamentoAPIController@show');
Route::put('marca_medicamentos/{id}', 'MarcaMedicamentoAPIController@update');
Route::delete('marca_medicamentos/{id}', 'MarcaMedicamentoAPIController@destroy');


//Categorias de Servicos
Route::get('categorias_servicos', 'CategoriaServicoAPIController@index');
Route::get('categorias_servicos/create', 'CategoriaServicoAPIController@create');
Route::post('categorias_servicos', 'CategoriaServicoAPIController@store');
Route::get('categorias_servicos/{id}', 'CategoriaServicoAPIController@show');
Route::put('categorias_servicos/{id}', 'CategoriaServicoAPIController@update');
Route::delete('categorias_servicos/{id}', 'CategoriaServicoAPIController@destroy');

//Servicos
Route::get('servicos', 'ServicoAPIController@index');
Route::get('servicos/create', 'ServicoAPIController@create');
Route::post('servicos', 'ServicoAPIController@store');
Route::get('servicos/{id}', 'ServicoAPIController@show');
Route::put('servicos/{id}', 'ServicoAPIController@update');
Route::delete('servicos/{id}', 'ServicoAPIController@destroy');

//sugesto
Route::get('sugestao', 'SugestaoAPIController@index');
Route::post('sugestao', 'SugestaoAPIController@store');
Route::delete('sugestao/{id}', 'SugestaoAPIController@destroy');

// Overview Empresa
Route::get('overview', 'OverviewAPIcontroller@index');
