<?php

namespace App\Providers;

use App\Providers\Archive;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateArchive
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Archive  $event
     * @return void
     */
    public function handle(Archive $event)
    {
        //
    }
}
