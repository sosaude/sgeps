@component('mail::message')
Bem-vindo(a) <b>{{$nome}}</b>

Foi registado(a) no SGEPS com o perfil de <b>{{$perfil}}</b>.

Use as credenciais abaixo para aceder ao SGPES e posteriormente poderá definir uma nova senha.

{{$identificador_login_campo}} : {{$identificador_login_valor}}

Senha : {{$plain_password}}

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
