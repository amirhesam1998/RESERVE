<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use Illuminate\Http\Request;

class ReserveController extends Controller
{
    public function index()
    {
        $reserves = Reserve::latest()->get();

        return view('Reserve.index', compact('reserves'));
    }
}
