<?php

namespace App\Commands;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use GuzzleHttp\Client;
class InspiringCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sum2num {num1 : first argument} {num2 : second argument}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = "First FaTak's program";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       // $name = $this->ask('What is your name?');
        $num1 = $this->argument('num1');
       $num2 = $this->argument('num2');
        $this->info('Display this on the screen : '.($num1+$num2));
        //$this->info("aaaa");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
