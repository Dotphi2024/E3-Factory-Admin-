<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){
        $credentials = $request->only('email','password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if(Auth::user()->user_type == 'entry-user'){
                return redirect()->route('entry-user-dashboard');
            }
            // if($user->user_type == 'master-admin'){
                $intendedUrl = session()->get('url.intended', route('master.dashboard'));

            // }
            // else{
            //     $intendedUrl = session()->get('url.intended', route('dashboard'));
            // }
            return redirect()->intended($intendedUrl);
        }
        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
    }
    public function logout(){
        Auth::logout();
        return redirect()->route('login');
    }
}
