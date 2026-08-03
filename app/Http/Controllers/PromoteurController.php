<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promoteur;

class PromoteurController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $promoteurs = Promoteur::paginate($perPage);

        return response()->json($promoteurs);
    }
}
