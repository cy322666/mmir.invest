<?php

namespace App\Console\Commands;

use Bloodlog\WebinarClient\Exception\WebinarException;
use Bloodlog\WebinarClient\WebinarClient;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Webinar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invest:webinar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws WebinarException
     */
    public function handle()
    {
        $client = new WebinarClient(new Client([
            'headers' => [
                'x-auth-token' => config('webinar.token'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'base_uri' => config('webinar.base_url'),
        ]));

        $webinars = $client
            ->events()
            ->webinarsList(1, 1, ['STOP'], Carbon::now()->subDays(2)->format('Y-m-d H:i:s'));

        foreach ($webinars as $webinar) {
            try {
                $info = $client->events()
                    ->get(
                        '/stats/users', [
//                        .'&from='.Carbon::now()->subDays(360)->format('Y-m-d H:i:s'));
//                        'eventId' => $webinar['id'],
                        'from' => '2019-04-01',
                    ]);
//                ]
//            );

                dd($info);

            } catch (ClientException $exception) {

            }
        }

        return CommandAlias::SUCCESS;
    }
}
