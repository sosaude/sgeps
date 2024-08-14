@component('mail::message')
# Saudações!

Foram associadas as Organizações abaixo à sua Organização:

Farmácias:

@foreach($farmacias as $farmacia)
    {{$farmacia->nome}}
@endforeach


Unidades Sanitárias:

@foreach($unidades_sanitarias as $unidade_sanitaria)
    {{$unidade_sanitaria->nome}}
@endforeach



@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
