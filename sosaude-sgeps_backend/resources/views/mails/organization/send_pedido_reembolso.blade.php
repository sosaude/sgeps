@component('mail::message')
# Saudações!

O Pedido de Reembolso em nome do membro principal <b>{{$membro_principal}}</b> encontra-se no estado <b>{{$estado_pedido_reembolso}}</b>.

ID do processo: {{$id_processo}}.

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
