<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use App\Models\Beneficiario;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Carbon;

class BeneficiarioImport implements ToModel, WithHeadingRow
{
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd('jj');
        
        return new Beneficiario(
            [
                // 'nome' => $row['nome'],
                // 'activo' => $row['activo'],
                // 'numero_identificacao' => $row['numero_identificacao'],
                // 'email' => $row['email'],
                // 'numero_beneficiario' => $row['numero_beneficiario'],
                // 'endereco' => $row['endereco'],
                // 'bairro' => $row['bairro'],
                // 'telefone' => $row['telefone'],
                // 'genero' => $row['genero'],
                // 'data_nascimento' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['data_nascimento'])),
                // 'ocupacao' => $row['ocupacao'],
                // 'aposentado' => $row['aposentado'],
                // 'tem_dependentes' => $row['tem_dependentes'],
                // 'doenca_cronica' => $row['doenca_cronica'],
                // 'doenca_cronica_nome' => $row['doenca_cronica_nome']
            ]);

        
    }

    
}
