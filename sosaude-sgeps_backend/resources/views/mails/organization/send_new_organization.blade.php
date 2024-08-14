@component('mail::message')
# Saudações!

Foi registada uma nova {{$categoria}} com o nome <b>{{$organizacao}}</b> na plataforma SGEPS. 

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
