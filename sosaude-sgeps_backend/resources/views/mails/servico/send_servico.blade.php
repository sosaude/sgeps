@component('mail::message')
# Saudações!

Foi registado um novo Serviço na plataforma SGEPS com as seguintes carateristicas:

Nome do Serviço: {{ $nome }}

Categoria: {{ $categoria }}

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
