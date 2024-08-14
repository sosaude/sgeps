@component('mail::message')
# Saudações

Foi registado um novo utilizador no SGEPS tendo sido este notificado pelo sistema. Eis os ddados do utilizador:

Nome : {{$nome}}

Perfil : {{$perfil}}

@component('mail::button', ['url' => config('app.url')])
Para aceder ao SGEPS clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
