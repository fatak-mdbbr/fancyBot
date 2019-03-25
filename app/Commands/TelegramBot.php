<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use React\Socket\Connector;
use \React\EventLoop\Factory;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\TgLog;
use Clue\React\Socks\Client as sClient;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Storage;
use \unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SendPhoto;
use unreal4u\TelegramAPI\Telegram\Types\Custom\InputFile;

class TelegramBot extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'TelegramBot';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Telegram bot';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $token = getenv('BOT_TOKEN');
        // $proxy_port = getenv('PROXY_PORT');
        // $proxy_address = getenv('PROXY_ADDRESS');
        // $loop = Factory::create();
        // $proxy = new Client('socks5://' . $proxy_address . ':' . $proxy_port, new Connector($loop));
        // $handler = new HttpClientRequestHandler($loop, [
        //     'tcp' => $proxy,
        //     'timeout' => 3.0,
        //     'dns' => false,
        // ]);

        // $tgLog = new TgLog($token, $handler);
        // $sendMessage = new SendMessage();
        // $sendMessage->chat_id = "-352246342";
        // $sendMessage->text = 'fatak says thank you';
        // $tgLog->performApiRequest($sendMessage);
        // $loop->run();
        $token = env('BOT_TOKEN');
        $proxy_port = env('PROXY_PORT');
        $proxy_address = env('PROXY_ADDRESS');
        $loop = Factory::create();
        $proxy = new sClient('socks5://' . $proxy_address . ':' . $proxy_port, new Connector($loop));
        $handler = new HttpClientRequestHandler($loop, [
            'tcp' => $proxy,
            'timeout' => 3.0,
            'dns' => false,
        ]);

        $tgLog = new TgLog($token, $handler);
        $sendPhoto = new SendPhoto();
        $sendPhoto->chat_id = "-352246342";
        $sendPhoto->photo = new InputFile('test.jpg');
        $sendPhoto->caption = 'Not sure if sending image or image not arriving';
        $promise = $tgLog->performApiRequest($sendPhoto);
        $promise->then(
            function ($response) {
                // echo '<pre>';
                // var_dump($response);
                // echo '</pre>';
            },
            function (\Exception $exception) {
                // Onoes, an exception occurred...
                echo 'Exception ' . get_class($exception) . ' caught, message: ' . $exception->getMessage();
            }
        );

        $loop->run();
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
