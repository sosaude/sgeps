<?php

namespace App\Http\Controllers\API\Empresa;

use App\Models\Clinica;
use App\Models\Empresa;
use App\Models\Farmacia;
use Illuminate\Http\Request;
use App\Models\BaixaFarmacia;
use App\Models\UnidadeSanitaria;
use Illuminate\Support\Facades\DB;
use App\Mail\SendLinkedOrganization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Models\BaixaUnidadeSanitaria;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\AppBaseController;

class EmpresaAPIController extends AppBaseController
{
    private $empresa;
    private $farmacia;
    private $clinica;
    private $unidade_sanitaria;
    private $baixa_farmacia;
    private $baixa_unidade_sanitaria;

    public function __construct(
        Empresa $empresa, 
        Farmacia $farmacia, 
        Clinica $clinica, 
        BaixaFarmacia $baixa_farmacia, 
        BaixaUnidadeSanitaria $baixa_unidade_sanitaria,
        UnidadeSanitaria $unidade_sanitaria)
    {
        $this->empresa = $empresa;
        $this->farmacia = $farmacia;
        $this->clinica = $clinica;
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->baixa_farmacia = $baixa_farmacia;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
    }

    public function indexTodasFarmacias()
    {
        if (Gate::denies('gerir farmácia')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $farmacias = $this->farmacia->get(['id', 'nome']);

        return $this->sendResponse($farmacias->toArray(), 200);
    }

    public function indexFarmaciasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir farmácia')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = $request->empresa_id;

        $empresa = $this->empresa->where('id', $empresa_id)->with('farmacias')->first();

        if (empty($empresa)) {
            return $this->sendError('Farmacias Not Found!', 404);
        }

        $farmacias = $empresa->farmacias->map(function ($farmacia) {
            return $farmacia->only(['id', 'nome']);
        });

        return $this->sendResponse($farmacias->toArray(), 200);
    }

    public function associarFarmaciasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir farmácia')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $validator = $request->validate([
            'farmacias' => 'nullable|array',
        ]);

        $empresa_id = $request->empresa_id;
        $farmacias = $request->farmacias;

        $empresa = $this->empresa->where('id', $empresa_id)->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa Not Found!', 404);
        }

        DB::beginTransaction();
        try {

            $empresa->farmacias()->detach();
            $empresa->farmacias()->attach($farmacias);
            $empresa->load(['beneficiarios:id,nome,email', 'farmacias:id,nome,email', 'unidadesSanitarias:id,nome,email']);
            $data = ['farmacias' => $empresa->farmacias];

            $beneficiarios_emails = $empresa->beneficiarios()->where('email', '!=', null)->pluck('email');
            $farmacias = $empresa->farmacias;
            $unidades_sanitarias = $empresa->unidadesSanitarias;
            $when = now()->addSeconds(10);

            foreach ($beneficiarios_emails as $key => $email) {
                Mail::to($email)->later($when, new SendLinkedOrganization($farmacias, $unidades_sanitarias));
            }
            
            
            DB::commit();
            return $this->sendResponse($data, 'Farmácias attached successfully', 200);

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function desassociarFarmaciasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir farmácia')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $validator = Validator::make($request->all(), [
            'farmacias' => function ($attribue, $value, $fail) {
                if (!(is_array($value)) && !(is_int($value))) {
                    $fail($attribue . ' must be an integer or an array!');
                }
            },
            'farmacias' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendErrorValidation($validator->errors(), 422);
        }

        $empresa_id = $request->empresa_id;
        $farmacias = $request->farmacias;

        $empresa = $this->empresa->where('id', $empresa_id)->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa Not Found!', 404);
        }

        DB::beginTransaction();
        try {

            $empresa->farmacias()->detach($farmacias);
            $empresa->load(['beneficiarios:id,nome,email', 'farmacias:id,nome,email', 'unidadesSanitarias:id,nome,email']);
            $data = ['farmacias' => $empresa->farmacias];

            $beneficiarios_emails = $empresa->beneficiarios()->where('email', '!=', null)->pluck('email');
            $farmacias = $empresa->farmacias;
            $unidades_sanitarias = $empresa->unidadesSanitarias;
            $when = now()->addSeconds(10);

            foreach ($beneficiarios_emails as $key => $email) {
                Mail::to($email)->later($when, new SendLinkedOrganization($farmacias, $unidades_sanitarias));
            }

            DB::commit();
            return $this->sendResponse($data, 'Farmácias detached successfully', 200);

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 500);
        }
    }

    // Clinicas

    public function indexTodasClinicas()
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        
        $clinicas = $this->clinica->get(['id', 'nome']);

        return $this->sendResponse($clinicas->toArray(), 200);
    }

    public function indexClinicasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = $request->empresa_id;

        $empresa = $this->empresa->where('id', $empresa_id)->with('clinicas')->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa não encontrada!', 404);
        }

        $clinicas = $empresa->clinicas->map(function ($clinica) {
            return $clinica->only(['id', 'nome']);
        });

        return $this->sendResponse($clinicas->toArray(), 200);
    }

    public function associarClinicasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $validator = $request->validate([
            'clinicas' => 'nullable|array',
        ]);

        $empresa_id = $request->empresa_id;
        $clinicas = $request->clinicas;

        $empresa = $this->empresa->where('id', $empresa_id)->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa Not Found!', 404);
        }

        DB::beginTransaction();
        try {
            $empresa->clinicas()->detach();
            $empresa->clinicas()->attach($clinicas);
            $data = ['clinicas' => $empresa->clinicas];
            DB::commit();
            return $this->sendResponse($data, 'Clínicas attached successfully', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace(), 500);
        }
    }

    public function desassociarClinicasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $validator = Validator::make($request->all(), [
            'clinicas' => function ($attribue, $value, $fail) {
                if (!(is_array($value)) && !(is_int($value))) {
                    $fail($attribue . ' must be an integer or an array!');
                }
            },
        ]);

        if ($validator->fails()) {
            return $this->sendErrorValidation($validator->errors(), 422);
        }

        $empresa_id = $request->empresa_id;
        $clinicas = $request->clinicas;

        $empresa = $this->empresa->where('id', $empresa_id)->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa Not Found!', 404);
        }

        DB::beginTransaction();
        try {

            $empresa->clinicas()->detach($clinicas);
            $data = ['clinicas' => $empresa->clinicas];
            DB::commit();
            return $this->sendResponse($data, 'Clínicas detached successfully', 200);

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 500);
        }
    }

    // Unidades Sanitarias

    public function indexTodasUnidadesSanitarias()
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidades_sanitarias = $this->unidade_sanitaria->get(['id', 'nome']);

        return $this->sendResponse($unidades_sanitarias->toArray(), 200);
    }

    public function indexUnidadesSanitariasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = $request->empresa_id;

        $empresa = $this->empresa->where('id', $empresa_id)->with('unidadesSanitarias')->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa não encontrdad.');
        }

        $unidades_sanitarias = $empresa->unidadesSanitarias->map(function ($clinica) {
            return $clinica->only(['id', 'nome']);
        });

        return $this->sendResponse($unidades_sanitarias->toArray(), 200);
    }

    public function associarUnidadesSanitariasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // dd($request->all());
        $request->validate([
            'unidades_sanitarias' => 'nullable|array',
        ]);

        $empresa_id = $request->empresa_id;
        $unidades_sanitarias = $request->unidades_sanitarias;

        $empresa = $this->empresa->where('id', $empresa_id)->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa não encontrada!', 404);
        }

        DB::beginTransaction();
        try {
            $empresa->unidadesSanitarias()->detach();
            $empresa->unidadesSanitarias()->attach($unidades_sanitarias);
            $empresa->load(['beneficiarios:id,nome,email', 'farmacias:id,nome,email', 'unidadesSanitarias:id,nome,email']);
            $data = ['unidades_sanitarias' => $empresa->unidadesSanitarias];

            $beneficiarios_emails = $empresa->beneficiarios()->where('email', '!=', null)->pluck('email');
            $farmacias = $empresa->farmacias;
            $unidades_sanitarias = $empresa->unidadesSanitarias;
            $when = now()->addSeconds(10);

            foreach ($beneficiarios_emails as $key => $email) {
                Mail::to($email)->later($when, new SendLinkedOrganization($farmacias, $unidades_sanitarias));
            }

            DB::commit();
            return $this->sendResponse($data, 'Unidades Sanitárias attached successfully', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace(), 500);
        }
    }


    public function desassociarUnidadesSanitariasDaEmpresa(Request $request)
    {
        if (Gate::denies('gerir unidade sanitária')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        
        $validator = Validator::make($request->all(), [
            'unidades_sanitarias' => function ($attribue, $value, $fail) {
                if (!(is_array($value)) && !(is_int($value))) {
                    $fail($attribue . ' must be an integer or an array!');
                }
            },
            'unidades_sanitarias' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendErrorValidation($validator->errors(), 422);
        }

        $empresa_id = $request->empresa_id;
        $unidades_sanitarias = $request->unidades_sanitarias;

        $empresa = $this->empresa->where('id', $empresa_id)->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa não encontrada!', 404);
        }

        DB::beginTransaction();
        try {

            $empresa->unidadesSanitarias()->detach($unidades_sanitarias);
            $empresa->load(['beneficiarios:id,nome,email', 'farmacias:id,nome,email', 'unidadesSanitarias:id,nome,email']);
            $data = ['unidades_sanitarias' => $empresa->unidadesSanitarias];

            $beneficiarios_emails = $empresa->beneficiarios()->where('email', '!=', null)->pluck('email');
            $farmacias = $empresa->farmacias;
            $unidades_sanitarias = $empresa->unidadesSanitarias;
            $when = now()->addSeconds(10);

            foreach ($beneficiarios_emails as $key => $email) {
                Mail::to($email)->later($when, new SendLinkedOrganization($farmacias, $unidades_sanitarias));
            }

            DB::commit();
            return $this->sendResponse($data, 'Unidades Sanitárias desassociadas com sucesso.', 200);

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 500);
        }
    }

    
}
