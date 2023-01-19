<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Segment;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SegmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Client $amoApi;

    private static int $apartPipelineId  = 6165066;
    private static int $investPipelineId = 6161598;

    /**
     * @throws Exception
     */
    public function __construct(public Segment $segment)
    {
        Log::info(__METHOD__, ['segment' => $this->segment->id]);
    }

    public function handle()
    {
        try {
        $this->amoApi = (new Client(
            Account::query()
                ->where('subdomain', 'mmirinvest')
                ->first()
        ))->init();

        $isDouble = false;

        $lead = $this->amoApi
            ->service
            ->leads()
            ->find($this->segment->lead_id);

        if ($lead->contact !== null) {
            $leadsArray = [
                static::$investPipelineId => [
                    'count_active' => 0,
                    'count_lost' => 0,
                    'count_success' => 0,
                    'sale' => 0,
                ],
                static::$apartPipelineId => [
                    'count_active' => 0,
                    'count_lost' => 0,
                    'count_success' => 0,
                    'sale' => 0,
                ],
            ];

            $contact = $lead->contact;

            foreach ($contact->leads->toArray() as $lead) {
                if ($lead['pipeline_id'] !== static::$apartPipelineId && $lead['pipeline_id'] !== static::$investPipelineId) {
                    continue;
                }

                $leadsArray[$lead['pipeline_id']]['count_active'] += $lead['status_id'] != 142 && $lead['status_id'] != 143 ? 1 : 0;
                $leadsArray[$lead['pipeline_id']]['count_lost'] += $lead['status_id'] == 143 ? 1 : 0;
                $leadsArray[$lead['pipeline_id']]['count_success'] += $lead['status_id'] == 142 ? 1 : 0;

                $leadsArray[$lead['pipeline_id']]['sale'] += $lead['status_id'] == 142 ? $lead['sale'] : 0;
            }
        }

        $segment = Segment::query()->firstOrCreate(
            [
                'contact_id' => $contact->id ?? null
            ],
            [
                'contact_id' => $contact->id ?? null,
                'lead_id' => $lead['id']
            ]
        );

        $segment->fill([
            'contact_id' => $contact->id ?? null,
            'count_leads' => !empty($contact) ? count($contact->leads->toArray()) : 1,
            'sale_invest' => $leadsArray[static::$investPipelineId]['sale'],
            'sale_apart' => $leadsArray[static::$apartPipelineId]['sale'],
            'sale' => $leadsArray[static::$investPipelineId]['sale'] + $leadsArray[static::$apartPipelineId]['sale'],
            'count_lost_invest' => $leadsArray[static::$investPipelineId]['count_lost'],
            'count_lost_apart' => $leadsArray[static::$apartPipelineId]['count_lost'],
            'count_leads_invest' => $leadsArray[static::$investPipelineId]['count_success'] + $leadsArray[static::$investPipelineId]['count_lost'] + $leadsArray[static::$investPipelineId]['count_active'],
            'count_leads_apart' => $leadsArray[static::$apartPipelineId]['count_success'] + $leadsArray[static::$apartPipelineId]['count_lost'] + $leadsArray[static::$apartPipelineId]['count_active'],
            'count_active_invest' => $leadsArray[static::$investPipelineId]['count_active'],
            'count_active_apart' => $leadsArray[static::$apartPipelineId]['count_active'],
            'count_success_invest' => $leadsArray[static::$investPipelineId]['count_success'],
            'count_success_apart' => $leadsArray[static::$apartPipelineId]['count_success'],
        ]);
        $segment->save();

//        if (!empty($contact)) {
//
//            $contact->cf('Сумма сделок')->setValue($segment->sale);
//            $contact->cf('Инвестировано проектов')->setValue(static::getCountSuccess($leadsArray, static::$investPipelineId));
//            $contact->cf('Куплено апартаментов')->setValue(static::getCountSuccess($leadsArray, static::$apartPipelineId));
//            $contact->save();
//        }

        $lead = $this->amoApi
            ->service
            ->leads()
            ->find($this->segment->lead_id);

        $contact = $lead->contact;

        $segment = Segment::query()
            ->where('contact_id', $contact->id)
            ->first();

        $phone = $contact->cf('Телефон')->getValue();
        $email = $contact->cf('Email')->getValue();

        if ($phone) {
            $phone = Contacts::clearPhone($phone);

            $contacts = $this->amoApi
                ->service
                ->contacts()
                ->searchByPhone($phone);

            if ($contacts->count() > 1) {
                $isDouble = true;
                $linkDoublePhone = 'https://mmirinvest.amocrm.ru/contacts/list/contacts/?term=' . $phone;
            }
        }

        if ($email) {
            $contacts = $this->amoApi
                ->service
                ->contacts()
                ->searchByEmail($email);

            if ($contacts->count() > 1) {
                $isDouble = true;
                $linkDoubleEmail = 'https://mmirinvest.amocrm.ru/contacts/list/contacts/?term=' . $email;
            }
        }

        if ($isDouble) {
            $textArrayDouble = static::buildTextDouble($isDouble, [
                'phone' => $linkDoublePhone ?? null,
                'email' => $linkDoubleEmail ?? null,
            ]);

            $doubleText = implode("\n", $textArrayDouble);
        } else {
            $doubleText = implode("\n", ['Дублей не найдено']);
        }

        if ($segment) {
            $segment->is_double = $isDouble ?? false;
            $segment->link_double_phone = $linkDoublePhone ?? null;
            $segment->link_double_email = $linkDoubleEmail ?? null;
            $segment->save();

            $arrayText = [
                'Информация по клиенту :',
                '-----------------------',
                'Услуг приобретено на сумму : ' . $segment->sale,
                'Всего сделок : ' . $segment->count_leads,
            ];

            if ($segment->sale_invest > 1) {
                $lead->attachTag('пайщик');
                $lead->save();
            }

            if ($lead->pipeline_id == static::$investPipelineId) {
                $textPipeline = static::buildTextInvest($segment);
            }
            if ($lead->pipeline_id == static::$apartPipelineId) {
                $textPipeline = static::buildTextApart($segment);
            }

            $text = implode("\n", array_merge($arrayText, $textPipeline));

            $note = $lead->createNote(4);
            $note->text = $text;
            $note->element_type = 2;
            $note->element_id = $lead->id;
            $note->save();
        }

        $note = $lead->createNote(4);
        $note->text = $doubleText;
        $note->element_type = 2;
        $note->element_id = $lead->id;
        $note->save();

        } catch (\Throwable $exception) {

            \App\Services\Telegram\Telegram::send('Партнеры компании', $exception->getMessage());
        }
    }

    private static function buildTextDouble(bool|null $isDouble, array $arrayLinks) : array
    {
        if ($isDouble) {

            $arrayText = [
                'Найдены дубли у контакта!',
                '-------------------------',
            ];

            if (!empty($arrayLinks['phone'])) {

                $arrayText = array_merge($arrayText, [
                    'Совпадение по телефону :',
                    $arrayLinks['phone'],
                ]);
            }

            if (!empty($arrayLinks['email'])) {

                $arrayText = array_merge($arrayText, [
                    'Совпадение по почте :',
                    $arrayLinks['email'],
                ]);
            }

            return $arrayText;
        }

        return [];
    }

    private static function buildTextInvest(Segment $segment) : array
    {
        return [
            '',
            'Инвестиции : ',
            '---------------------------',
            'Вложено всего : '.$segment->sale_invest,
            'Количество сделок : '.$segment->count_leads_invest,
            'Сделок в работе : '.$segment->count_active_invest,
            'Сделок реализовано : '.$segment->count_success_invest,
            'Сделок не реализовано : '.$segment->count_lost_invest,
        ];
    }

    private static function buildTextApart(Segment $segment) : array
    {
        return [
            '',
            'Апартаменты : ',
            '---------------------------',
            'Куплено на сумму : '.$segment->sale_apart,
            'Количество сделок : '.$segment->count_leads_apart,
            'Сделок в работе : '.$segment->count_active_apart,
            'Сделок реализовано : '.$segment->count_success_apart,
            'Сделок не реализовано : '.$segment->count_lost_apart,
        ];
    }

    private static function getCountSuccess(array $arrayLeads, $pipelineId): int
    {
        $countSuccess = 0;

        if (!empty($arrayLeads[$pipelineId])) {

            foreach ($arrayLeads[$pipelineId] as $leadArray) {
                try {
                    $countSuccess =+ $leadArray['status_id'] == 142 ? 1 : 0;

                } catch (\Throwable $exception) {}
            }
        }

        return $countSuccess;
    }
}
