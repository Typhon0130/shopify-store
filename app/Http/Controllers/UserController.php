<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class UserController extends Controller
{
    public function index(){
        $user = User::where('id', auth()->user()->id)->first(['id', 'current_plan', 'next_plan', 'balance', 'last_pay_month']);
        return view('auth.change-password', ['user' => $user]);
    }

    public function store(Request $request){
        $user = Auth::user();    
        $userPassword = $user->password;        
        $request->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|same:confirmPassword|min:8',
            'confirmPassword' => 'required',
        ]);
        if (!Hash::check($request->currentPassword, $userPassword)) {
            return back()->withErrors(['currentPassword'=>'Current password is not correct']);
        }
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->newPassword)]);
        return redirect()->back()->with('success','Password successfully updated.');
    }
    
    public function setPlan(Request $request){
        $msg = null;
        $user = User::where('id', auth()->user()->id)->first(['id', 'current_plan', 'next_plan', 'balance', 'last_pay_month', 'shop_name']);
        if(in_array($request->plan, [1, 2, 3])){
            if($user->next_plan == 0){
                // We are selecting plan first time, Needs to be deducted plan fee based on current day
                $fee = \App\Models\Plan::where('id', $request->plan)->first()->month_fee;
                $daysInMonth = days_in_month(date('m'), date('Y'));
                $fee = round(($daysInMonth-date('d')) / $daysInMonth * $fee, 2);
                if($user->balance >= $fee){
                    // update user data with deducted balance
                    User::where('id', auth()->user()->id)->update([
                        'current_plan' => $request->plan, 
                        'next_plan' => $request->plan, 
                        'balance' => $user->balance - $fee,
                        'last_pay_month' => date("Y") * 12 + date("m")
                    ]);
                    // log to merchant_fee table
                    \App\Models\merchant_fee::create([
                        'user_id'    => $user->id,
                        'order_name' => 'Month fee '.date('Y').', '.date('F', mktime(0, 0, 0, date('m'), 10)), 
                        'fee'        => -$fee,
                        'status'     => 1,
                        'paid_FR'    => 1
                    ]);

                    // Send shop status to Shopify public app
                    $url = env('UPDATE_SHOP_STATUS_URL');
                    $postFields = [
                        'shopname' => $user->shop_name,
                        'status' => 'active'
                    ];
                        
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_ENCODING, '');
                    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 100);
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                    $response = curl_exec($curl);

                    curl_close($curl);
                }else{
                    $msg = 'In order to select a plan, please top up your account below for the desired amount.';
                }
            }else{                
                if($request->plan != $user->current_plan){
                    User::where('id', auth()->user()->id)->update(['next_plan' => $request->plan]);
                    $planName = \App\Models\Plan::where('id', $request->plan)->first()->name;
                    $msg = "<strong>$planName</strong> will be activated starting from next month.";
                }
            }
        }
        Session::flash('msg', $msg);
        return redirect()->route('profile');
    }
}
