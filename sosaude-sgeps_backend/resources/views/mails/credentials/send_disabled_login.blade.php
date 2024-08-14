@component('mail::message')
Caro(a) <b>{{$nome}}</b>

A sua conta foi inactivada devido ao excesso de tentativas de acesso usando credenciais inválidas ou incorrectas.
Queria por favor proceder com o mecanismo de recuperação clicando no link "Clique aqui" que aparece no formulário de login da Plataforma e seguir as instruções subsequentes.

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
