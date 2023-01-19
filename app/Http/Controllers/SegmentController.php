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
        $this->amoApi = (new Client(Account::query()
            ->where('subdomain', 'mmirinvest')
            ->first()
        ))->init();
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

//        $lead = $this->amoApi
//            ->service
//            ->leads()
//            ->find($_POST['leads']['status'][0]['id']);
//
//        if ($lead->contact !== null) {
//
//            $contact = $lead->contact;
//
//            $leadsArray = [];
//
//            foreach ($contact->leads->toArray() as $lead) {
//
//                $countActive = $lead->status_id != 142 && $lead->status_id != 143 ? 1 : 0;
//
//                $sale =+ $lead['sale'];
//
//                $leadsArray[$lead['pipeline_id']] = $lead;
//                $leadsArray[$lead['pipeline_id']]['sale'] =+ $lead->status_id == 142 ? $lead->sale : 0;
//                $leadsArray[$lead['pipeline_id']]['count_active'] =+ $countActive;
//            }
//        }
//
//        if (!empty($contact)) {
//
//            $phone = $contact->cf('Телефон')->getValue();
//            $email = $contact->cf('Email')->getValue();
//
//            if ($phone) {
//
//                $contacts = $this->amoApi
//                    ->service
//                    ->contacts()
//                    ->searchByPhone($phone);
//
//                if ($contacts->count() > 1) {
//
//                    $isDouble = true;
//                    $linkDoublePhone = 'https://mmirinvest.amocrm.ru/contacts/list/contacts/?term='.$phone;
//                }
//            }
//
//            if ($email) {
//
//                $contacts = $this->amoApi
//                    ->service
//                    ->contacts()
//                    ->searchByEmail($email);
//
//                if ($contacts->count() > 1) {
//
//                    $isDouble = true;
//                    $linkDoubleEmail = 'https://mmirinvest.amocrm.ru/contacts/list/contacts/?term='.$email;
//                }
//            }
//        }
//
//        Segment::query()->updateOrCreate([
//            'contact_id' => $contact->id ?? null
//        ], [
//            'lead_id'    => $lead->id,
//            'contact_id' => $contact->id ?? null,
//            'sale' => $sale ?? 0,
//            'sale_invest' => !empty($leadsArray[static::$investPipelineId]) ? $leadsArray[static::$investPipelineId]['sale'] : 0,
//            'sale_apart'  => !empty($leadsArray[static::$apartPipelineId]) ? $leadsArray[static::$apartPipelineId]['sale'] : 0,
//            'count_leads' => !empty($contact) ? count($contact->leads->toArray()) : 1,
//            'is_double'   => $isDouble ?? false,
//            'link_double_phone'  => $linkDoublePhone ?? null,
//            'link_double_email'  => $linkDoubleEmail ?? null,
//            'count_leads_invest' => !empty($leadsArray[static::$investPipelineId]) ? count($leadsArray[static::$investPipelineId]) : 0,
//            'count_leads_apart'  => !empty($leadsArray[static::$apartPipelineId]) ? count($leadsArray[static::$apartPipelineId]) : 0,
//        ]);
//
//        if (!empty($contact)) {
//
//            $contact->cf('Сумма сделок')->setValue($sale);
//            $contact->cf('Инвестировано проектов')->setValue(function () use ($leadsArray) {
//
//                    $countSuccess = 0;
//
//                    foreach ($leadsArray[static::$investPipelineId] as $leadArray) {
//
//                        $countSuccess =+ $leadArray['status_id'] == 142 ? 1 : 0;
//                    }
//
//                    return $countSuccess;
//                });
//            $contact->cf('Куплено апартаментов')->setValue(function () use ($leadsArray) {
//
//                    $countSuccess = 0;
//
//                    foreach ($leadsArray[static::$apartPipelineId] as $leadArray) {
//
//                        $countSuccess =+ $leadArray['status_id'] == 142 ? 1 : 0;
//                    }
//
//                    return $countSuccess;
//                });
//            }
//            $contact->save();
    }
}
