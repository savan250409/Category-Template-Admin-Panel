<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
{
    return view('dashboard');
}
}
