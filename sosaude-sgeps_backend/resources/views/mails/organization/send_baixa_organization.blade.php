@component('mail::message')
# Saudações!

O(a) {{$referencia_processo}} em nome do membro principal <b>{{$membro_principal}}</b> encontra-se no estado <b>{{$estado_baixa}}</b>.

Processo proveniente da instituição: {{$instituicao_proveniencia}}.

ID do processo: {{$id_processo}}.

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
