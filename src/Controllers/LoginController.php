<?php

namespace Artadmin\Controllers;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;


class LoginController extends \App\Http\Controllers\Controller
{
    protected $guard = null;
    function __construct()
    {

        $this->guard=config("artadmin.guard");

    }

    function login(){

        $data=array();





        return view('artadmin::login');
    }

    public function doit(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $rateLimitKey = 'artadmin-login:' . hash('sha256', strtolower(trim((string) $credentials['email'])) . '|' . $request->ip());
        $maxAttempts = max(1, (int) config('artadmin.login_max_attempts', 5));
        $decaySeconds = max(1, (int) config('artadmin.login_decay_seconds', 60));

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            return redirect()->route("artadmin.login")->withErrors([trans("artadmin::login.incorrect")]);
        }

        RateLimiter::hit($rateLimitKey, $decaySeconds);

        if (Auth::guard($this->guard)->attempt($credentials)) {
            RateLimiter::clear($rateLimitKey);
            $request->session()->regenerate();
            return redirect()->route(config("artadmin.route_after_login"))
                ->withSuccess(trans("artadmin::login.success"));
        }

        return redirect()->route("artadmin.login")->withErrors([trans("artadmin::login.incorrect")]);
    }


}
