<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Blacklist;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Block users by id specific routes
        if(Blacklist::where('user_id', Auth::user()->id)->where('route', $request->path())->count())
        {
            return response()->json([
                "status" => "unauthorized"
            ]);            
        }else{
            return $next($request);
        }
               
    }
}
