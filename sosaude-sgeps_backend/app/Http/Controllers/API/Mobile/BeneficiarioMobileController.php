<?php

namespace App\Http\Controllers\API\Mobile;

use App\Models\User;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DependenteBeneficiario;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;

class BeneficiarioMobileController extends AppBaseController
{
    private $beneficiario;
    private $dependente_beneficiario;
    private $user;

    public function __construct(Beneficiario $beneficiario, DependenteBeneficiario $dependente_beneficiario, User $user)
    {
        $this->beneficiario = $beneficiario;
        $this->dependente_beneficiario = $dependente_beneficiario;
        $this->user = $user;
    }
    public function desactivarDependenteBeneficiario(Request $request)
    {
        $request->validate(['dependente_beneficiario_id' => 'required|integer']);
        $beneficiario = Auth::user()->beneficiario;
        // dd($beneficiario->empresa_id);
        if (empty($beneficiario))
            return $this->sendError('O Cliente não está associado a nenhuma conta de Beneficiário ou a sua conta de Beneficiario encontra-se inactiva!', 404);

        $dependente_beneficiario = $this->dependente_beneficiario
            ->where('beneficiario_id', $beneficiario->id)
            ->where('activo', true)
            ->find($request->dependente_beneficiario_id);

        if (empty($dependente_beneficiario))
            return $this->sendError('Dependente não encontrado, ou o mesmo encontra-se inactivo!', 404);

        // dd($dependente_beneficiario);

        DB::beginTransaction();
        try {
            $dependente_beneficiario->activo = false;
            $dependente_beneficiario->save();

            $user = $this->user->find($dependente_beneficiario->user_id);
            if(!empty($user)) {
                $user->active = false;
                $user->save();
            }
                
            DB::commit();
            return $this->sendSuccess('Dependente desactivado sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    public function getDependenteBeneficiario()
    {
        $beneficiario = Auth::user()->beneficiario;
        $dependentes_beneficiario = [];

        if(!empty($beneficiario)) {
            $dependentes_beneficiario = $this->dependente_beneficiario->where('beneficiario_id', $beneficiario->id)->get([
                'id', 'nome', 'activo'
            ]);
        }

        $data = [
            'dependentes_beneficiario' => $dependentes_beneficiario,
        ];

        return $this->sendResponse($data, '', 200);
    }
}
