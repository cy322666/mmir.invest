<?php

namespace App\Http\Controllers;

use App\Jobs\SegmentJob;
use App\Models\Account;
use App\Models\Segment;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SegmentController extends Controller
{
    private static int $apartPipelineId  = 6165066;
    private static int $investPipelineId = 6161598;

    /**
     * @throws Exception
     */
    public function __construct()
    {

    }

    /**
     * @throws Exception
     */
    public function success(Request $request)
    {

    }

    public function create(Request $request)
    {
        $arrayLead = $request->toArray()['leads']['add'][0];

        if ($arrayLead['pipeline_id'] != static::$investPipelineId &&
            $arrayLead['pipeline_id'] != static::$apartPipelineId) {

            exit;
        }

        $segment = Segment::query()->create([
            'lead_id' => $arrayLead['id'],
        ]);

        SegmentJob::dispatch($segment);
    }

    public function cron()
    {
        Artisan::call('queue:work');
    }
}
