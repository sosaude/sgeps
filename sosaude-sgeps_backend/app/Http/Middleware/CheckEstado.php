<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use App\Models\BaixaFarmacia;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\EstadoPedidoReembolso;
use App\Models\PedidoReembolso;
use Closure;

class CheckEstado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $url = $request->url();
        $url_array = explode('/', $url);

        if (in_array('baixas', $url_array)) {

            if (in_array('empresa', $url_array)) {
                $request->validate(['id' => 'required|integer', 'empresa_id' => 'required|integer', 'proveniencia' => 'required|integer']);

                if ($request->proveniencia == 1) {
                    $baixa = BaixaFarmacia::byEmpresa($request->empresa_id)->find($request->id);
                    if (!$baixa) {
                        return response()->json(Helper::makeError('Baixa não encontrada!'), 404);
                    }
                } elseif ($request->proveniencia == 2) {
                    $baixa = BaixaUnidadeSanitaria::byEmpresa($request->empresa_id)->find($request->id);
                    if (!$baixa) {
                        return response()->json(Helper::makeError('Baixa não encontrada!'), 404);
                    }
                }

                if ($baixa->estado == 3) {
                    return response()->json(Helper::makeError("A Baixa não pode ser alterada, pois já encontra no estado $baixa->estado_texto"), 400);
                }

                $request['baixa'] = $baixa;
            }
        } elseif (in_array('pedidos_reembolso', $url_array)) {

            if (in_array('empresa', $url_array)) {

                $request->validate(['id' => 'required|integer', 'empresa_id' => 'required|integer']);
                $pedido_rembolso = PedidoReembolso::byEmpresa($request->empresa_id)->find($request->id);
                $estado_pagamento_processado = EstadoPedidoReembolso::where('codigo', '12')->first();

                if (!$pedido_rembolso) {
                    return response()->json(Helper::makeError('Pedido de Reembolso não encontrado!'), 404);
                }

                if (empty($estado_pagamento_processado))
                    return response()->json(Helper::makeError('Estado do Pedido não identificado! Contacte o Administrador.'), 404);

                /* if ($pedido_rembolso->estado == 3) {
                    return response()->json(Helper::makeError("Pedido de Reembolso não pode ser alterado, pois já encontra no estado $pedido_rembolso->estado_texto"), 400);
                } */
                if ($pedido_rembolso->estado_pedido_reembolso_id == $estado_pagamento_processado->id) {
                    return response()->json(Helper::makeError("Pedido de Reembolso não pode ser alterado, pois já encontra no estado $estado_pagamento_processado->nome"), 400);
                }

                $request['pedido_reembolso'] = $pedido_rembolso;
            }
        }
        // dd($request->all());

        return $next($request);
    }
}
