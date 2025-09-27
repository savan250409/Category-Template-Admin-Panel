<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            session([
                'ADMIN_LOGIN' => true,
                'ADMIN_ID' => $admin->id,
            ]);
            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid email or password');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/')->with('success', 'Logged out successfully');
    }

    public function index(Request $request)
    {
        if ($request->session()->has('ADMIN_LOGIN')) {
            return redirect('/dashboard');
        }

        return view('login')->with('error', 'please check email and password');
    }

    public function dashboard(Request $request)
    {
        if (!$request->session()->has('ADMIN_LOGIN')) {
            return redirect('/')->with('error', 'please check email and password');
        }

        // $totalFrames = Frame::count();
        // $totalLanguages = Frame::distinct('language_name')->count('language_name');
        // $totalCategories = Frame::distinct('category_name')->count('category_name');

        return view('dashboard');
    }
}
