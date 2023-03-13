<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function registerUser(Request $request) {
        if (!$request->header('user'))
            abort (403, 'Where is the username?');

        if ($request->header('user') != 'myvalentine')
            abort (403, 'Forgot your username?');

        if (!$request->header('pass'))
            abort (403, 'Where is the password?');

        if ($request->header('pass') != '$2y$12$FAzIRc0F1zWAsrsCt3c2Sexs2x7Hd6bpag6su5swjKtysteM5gtOu')
            abort (403, 'Forgot your password?');

        \Illuminate\Support\Facades\Log::info('New installation: ' . file_get_contents("php://input"));

        $data = json_decode(file_get_contents("php://input"));
        
        if (!$data->email) {
            return response()->json(['status' => 'error'], 400);
        }

        return User::create([
            'name' => $data->ownerName,
            'email' => $data->email,
            'password' => Hash::make(env('NEW_USER_PASSWORD')),
            'company_name' => $data->company,
            'shop_name' => $data->shop,
            'active' => 1
        ]);
    }

    public function removeUser(Request $request) {
        if (!$request->header('user'))
            abort (403, 'Where is the username?');

        if ($request->header('user') != 'myvalentine')
            abort (403, 'Forgot your username?');

        if (!$request->header('pass'))
            abort (403, 'Where is the password?');

        if ($request->header('pass') != '$2y$12$FAzIRc0F1zWAsrsCt3c2Sexs2x7Hd6bpag6su5swjKtysteM5gtOu')
            abort (403, 'Forgot your password?');

        // \Illuminate\Support\Facades\Log::info('Uninstallation: ' . file_get_contents("php://input"));
            
        $data = json_decode(file_get_contents("php://input"));

        if (!$data->shop) {
            return response()->json(['status' => 'error'], 400);
        }

        return User::where('shop_name', $data->shop)->delete();
    }
}