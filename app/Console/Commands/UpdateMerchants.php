<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\merchant_fee;
use App\Models\Plan;

class UpdateMerchants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:merchants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge merchants from balance for month fee and update membership period';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Updating merchant data...');

        $year = date("Y");
        $month = date("m");

        $users = User::where('company_id', '>', 2)->get();
        foreach($users as $user){
            // Replace current_plans with next_plans
            if($user->next_plan != $user->current_plan)
                User::where('id', $user->id)->update(['current_plan' => $user->next_plan]);
            // Try charge for month fee
            $monthFee = Plan::where('id', $user->next_plan)->first()->month_fee;
            if($user->balance >= $monthFee){
                User::where('id', $user->id)->update(['balance' => $user->balance - $monthFee, 'last_pay_month' => $year*12+$month]);
                merchant_fee::create(['user_id' => $user->id, 'order_name' => 'Month fee '.$year.', '.date('F', mktime(0, 0, 0, $month, 10)), 'fee' => -$monthFee]);
            }
        }
    }
}
