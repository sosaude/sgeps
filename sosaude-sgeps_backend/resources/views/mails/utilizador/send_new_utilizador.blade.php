@component('mail::message')
# Saudações!

Foi registado(a) na sua organização um(a) novo(a) colaborador(a) com o nome <b>{{$nome}}</b> na plataforma SGEPS. 

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
