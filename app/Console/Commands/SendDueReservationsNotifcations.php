<?php

namespace App\Console\Commands;

use App\Models\Office;
use App\Models\Reservation;
use App\Notifications\HostReservationStarting;
use App\Notifications\UserReservationStarting;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification as NotificationsNotification;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Notification;

class SendDueReservationsNotifcations extends Command
{


    //Gul here we have done the notification part here
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ergodnc:send-reservations-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //Gul here we are sending notifications to the host and user on the day on 
        //which reservation starts

        Reservation::query()
                    ->with('office.user')        
                    ->where('status',Reservation::STATUS_ACTIVE)
                    ->where('start_date' ,now()->toDateString())
                    ->each(function($reservation){
                          
                        FacadesNotification::send($reservation->user ,UserReservationStarting($reservation));
                        FacadesNotification::send($reservation->office->user ,HostReservationStarting($reservation));


                      });

                 return 0;     
    }
}
