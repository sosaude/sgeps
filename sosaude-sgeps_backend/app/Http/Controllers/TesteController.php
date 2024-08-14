<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TesteController extends Controller
{
    //
    public function welcome()
    {
        return view('welcome');
    }

    public function testeWeb()
    {
        dd('Teste Web');
    }
    
    
}
