<?php

namespace App\Http\Controllers\API\Empresa;

use Excel;
use Response;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\OrcamentoEmpresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AppBaseController;
use App\Models\Empresa;
use App\Models\BaixaFarmacia;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\UnidadeSanitaria;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdateOrcamentoEmpresaFormRequest;


/**
 * Class OrcamentoEmpresaController
 * @package App\Http\Controllers\API
 */

class OrcamentoEmpresaController extends AppBaseController
{
    private $orcamento_empresa;

    public function __construct(OrcamentoEmpresa $orcamento_empresa) {
        $this->orcamento_empresa = $orcamento_empresa;
    }

       /**
     * Display a listing of the Orcamentos.
     * GET|HEAD /orcamento_empresas
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // if (Gate::denies('gerir beneficiário')) {
        //     return $this->sendError('Esta acção não está autorizada!', 403);
        // }
        $empresa_id = $request->empresa_id;
        $orcamento_empresas = $this->orcamento_empresa->byEmpresa($empresa_id)->get()->map(function ($orcamento) {
            return [
                'id' => $orcamento->id,
                'tipo_orcamento' => $orcamento->tipo_orcamento,
                'orcamento_laboratorio' => $orcamento->orcamento_laboratorio,
                'orcamento_farmacia' => $orcamento->orcamento_farmacia,
                'orcamento_clinica' => $orcamento->orcamento_clinica,
                'ano_de_referencia' => $orcamento->ano_de_referencia,
                'executado' => $orcamento->executado,
            ];
        })->toArray();
        
        return $this->sendResponse($orcamento_empresas, 'Overview retrieved successfully');
    }

     /**
     * Store a newly created Beneficiario in storage.
     * POST /beneficiarios
     *
     * @param CreateUpdateOrcamentoEmpresaFormRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateOrcamentoEmpresaFormRequest $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
      
        $input = $request->all();

        $result_extra_ano_executado_validation = $this->extraAnoExecutadoValidation($input);
        if (sizeof($result_extra_ano_executado_validation) > 0)
            return $this->sendErrorValidation($result_extra_ano_executado_validation, 422);
        
        DB::beginTransaction();
        try {
            /** @var OrcamentoEmpresa $orcamento_empresa */
            $orcamento_empresa = OrcamentoEmpresa::create($input);

            DB::commit();
            return $this->sendResponse($orcamento_empresa, 'Orcamento saved successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

     /**
     * Display the specified Beneficiario.
     * GET|HEAD /beneficiarios/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        
        $empresa_id = request('empresa_id');
         /** @var OrcamentoEmpresa $orcamento_empresa */
        $orcamento_empresas = $this->orcamento_empresa->byEmpresa($empresa_id)->where('id', $id)
        ->first();

        if (empty($orcamento_empresas)) {
            return $this->sendError('Orçamento not found');
        }



        $data = [
            'id' => $orcamento_empresas->id,
            'tipo_orcamento' => $orcamento_empresas->tipo_orcamento,
            'orcamento_laboratorio' => $orcamento_empresas->orcamento_laboratorio,
            'orcamento_farmacia' => $orcamento_empresas->orcamento_farmacia,
            'orcamento_clinica' => $orcamento_empresas->orcamento_clinica,
            'ano_de_referencia' => $orcamento_empresas->ano_de_referencia,
            'executado' => $orcamento_empresas->executado,
        ];

        return $this->sendResponse($data, 'Orçamento retrieved successfully');
    }


    /**
     * Update the specified Beneficiario in storage.
     * PUT/PATCH /beneficiarios/{id}
     *
     * @param int $id
     * @param CreateUpdateOrcamentoEmpresaFormRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateOrcamentoEmpresaFormRequest $request)
    {

        $input = $request->all();

        $empresa_id = $request->empresa_id;

        $orcamento_empresa = $this->orcamento_empresa->byEmpresa($empresa_id)->find($id);
        if (empty($orcamento_empresa))
            return $this->sendError('Orcamento not found', 404);

        $result_extra_ano_executado_validation = $this->extraAnoExecutadoValidation($input);
        if (sizeof($result_extra_ano_executado_validation) > 0)
            return $this->sendErrorValidation($result_extra_ano_executado_validation, 422);

        DB::beginTransaction();
        try {
            // Update the $beneficiario
            $orcamento_empresa->update($input);
            // dd('passou');
            DB::commit();
            return $this->sendResponse($orcamento_empresa->toArray(), 'Orcamento updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

        /**
     * Remove the specified OrcamentoEmpresas from storage.
     * DELETE /orcamento/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $empresa_id = request('empresa_id');
        $orcamento_empresa = $this->orcamento_empresa->byEmpresa($empresa_id)->find($id);
        if (empty($orcamento_empresa))
            return $this->sendError('Orcamento da Empresa não encontrado.');

        DB::beginTransaction();
        try {
            $orcamento_empresa->delete();
            DB::commit();
            return $this->sendSuccess('Orcamento da Empresa removido com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }


       /**
     * Display a listing of the Orcamentos.
     * GET|HEAD /orcamento_executado
     *
     * @param Request $request
     * @return Response
     */
    public function indexOrcamentoExecutado(Request $request){

        $empresa_id = $request->empresa_id;

        $us_executado_valor = DB::select(DB::raw("
        select sum(valor) as valor from (
            select 
                            DISTINCT bus.id,
                            bus.valor
                            from baixa_unidade_sanitarias bus
                            inner join unidade_sanitarias us on us.id = bus.unidade_sanitaria_id
                            inner join categoria_unidade_sanitarias c_us on c_us.id = us.categoria_unidade_sanitaria_id
                            inner join empresa_unidade_sanitaria empus on empus.unidade_sanitaria_id = us.id
                            inner join empresas emp on emp.id = empus.empresa_id
                            inner join iten_baixa_unidade_sanitarias ibus on ibus.baixa_unidade_sanitaria_id = bus.id
                            inner join servicos serv on serv.id = ibus.servico_id
                            inner join estado_baixas eb on eb.id = bus.estado_baixa_id 
            WHERE bus.estado_baixa_id  = 6 and bus.empresa_id = $empresa_id and c_us.id = 1
            ) as tt
        "));

        $lab_executado_valor = DB::select(DB::raw("
        select sum(valor) as valor from (
            select 
                            DISTINCT bus.id,
                            bus.valor
                            from baixa_unidade_sanitarias bus
                            inner join unidade_sanitarias us on us.id = bus.unidade_sanitaria_id
                            inner join categoria_unidade_sanitarias c_us on c_us.id = us.categoria_unidade_sanitaria_id
                            inner join empresa_unidade_sanitaria empus on empus.unidade_sanitaria_id = us.id
                            inner join empresas emp on emp.id = empus.empresa_id
                            inner join iten_baixa_unidade_sanitarias ibus on ibus.baixa_unidade_sanitaria_id = bus.id
                            inner join servicos serv on serv.id = ibus.servico_id
                            inner join estado_baixas eb on eb.id = bus.estado_baixa_id 
            WHERE bus.estado_baixa_id  = 6 and bus.empresa_id = $empresa_id and c_us.id = 2
            ) as tt
        "));

        $farmacia_executado_valor = DB::select(DB::raw("
        select sum(valor) as valor from (
            select 
                            DISTINCT bfarm.id,
                            bfarm.valor
                            from baixa_farmacias bfarm
                            inner join farmacias farm on farm.id = bfarm.farmacia_id
                            inner join empresa_farmacia empfar on empfar.farmacia_id = farm.id
                            inner join empresas emp on emp.id = empfar.empresa_id
                            inner join estado_baixas eb on eb.id = bfarm.estado_baixa_id 
            WHERE bfarm.estado_baixa_id  = 6 and bfarm.empresa_id = $empresa_id
            ) as tt
        "));

        $ex_us = 0;
        $ex_lab = 0;
        $ex_farmacia = 0;

        foreach($us_executado_valor as $exus){
            $ex_us = intval($exus->valor);
        }
        foreach($lab_executado_valor as $exlab){
            $ex_lab = intval($exlab->valor);
        }
        foreach($farmacia_executado_valor as $exfarm){
            $ex_farmacia = intval($exfarm->valor);
        }

        $results = DB::select(DB::raw("
        
        SELECT
        'Clinica' as categoria,
        max(IF( oe.ano_de_referencia = 2022 and oe.executado = 0, oe.orcamento_clinica, 0)) AS or1,
        IFNULL($ex_us, 0) AS ex1,
        max(IF( oe.ano_de_referencia = 2021 and oe.executado = 0, oe.orcamento_clinica, 0)) AS or2,
        max(IF( oe.ano_de_referencia = 2021 and oe.executado = 1, oe.orcamento_clinica, 0)) AS ex2,
        max(IF(oe.ano_de_referencia = 2020 and oe.executado = 0, oe.orcamento_clinica, 0)) AS or3,
        max(IF(oe.ano_de_referencia = 2020 and oe.executado = 1, oe.orcamento_clinica, 0)) AS ex3
        FROM orcamento_empresas oe
        where oe.empresa_id = $empresa_id
		group by categoria
union all
SELECT
        'Farmacia' as categoria,
        max(IF( oe.ano_de_referencia = 2022 and oe.executado = 0, oe.orcamento_farmacia, 0)) AS or1,
        IFNULL($ex_farmacia, 0) AS ex1,
        max(IF( oe.ano_de_referencia = 2021 and oe.executado = 0, oe.orcamento_farmacia, 0)) AS or2,
        max(IF( oe.ano_de_referencia = 2021 and oe.executado = 1, oe.orcamento_farmacia, 0)) AS ex2,
        max(IF(oe.ano_de_referencia = 2020 and oe.executado = 0, oe.orcamento_farmacia, 0)) AS or3,
        max(IF(oe.ano_de_referencia = 2020 and oe.executado = 1, oe.orcamento_farmacia, 0)) AS ex3
        FROM orcamento_empresas oe
        where oe.empresa_id = $empresa_id
		group by categoria
        
union all
SELECT
        'Laboratorio' as categoria,
        max(IF( oe.ano_de_referencia = 2022 and oe.executado = 0, oe.orcamento_laboratorio, 0)) AS or1,
        IFNULL($ex_lab, 0) AS ex1,
        max(IF( oe.ano_de_referencia = 2021 and oe.executado = 0, oe.orcamento_laboratorio, 0)) AS or2,
        max(IF( oe.ano_de_referencia = 2021 and oe.executado = 1, oe.orcamento_laboratorio, 0)) AS ex2,
        max(IF(oe.ano_de_referencia = 2020 and oe.executado = 0, oe.orcamento_laboratorio, 0)) AS or3,
        max(IF(oe.ano_de_referencia = 2020 and oe.executado = 1, oe.orcamento_laboratorio, 0)) AS ex3
        FROM orcamento_empresas oe
        where oe.empresa_id = $empresa_id
		group by categoria

union all

        select 'Total' as categoria,sum(or1) as or1,sum(ex1) as ex1,sum(or2) as or2,sum(ex2) as ex2,sum(or3) as or3,sum(ex3) as ex3 from (
        SELECT
                max(IF( oe.ano_de_referencia = 2022 and oe.executado = 0, oe.orcamento_clinica, 0)) AS or1,
                IFNULL($ex_us, 0) AS ex1,
                max(IF( oe.ano_de_referencia = 2021 and oe.executado = 0, oe.orcamento_clinica, 0)) AS or2,
                max(IF( oe.ano_de_referencia = 2021 and oe.executado = 1, oe.orcamento_clinica, 0)) AS ex2,
                max(IF(oe.ano_de_referencia = 2020 and oe.executado = 0, oe.orcamento_clinica, 0)) AS or3,
                max(IF(oe.ano_de_referencia = 2020 and oe.executado = 1, oe.orcamento_clinica, 0)) AS ex3
                FROM orcamento_empresas oe
                where oe.empresa_id = $empresa_id
        union all
        SELECT
                max(IF( oe.ano_de_referencia = 2022 and oe.executado = 0, oe.orcamento_farmacia, 0)) AS or1,
                IFNULL($ex_farmacia, 0) AS ex1,
                max(IF( oe.ano_de_referencia = 2021 and oe.executado = 0, oe.orcamento_farmacia, 0)) AS or2,
                max(IF( oe.ano_de_referencia = 2021 and oe.executado = 1, oe.orcamento_farmacia, 0)) AS ex2,
                max(IF(oe.ano_de_referencia = 2020 and oe.executado = 0, oe.orcamento_farmacia, 0)) AS or3,
                max(IF(oe.ano_de_referencia = 2020 and oe.executado = 1, oe.orcamento_farmacia, 0)) AS ex3
                FROM orcamento_empresas oe
                where oe.empresa_id = $empresa_id
                
        union all
        SELECT
                max(IF( oe.ano_de_referencia = 2022 and oe.executado = 0, oe.orcamento_laboratorio, 0)) AS or1,
                IFNULL($ex_lab, 0) AS ex1,
                max(IF( oe.ano_de_referencia = 2021 and oe.executado = 0, oe.orcamento_laboratorio, 0)) AS or2,
                max(IF( oe.ano_de_referencia = 2021 and oe.executado = 1, oe.orcamento_laboratorio, 0)) AS ex2,
                max(IF(oe.ano_de_referencia = 2020 and oe.executado = 0, oe.orcamento_laboratorio, 0)) AS or3,
                max(IF(oe.ano_de_referencia = 2020 and oe.executado = 1, oe.orcamento_laboratorio, 0)) AS ex3
                FROM orcamento_empresas oe
                where oe.empresa_id = $empresa_id
        
        
        ) as total

 "));


        $LineChartData = [];
        for ($x = 0; $x <= 2; $x++) {
            $year = date('Y');
            $currentYear = $year -$x;
            $te = $this->extractExecutadoChartDataPerYear($empresa_id,$currentYear);
            array_push($LineChartData,$te);

          } 
        
        $chart_labels = [];
        $chart_data = [];
        $dim_data = [];
        foreach($LineChartData as $mt){
            foreach($mt as $mtx){
                $usname = $mtx->ano;
                $dim_data = [intval($mtx->m1),intval($mtx->m2),intval($mtx->m3),intval($mtx->m4),intval($mtx->m5),intval($mtx->m6),intval($mtx->m7),intval($mtx->m8),intval($mtx->m9),intval($mtx->m10),intval($mtx->m11),intval($mtx->m12)];
                array_push($chart_labels, $usname);
                array_push($chart_data, $dim_data);
            }
        }



        $data = [
            'orcamentos' => $results,
            'executadosPorAno' => $LineChartData,
            'linechart_labels' => $chart_labels,
            'linechart_data' => $chart_data
        ];

    return $this->sendResponse($data, 'Overview retrieved successfully');
}

   


public function extractExecutadoChartDataPerYear($empId,$ano)
{




$results = DB::select(DB::raw("

select $ano as ano, sum(m1) as m1,
sum(m2) as m2,
sum(m3) as m3,
sum(m4) as m4,
sum(m5) as m5,
sum(m6) as m6,
sum(m7) as m7,
sum(m8) as m8,
sum(m9) as m9,
sum(m10) as m10,
sum(m11) as m11,
sum(m12) as m12 FROM(
select  
sum(january) as m1,
sum(february) as m2,
sum(march) as m3,
sum(april) as m4,
sum(may) as m5,
sum(june) as m6,
sum(july) as m7,
sum(august) as m8,
sum(september) as m9,
sum(october) as m10,
sum(november) as m11,
sum(december) as m12
  FROM (
    select 
                DISTINCT bus.id,
            CASE WHEN MONTH(bus.created_at) = 1 and YEAR(bus.created_at) =   $ano  and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as january,
            CASE WHEN MONTH(bus.created_at) = 2 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as february,
            CASE WHEN MONTH(bus.created_at) = 3 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as march,
            CASE WHEN MONTH(bus.created_at) = 4 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as april,
            CASE WHEN MONTH(bus.created_at) = 5 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as may,
            CASE WHEN MONTH(bus.created_at) = 6 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as june,
            CASE WHEN MONTH(bus.created_at) = 7 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as july,
            CASE WHEN MONTH(bus.created_at) = 8 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as august,
            CASE WHEN MONTH(bus.created_at) = 8 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as september,
            CASE WHEN MONTH(bus.created_at) = 10 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as october,
            CASE WHEN MONTH(bus.created_at) = 11 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as november,
            CASE WHEN MONTH(bus.created_at) = 12 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as december
               
                from baixa_unidade_sanitarias bus
                inner join unidade_sanitarias us on us.id = bus.unidade_sanitaria_id
                inner join categoria_unidade_sanitarias c_us on c_us.id = us.categoria_unidade_sanitaria_id
                inner join empresa_unidade_sanitaria empus on empus.unidade_sanitaria_id = us.id
                inner join empresas emp on emp.id = empus.empresa_id
                inner join iten_baixa_unidade_sanitarias ibus on ibus.baixa_unidade_sanitaria_id = bus.id
                inner join servicos serv on serv.id = ibus.servico_id
                inner join estado_baixas eb on eb.id = bus.estado_baixa_id 
WHERE bus.estado_baixa_id  = 6 and bus.empresa_id = $empId
) AS tt 

union
select  sum(january) as m1,
sum(february) as m2,
sum(march) as m3,
sum(april) as m4,
sum(may) as m5,
sum(june) as m6,
sum(july) as m7,
sum(august) as m8,
sum(september) as m9,
sum(october) as m10,
sum(november) as m11,
sum(december) as m12
FROM (
select 
        DISTINCT b_farmacias.id,
        CASE WHEN year(b_farmacias.created_at) = 2022 and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as t,
        CASE WHEN year(b_farmacias.created_at) = 2021 and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as tt,
        CASE WHEN YEAR(b_farmacias.created_at) = 2020 and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as ttt,
            CASE WHEN MONTH(b_farmacias.created_at) = 1 and YEAR(b_farmacias.created_at) =  $ano  and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as january,
            CASE WHEN MONTH(b_farmacias.created_at) = 2 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as february,
            CASE WHEN MONTH(b_farmacias.created_at) = 3 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as march,
            CASE WHEN MONTH(b_farmacias.created_at) = 4 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as april,
            CASE WHEN MONTH(b_farmacias.created_at) = 5 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as may,
            CASE WHEN MONTH(b_farmacias.created_at) = 6 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as june,
            CASE WHEN MONTH(b_farmacias.created_at) = 7 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as july,
            CASE WHEN MONTH(b_farmacias.created_at) = 8 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as august,
            CASE WHEN MONTH(b_farmacias.created_at) = 8 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as september,
            CASE WHEN MONTH(b_farmacias.created_at) = 10 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as october,
            CASE WHEN MONTH(b_farmacias.created_at) = 11 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as november,
            CASE WHEN MONTH(b_farmacias.created_at) = 12 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as december
        
        from baixa_farmacias b_farmacias
        inner join farmacias farm on farm.id = b_farmacias.farmacia_id
        inner join empresa_farmacia emp_farm on emp_farm.farmacia_id = farm.id
        inner join empresas emp on emp.id = emp_farm.empresa_id
        inner join estado_baixas eb on eb.id = b_farmacias.estado_baixa_id
WHERE b_farmacias.estado_baixa_id  = 6 and b_farmacias.empresa_id = $empId
) AS tt 
) AS TTT "),["empId" => $empId,"ano" => $ano]);

        return $results;
    }

    protected function extraAnoExecutadoValidation(array $input)
    {
        $errors = [];
        $ano_executado_validation = $this->validarUniqueAnoExecutado($input);

        $errors = $ano_executado_validation;

        return $errors;
    }


    protected function validarUniqueAnoExecutado(array $input)
    {
        $errors = [];

            if (isset($input['id']) && isset($input['ano_de_referencia']) && isset($input['executado'])) {
                $ano = OrcamentoEmpresa::where('ano_de_referencia', $input['ano_de_referencia'])->where('executado', $input['executado'])->where('empresa_id', $input['empresa_id'])->whereNotIn('id', [$input['id']])->count();

                if ($ano > 0) {
                    $errors["ano_de_referencia"] = ['O Ano informado já foi usado para o Executado ou Orcamento!'];
                }
            } else if (isset($input['ano_de_referencia']) && isset($input['executado'])) {
                $ano = OrcamentoEmpresa::where('ano_de_referencia', $input['ano_de_referencia'])->where('executado', $input['executado'])->where('empresa_id', $input['empresa_id'])->count();

                if ($ano > 0) {
                    $errors["ano_de_referencia"] = ['O Ano informado já foi usado para o Executado ou Orcamento!'];
                }
            }
        

        return $errors;
    }


    public function getTotalOrcamento($empId,$ano){
        $orcamentoTotal = DB::table('orcamento_empresas')
        ->select(
        DB::raw('sum(orcamento_laboratorio + orcamento_farmacia + orcamento_clinica ) as totalOrcamento')
        )
        ->where('empresa_id',$empId)
        ->where('ano_de_referencia',$ano)
        ->where('executado',false)
        ->get()->toArray();

        return $orcamentoTotal;
    }


}
