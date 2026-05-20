<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChargerController extends Controller
{
    public function configuration(Charger $charger)
    {
        return view('chargers.configuration', compact('charger'));
    }
    
    public function ChangeConfiguration(): array
    {
        return  [

        ];
    }


}
