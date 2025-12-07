<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campi;

class CampiController extends Controller
{
    // GET ALL Campi
    public function index()
    {
        return response()->json(Campi::all(), 200);
    }

}
