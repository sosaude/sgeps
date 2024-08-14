@component('mail::message')
# Saudações 

Foi registado(a) no SGEPS um(a) novo(a) colaborador(a) de nome <b>{{$nome}}</b>, com o perfil de <b>{{$perfil}}</b>.

Sendo que não possuí uma conta de email registada, as credencias foram enviadas para esta conta de Administrador.

Na qualidade de Administrador informe as credencias recebidas e instrua o novo colaborador a trocar a senha após a primeira autenticação.

Eis as credenciais abaixo:

{{$identificador_login_campo}} : {{$identificador_login_valor}}

Senha : {{$plain_password}}

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
