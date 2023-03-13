<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Plan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::user()->company_id > 2){
            // Redirect if balance is
            // if(Auth::user()->balance == 0)
            //     return redirect()->route('app.fees');
            // Allow route only to admins
            if (Auth::user()->current_plan == 0)
                return redirect()->route('profile');
        }        
        return $next($request);    
    }
}
