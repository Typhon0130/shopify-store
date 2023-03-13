<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use \Statickidz\GoogleTranslate;
use App\Models\Foreigndata;

class UpdateMugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:mugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update mugs data';

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
        Log::info('Updating mugs...');  

        $foreignData = Foreigndata::all();
        $source = 'pl';
        $target = 'en';
        foreach ($foreignData as $frgData){
            $url = $frgData->url;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
            curl_close($ch);
            $result=json_decode($output);		
            if($result->sizeavailability){
                $trans = new GoogleTranslate();
                $additional_info = $trans->translate($source, $target, $result->sizeavailability->status_description);
                if($result->sizeavailability->status == 'enable'){
                    Foreigndata::where('id', $frgData->id)->update([
                        'quantity' => $result->sizeavailability->sum,
                        'additional_info' => $additional_info
                    ]);
                }else{
                    Foreigndata::where('id', $frgData->id)->update([
                        'quantity' => 0,
                        'additional_info' => $additional_info
                    ]);
                }
            }
        }
    }
}
