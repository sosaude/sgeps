@component('mail::message')
# Saudações!

Foi registado um novo medicamento na plataforma SGEPS com as seguintes carateristicas:

Nome Génerico: {{ $nome_generico }}

Forma: {{ $forma }}

Dosagem: {{ $dosagem }}

Grupo: {{ $grupo }}

Sub-Grupo: {{ $subgrupo }}

Sub-Classe: {{ $subclasse }}

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
