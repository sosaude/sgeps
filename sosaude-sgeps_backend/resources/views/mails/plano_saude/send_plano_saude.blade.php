@component('mail::message')
# Saudações!

Caro Beneficiário, o seu plano de saúde foi actualizado!

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent

