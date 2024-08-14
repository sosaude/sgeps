<?php

namespace App\Http\Controllers\API;

use Excel;
use Response;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Models\DoencaCronica;
use Illuminate\Validation\Rule;
use App\Models\GrupoBeneficiario;
use Illuminate\Support\Facades\DB;
use App\Imports\BeneficiarioImport;
use Illuminate\Support\Facades\Gate;
use App\Models\DependenteBeneficiario;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdateBeneficiarioFormRequest;
use App\Models\BaixaFarmacia;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\Empresa;
use App\Models\UnidadeSanitaria;
use App\Models\Farmacia;

use function PHPSTORM_META\map;

class OverviewAPIcontroller extends AppBaseController
{
    //
    /** @var $beneficiario $dependente_beneficiario */
    private $empresa;
    private $user;
    private $beneficiario;
    private $dependente_beneficiario;
    private $grupo_beneficiario;
    private $doenca_cronica;
    private $baixa_farmacia;
    private $baixa_unidade_sanitaria;
    private $unidade_sanitaria;

    public function __construct(
        Empresa $empresa,
        UnidadeSanitaria $unidade_sanitaria,
        Farmacia $farmacia,
        User $user,
        Beneficiario $beneficiario,
        DependenteBeneficiario $dependente_beneficiario,
        GrupoBeneficiario $grupo_beneficiario,
        DoencaCronica $doenca_cronica,
        BaixaFarmacia $baixa_farmacia,
        BaixaUnidadeSanitaria $baixa_unidade_sanitaria
    ) {
        $this->empresa = $empresa;
        $this->user = $user;
        $this->beneficiario = $beneficiario;
        $this->dependente_beneficiario = $dependente_beneficiario;
        $this->grupo_beneficiario = $grupo_beneficiario;
        $this->doenca_cronica = $doenca_cronica;
        $this->baixa_farmacia = $baixa_farmacia;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->farmacia = $farmacia;
    }

    /**
     * Display a listing of the Beneficiario.
     * GET|HEAD /beneficiarios
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // if (Gate::denies('gerir beneficiário')) {
        //     return $this->sendError('Esta acção não está autorizada!', 403);
        // }
        $beneficiarios = $this->beneficiario->where('activo', true)->get();
        $farmacia = $this->farmacia->where('activa', true)->get();
        $unidade_sanitaria = $this->unidade_sanitaria->get();

        $provedores = array_merge($farmacia->toArray(),$unidade_sanitaria->toArray());

        $data = [
            'nr_beneficiarios' => count($beneficiarios),
            'provedores' => count($provedores)
        ];

        return $this->sendResponse($data, 'Overview retrieved successfully');
    }

}