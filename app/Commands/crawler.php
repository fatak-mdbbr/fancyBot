<?php

namespace App\Commands;

use App\Product;
use App\Product_image;
use Clue\React\Socks\Client as sClient;
use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
use LaravelZero\Framework\Commands\Command;
use React\Socket\Connector;
use Storage;
use unreal4u\TelegramAPI\Telegram\Methods\SendPhoto;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use \React\EventLoop\Factory;
use \unreal4u\TelegramAPI\HttpClientRequestHandler;
use \unreal4u\TelegramAPI\TgLog;

class Crawler extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'crawler';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = "crawler";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $category = '515';
        // Create a client with a base URI
        $client = new Client(['base_uri' => 'https://newapi.banimode.com']);
        $response = $client->request('GET', '/search', [
            'header' => ['Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 OPR/57.0.3098.116',
            ],
            'query' =>
            ['price' => '{"from":0,"to":0}',
                'in_stock' => '1',
                'category' => $category,
                'brand' => '0',
                'pageSize' => '10',
                'from' => 0,
                'sort' => 'discount',
                'order_by' => 'desc',
                'pageFilters' => '{"category":[{"id":"4","custom_name":""},{"id":"3","custom_name":""},{"id":"5","custom_name":""},{"id":"6","custom_name":""},{"id":"455","custom_name":""},{"id":"183","custom_name":""}],"size":[true],"color":[false],"brand":[true]}',
                'parameter' => '',
            ],
        ]
        );
        //$body = $response->getBody();
        //echo $body;
        //var_dump($body);
        $result = json_decode($response->getBody()->getContents());
        //echo $result->links->next;

        // foreach($result->data->list as $item){
        //     echo "product_id : " . $item->product_id . "\r\n";
        //     echo "product_title : " . $item->title . "\r\n";
        //     echo "product_price : " . $item->price . "\r\n";
        //     foreach($item->images as $img){
        //         echo "product_images : " . $img->image_size->large_default;
        //         echo  "\r\n";
        //     }
        // echo "---------------------------------------------------------------"."\r\n";
        // }

        //  $headers = ['Id', 'Title','Price'];
        //  $temp=$result->data->list;
        // $array=array();

        // foreach($result->data->list as $item){

        //  $array[]=[$item->product_id,$item->title,$item->price];
        // }
        // //print_r($array);
        // $this->table($headers,$array);

        //print_r($result);
        //$myfile = fopen("testfile.txt",(string) $body);
        //var_dump($result);
        // //json_decode($response->getBody()->getContents(), true);
        //echo file_put_contents("jsonTest.txt",(string)$result);

        foreach ($result->data->list as $item) {
            try {
                $product = new Product();
                $product->product_id = $item->product_id;
                echo "\r\nproduct id : " . $item->product_id;
                $product->product_title = $item->title;
                echo "\r\ntitle : " . $item->title;
                $product->product_price = $item->price;
                echo "\r\nprice : " . $item->price;
                $product->product_link = $item->link;
                $product->save();
                echo "\r\nsuccess in saving to product table\r\n";
                foreach ($item->images as $img) {
                    $url = $img->image_size->large_default;
                    $path = parse_url($url, PHP_URL_PATH);
                    $file_name = str_random(32) . '.jpg';
                    $image_add = 'Images/category_' . $category . "/product_" . $item->product_id . '/' . $file_name;
                    $product_image = new Product_image(['image_address' => $image_add, 'image_name' => basename($url)]);
                    $product = Product::find($item->product_id);
                    $product->product_images()->save($product_image);

                    // $product_image=new Product_image;
                    // $product_image->product_id=$item->product_id;
                    // $product_image->image_address=$image_add;
                    // $product_image->image_name=basename($url);
                    // $product_image->save();
                   // echo "\r\nsuccess in saving images\r\n";
                    $client = new Client(['base_uri' => 'https://www.banimode.com/']);
                    $response = $client->request('GET', $path);
                    $body = $response->getBody();
                   // Storage::put($image_add, $body);

                }
                echo "finished all successfully\r\n";
                echo "-----------------------------------------------------------------------------------------------";
                if ($item->has_discount == 0) {
                    $discount = "";
                } else {
                    $discount = $item->new_price;
                }

                $this->bot($product, $item->images[0]->image_size->large_default, $discount);
            } catch (QueryException $e) {
                print "\r\nCATCH! \r\n";
                $error_code = $e->errorInfo[1];
                // echo $e->getMessage();
                if ($error_code == 1062) {
                    $product->delete();
                    echo "duplicate entry problem \r\n";
                    echo "********************************************************* \r\n";
                }
            }
        }

        echo "\r\nend of program";

    } //handle
    /**
     * Define the command's schedule.
     *+
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
    public function bot(Product $product, String $img_url, String $discount)
    {
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
        $inlineKeyboard = new Markup([
            'inline_keyboard' => [
                [
                    ['text' => 'مشاهده در بانی‌مد', 'url' => $product->product_link],
                ],
            ],
        ]);
        if ($discount == "") {
            $discountCaption = "";
        } else {
            $discountCaption = "قیمت بعد از تخفیف" . $discount;
        }

        $sendPhoto = new SendPhoto();
        $sendPhoto->chat_id = env('CHAT_ID');
        $sendPhoto->photo = $img_url;
        $sendPhoto->parse_mode = 'Markdown';
        $sendPhoto->reply_markup = $inlineKeyboard;
        $sendPhoto->caption = $product->product_title .
        "\r\nقیمت : " . $product->product_price ."\r\n".
            $discountCaption;
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
}
