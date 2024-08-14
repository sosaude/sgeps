@component('mail::message')
Bem-vindo(a) <b>{{$nome}}</b>

Foi registado(a) no SGEPS com o perfil de <b>{{$perfil}}</b>.

Abaixo encontre as credenciais da sua conta de Beneficiário:

{{$identificador_login_campo}} : {{$identificador_login_valor}}

Senha : {{$plain_password}}

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
