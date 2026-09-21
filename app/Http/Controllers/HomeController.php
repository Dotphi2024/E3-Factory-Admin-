<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function dashboard()
    {
        return view('dashboard');
    }
    public function stopImpersonate()
    {
        $userId = session('impersonate');
        $centerId = session('impersonate_center_id');
        session()->flush();
        Auth::logout();
        session(['stop_impersonate' => $userId]);
        Auth::loginUsingId($userId);
        return redirect(route('master.center.view-center', $centerId));
    }

}