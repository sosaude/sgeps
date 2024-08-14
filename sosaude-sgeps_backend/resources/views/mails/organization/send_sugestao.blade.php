@component('mail::message')
# Saudações!

Foi submetida uma nova sugestão na plataforma SGEPS com o conteúdo abaixo, queira por favor aceder à plataforma e verificar.

{{ $conteudo }}

@component('mail::button', ['url' => config('app.url')])
Para aceder ao sistema clique aqui
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
