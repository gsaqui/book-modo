<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

use Httpful\Mime;
use Httpful\Request as HRequest;


class BookModo extends Command
{
    private $sessionValue = 'You-need-to-set-this';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modo:book';

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
        $this->error($this->sessionValue);
//        $ids = collect([  4091241,   4091244, 4091246,4091249,4091251,  4091254, 4091256, 4091260, 4091262, 4091265, 4091267, 4091271]);
//        $ids->each(fn($it)=>$this->cancelBooking($it));
//        dd('done');

        $date = Carbon::createFromDate(2021, 9, 9);
        $morningBooked = 0;
        $afternoonBooked = 0;
//        while($date->lessThan(Carbon::createFromDate(2021, 12, 22))) {
        while($date->lessThan(Carbon::createFromDate(2021, 12, 22))) {
            $this->info($date->dayName.'  -  '.$date->toFormattedDateString());

            if (in_array($date->dayName, ['Tuesday', 'Thursday', 'Friday'], TRUE)) {
                $response = $this->bookMorning($date);
                $morningBooked++;
                if ($response->code > 400) {
                    dd('failure', $response);
                }
            }

            if (in_array($date->dayName, ['Wednesday', 'Friday'], TRUE)) {
                $response = $this->bookNight($date);
                $afternoonBooked++;
                if ($response->code > 400) {
                    dd('failure', $response);
                }
            }
            $date = $date->addDay();
            $this->info($date->dayName);
        }
        $this->info("Mornings booked = $morningBooked");
        $this->info("Afternoon booked = $afternoonBooked");

        return 0;
    }

    /**
     * @param Carbon $date
     * @return \Httpful\Response
     * @throws \Httpful\Exception\ConnectionErrorException
     */
    private function bookMorning(Carbon $date): \Httpful\Response
    {
        $data = [
            "book_car_id"           => "993",
            "book_location_id"      => "581",
            "is_open_return"        => "0",
            "return_type_selection" => "0",
            "pickup_date"           => $date->toFormattedDateString(),
            "pickup_time"           => "8:30",
            "return_date"           => $date->toFormattedDateString(),
            "return_time"           => "9:15",
            "display"               => "favorites",
            "search_location"       => "64292",
        ];

        $response = HRequest::post("https://bookit.modo.coop/booking", $data)
                            ->sendsType(Mime::FORM)
                            ->addHeader('Cookie', "PHPSESSID=$this->sessionValue; mobile=0")
                            ->followRedirects(FALSE)
                            ->send();
        return $response;
    }

    private function bookNight(Carbon $date): \Httpful\Response
    {
        $data = [
            "book_car_id"           => "993",
            "book_location_id"      => "581",
            "is_open_return"        => "0",
            "return_type_selection" => "0",
            "pickup_date"           => $date->toFormattedDateString(),
            "pickup_time"           => "17:00",
            "return_date"           => $date->toFormattedDateString(),
            "return_time"           => "17:30",
            "display"               => "favorites",
            "search_location"       => "64292",
        ];

        $response = HRequest::post("https://bookit.modo.coop/booking", $data)
                            ->sendsType(Mime::FORM)
                            ->addHeader('Cookie', "PHPSESSID=$this->sessionValue; mobile=0")
                            ->followRedirects(FALSE)
                            ->send();
        return $response;
    }

    private function cancelBooking($id){
        $data = [
            "booking_id"           => "$id",
            "cancel_booking"=>"Cancel+Booking"
            ];

        $response = HRequest::post("https://bookit.modo.coop/booking/edit", $data)
                            ->sendsType(Mime::FORM)
                            ->addHeader('Cookie', "PHPSESSID=$this->sessionValue; mobile=0")
                            ->followRedirects(FALSE)
                            ->send();
        return $response;
    }
}
