<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoReembolso extends Model
{
    protected $fillable = [
        'empresa_id',
        'unidade_sanitaria',
        'servico_prestado',
        'nr_comprovativo',
        'responsavel',
        'valor',
        'data',
        'comprovativo',
        'beneficio_proprio_beneficiario',
        'beneficiario_id',
        'dependente_beneficiario_id',
        // 'estado',
        'estado_pedido_reembolso_id',
        'comentario'
    ];

    // protected $appends = ['estado_texto'];
    protected $appends = ['comprovativo_link'];

    public $estado_array = [
        'Aguardando confirmação' => 1,
        'Aguardando Pagamento' => 2,
        'Pagamento Processado' => 3,
        'Aguardando Correcção' => 4,
    ];

    public $accao_array = [
        'Submeter Pedido Ressmbolso' => 1,
        'Processar Pagamento Pedido Reembolso' => 2,
        'Devolver Pedido Reembolso' => 3,
    ];

    protected $cast = [
        'unidade_sanitaria' => 'string',
        'servico_prestado' => 'string',
        'nr_comprovativo' => 'string',
        'valor' => 'decimal',
        'data' => 'date',
        'comprovativo' => 'array',
        'comentario' => 'array',
        'responsavel' => 'array',
        'beneficio_proprio_beneficiario' => 'boolean',
        'beneficiario_id' => 'integer',
        'dependente_beneficiario_id' => 'integer',
    ];


    public function estadoPedidoReembolso()
    {
        return $this->belongsTo(EstadoPedidoReembolso::class);
    }

    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function dependenteBeneficiario()
    {
        return $this->belongsTo(DependenteBeneficiario::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function setComprovativoAttribute($value)
    {
        $this->attributes['comprovativo'] = json_encode($value);
    }

    public function setComentarioAttribute($value)
    {
        $this->attributes['comentario'] = json_encode($value);
    }

    public function setResponsavelAttribute($value)
    {
        $this->attributes['responsavel'] = json_encode($value);
    }

    /* public function getEstadoTextoAttribute()
    {
        return array_search($this->attributes['estado'], $this->estado_array);
    } */

    public function getComprovativoAttribute()
    {
        return json_decode($this->attributes['comprovativo']);
    }

    public function getComprovativoLinkAttribute()
    {
        // dd('i');
        $comprovativo_temp = [];
        // return json_decode($this->attributes['comprovativo']);
        $comprovativos = json_decode($this->attributes['comprovativo']);
        if (is_array($comprovativos)) {
            foreach ($comprovativos as $comprovativo) {

                if (!$this->empresa)
                    continue;
                    
                // $url = env('APP_URL') . '/storage/' . $this->getUploadedFileDirectory() . '/' . $comprovativo;
                $url = $this->getUploadedFileDirectory(). $comprovativo;
                $comprovativo = $url;

                array_push($comprovativo_temp, $comprovativo);
            }
        }

        // dd(gettype($comprovativos));


        return $comprovativo_temp;
    }

    public function getComentarioAttribute()
    {
        return json_decode($this->attributes['comentario']);
    }

    public function getResponsavelAttribute()
    {
        return json_decode($this->attributes['responsavel']);
    }

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }

    public function scopeByBeneficiario($query, $beneficiario_id)
    {
        return $query->where('beneficiario_id', $beneficiario_id);
    }

    public function scopeByDependenteBeneficiario($query, $dependente_beneficiario_id)
    {
        return $query->where('dependente_beneficiario_id', $dependente_beneficiario_id);
    }

    /* public function getComprovativoAttribute()
    {
        $url = '';
        $foto_perfil = $this->attributes['foto_perfil'];
        if(!empty($foto_perfil)) {
            $url = env('APP_URL').'/storage'.$this->getUploadedFileDirectory().'/'.$this->attributes['foto_perfil'];
        }
        return $url;
    } */

    public function getUploadedFileDirectory()
    {
        // return kebab_case($this->empresa->nome) . '-' . $this->empresa->id . '/pedidos-reembolso/' . $this->getKey();
        $relative_path = storage_path_empresa($this->empresa->nome, $this->empresa->id, 'pedidos-reembolso');
        return config('custom.s3.aws_url')."/".$relative_path.$this->getKey()."/";
    }
}
