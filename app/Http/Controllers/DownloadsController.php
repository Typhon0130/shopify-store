<?php

namespace App\Http\Controllers;

use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadsController extends Controller
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
        
        $admin = Auth::user()->administrator;
        $downloads = Download::where(function($query) use ($admin){
            if(!$admin)
                $query->where('company_id', Auth::user()->id);
        })
        ->with(['order.files'])
        ->paginate(30);
        

        //$downloads = Download::with(['order.files'])->paginate(30);

        return view('downloads', [
            'title'     => 'Downloads',
            'class'     => 'downloads',
            'downloads' => $downloads,
            'type'      => null
        ]);
    }
}
