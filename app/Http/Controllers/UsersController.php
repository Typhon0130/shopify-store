<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\User;

class UsersController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'checkRole']);
    }

    public function listUsers(){
        $users = User::where('company_id', '>', 0)->get();
        return view('users', ['users' => $users, 'title' => "Users"]);
    }

    
    
}
