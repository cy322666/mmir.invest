<?php

namespace App\Http\Controllers\Invest;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SiteController extends Controller
{
    private Client $amoApi;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        try {
            if (\request()->get('test') == 'test') exit;

            Log::info(__METHOD__, \request()->toArray());

        } catch (NotFoundExceptionInterface|ContainerExceptionInterface) {

            Log::error(__METHOD__, \request()->toArray());
        }
        $this->amoApi = (new Client(Account::query()
            ->where('subdomain', 'mmirinvest')
            ->first()
        ))->init();

    }

    public function invest_tilda(Request $request)
    {
        $name  = $request->Name ?? $request->name;
        $phone = $request->Phone ?? $request->phone;

        $statusId = 53290586;

        $model = Site::query()->create([
            'source' => 'investclub.mmir.pro',
            'name' => $name,
            'phone' => $phone,
            'utm_source' => $request->utm_source,
            'utm_term'   => $request->utm_term,
            'utm_medium' => $request->utm_medium,
            'utm_content' => $request->utm_content,
            'utm_campaign' => $request->utm_campaign,
            'body' => json_encode($request->toArray()),
        ]);

        $text = implode("\n", [
            'Новая заявка с сайта mmir.invest!',
            '---------------------------------',
            " - Имя : $name",
            " - Телефон : $phone",
            " - Тип : $request->who",
            " - Опыт инвестирования : $request->experience",
            " - Город : $request->city",
        ]);

        try {
            $contact = Contacts::search(['Телефоны' => [$phone]], $this->amoApi);

            if (!$contact)
                $contact = Contacts::create($this->amoApi, $name);

            $contact = $this->amoApi
                ->service
                ->contacts()
                ->find($contact->id);

            $lead = Leads::create(
                $contact,
                ['status_id' => $statusId,],
                $request->formname,
            );

            sleep(1);

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($lead->id);

            if ($request->who)
                $lead->cf('Тип клиента')->setValue($request->who);

//            $lead->cf('Город')->setValue($request->city);
            $lead->cf('Рекламный источник')->setValue('Сайт INVEST.MMIR');

            $lead->cf('utm_term')->setValue($model->utm_term);
            $lead->cf('utm_source')->setValue($model->utm_source);
            $lead->cf('utm_medium')->setValue($model->utm_medium);
            $lead->cf('utm_content')->setValue($model->utm_content);
            $lead->cf('utm_campaign')->setValue($model->utm_campaign);

            if ($request->tag) {

                $lead->attachTag($request->tag);
            }
            $lead->attachTag('tilda');
            $lead->save();

            Notes::addOne($lead, $text);

            $model->lead_id = $lead->id;
            $model->contact_id = $contact->id;
            $model->save();

        } catch (\Throwable $exception) {

            $model->error = $exception->getMessage().' '.$exception->getFile().' '.$exception->getLine();
            $model->save();

            \App\Services\Telegram::send('Сайт Инвестиции', $model->error);
        }
    }

    public function apart_tilda(Request $request)
    {
        $name  = $request->Name ?? $request->name;
        $phone = $request->Phone ?? $request->phone;

        $statusId = 53274922;

        $model = Site::query()->create([
            'source' => 'investclub.mmir.pro/apartments-promo',
            'name' => $name,
            'phone' => $phone,
            'utm_source' => $request->utm_source,
            'utm_term'   => $request->utm_term,
            'utm_medium' => $request->utm_medium,
            'utm_content' => $request->utm_content,
            'utm_campaign' => $request->utm_campaign,
            'body' => json_encode($request->toArray()),
        ]);

        $text = implode("\n", [
            'Новая заявка с сайта investclub.mmir.pro!',
            '---------------------------------',
            " - Имя : $name",
            " - Телефон : $phone",
        ]);

        try {
            $contact = Contacts::search(['Телефоны' => [$phone]], $this->amoApi);

            if (!$contact)
                $contact = Contacts::create($this->amoApi, $name);

            $contact = $this->amoApi
                ->service
                ->contacts()
                ->find($contact->id);

            sleep(1);

            $contact = Contacts::update($contact, ['Телефоны' => [$phone]],
//            'Ответственный' => ''
            );

            $lead = Leads::create(
                $contact,
                ['status_id' => $statusId,],
                $request->formname,
            );

            sleep(1);

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($lead->id);

            $lead->cf('Рекламный источник')->setValue('Сайт INVESTCLUB.MMIR');

            $lead->cf('utm_term')->setValue($model->utm_term);
            $lead->cf('utm_source')->setValue($model->utm_source);
            $lead->cf('utm_medium')->setValue($model->utm_medium);
            $lead->cf('utm_content')->setValue($model->utm_content);
            $lead->cf('utm_campaign')->setValue($model->utm_campaign);

            if ($request->tag) {

                $lead->attachTag($request->tag);
            }
            $lead->attachTag('tilda');
            $lead->save();

            Notes::addOne($lead, $text);

            $model->lead_id = $lead->id;
            $model->contact_id = $contact->id;
            $model->save();

        } catch (\Throwable $exception) {

            $model->error = $exception->getMessage().' '.$exception->getFile().' '.$exception->getLine();
            $model->save();

            \App\Services\Telegram::send('Сайт Апартамент', $model->error);
        }
    }

    public function webinar_tilda(Request $request)
    {
        $name  = $request->Name ?? $request->name;
        $phone = $request->Phone ?? $request->phone;
        $email = $request->Email ?? $request->email;

        $statusId = 53289854;

        $model = Site::query()->create([
            'source' => 'investclub.mmir.pro/online-conf-2022',
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'utm_source' => $request->utm_source,
            'utm_term'   => $request->utm_term,
            'utm_medium' => $request->utm_medium,
            'utm_content' => $request->utm_content,
            'utm_campaign' => $request->utm_campaign,
            'body' => json_encode($request->toArray()),
        ]);

        $text = implode("\n", [
            'Новая регистрация с сайта investclub.mmir.pro!',
            '---------------------------------',
            " - Имя : $name",
            " - Телефон : $phone",
            " - Почта : $email",
        ]);

        try {
            $contact = Contacts::search([
                'Телефоны' => [$phone],
                'Почта' => $email,
            ], $this->amoApi);

            if (!$contact)

                $contact = Contacts::create($this->amoApi, $name);

            $contact = $this->amoApi
                ->service
                ->contacts()
                ->find($contact->id);

            $contact = Contacts::update($contact, [
                'Телефоны' => [$phone],
                'Почта' => $email,
            ],
//            'Ответственный' => ''
            );

            $lead = Leads::create(
                $contact,
                ['status_id' => $statusId,],
                $request->formname,
            );

            sleep(1);

            $lead = $this->amoApi
                ->service
                ->leads()
                ->find($lead->id);

            $lead->cf('Рекламный источник')->setValue('Сайт INVESTCLUB.MMIR');

            $lead->cf('utm_term')->setValue($model->utm_term);
            $lead->cf('utm_source')->setValue($model->utm_source);
            $lead->cf('utm_medium')->setValue($model->utm_medium);
            $lead->cf('utm_content')->setValue($model->utm_content);
            $lead->cf('utm_campaign')->setValue($model->utm_campaign);

            if ($request->tag) {

                $lead->attachTag($request->tag);
            }
            $lead->attachTags(['tilda','free-12-2022']);
            $lead->save();

            Notes::addOne($lead, $text);

            $model->lead_id = $lead->id;
            $model->contact_id = $contact->id;
            $model->save();

        } catch (\Throwable $exception) {

            $model->error = $exception->getMessage().' '.$exception->getFile().' '.$exception->getLine();
            $model->save();

            \App\Services\Telegram::send('Сайт Вебинары', $model->error);
        }
    }

    //TODO pay form + tags and price
}
