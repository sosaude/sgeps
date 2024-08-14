<!DOCTYPE html>
<html>
<strong>iFarmacias</strong>
<br><br>
Nome: {{$user->nome}}
<br><br>
@if($user->codigo_login)
Código Login: {{$user->codigo_login}}
@elseif($user->email)
Email Login: {{$user->email}}
@endif
<br>
Password: <strong>{{$hash}}</strong>

</html>