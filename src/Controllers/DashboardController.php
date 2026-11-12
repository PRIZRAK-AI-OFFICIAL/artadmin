<?php


namespace Artadmin\Controllers;


use App\Http\Controllers\Controller;
use Artadmin\Repository\CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index(){


        return view("artadmin::index");
    }

    public function clear_cache(){
        CacheRepository::clear();

        return back()->with("success",trans("artadmin::notify.cache_clear"));
    }

    public function logout(Request $request){
        Auth::guard(config("artadmin.guard"))->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("artadmin.login");

    }
}
