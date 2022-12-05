<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use Exception;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    private Client $amoApi;

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

    public function invest_tilda(Request $request)
    {
        $name  = $request->Name;
        $phone = $request->Phone;

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
            '---------------------------------',
        ]);

        $contact = Contacts::search(['Телефоны' => [$phone]], $this->amoApi);

        if (!$contact)
            $contact = Contacts::create($this->amoApi, $name);

        $contact = Contacts::update($contact, ['Телефоны' => [$phone]],
//            'Ответственный' => ''
        );

        $lead = Leads::create(
            $contact,
            ['status_id' => $statusId,],
            'Новый лид с mmir.invest'
        );

        $lead->cf('Тип клиента')->setValue($request->who);
        $lead->cf('Город')->setValue($request->city);
        $lead->cf('Рекламный источник')->setValue('Сайт INVEST.MMIR');

        $lead->cf('utm_term')->setValue($model->utm_term);
        $lead->cf('utm_source')->setValue($model->utm_source);
        $lead->cf('utm_medium')->setValue($model->utm_medium);
        $lead->cf('utm_content')->setValue($model->utm_content);
        $lead->cf('utm_campaign')->setValue($model->utm_campaign);

        $lead->attachTag('tilda');
        $lead->save();

        Notes::addOne($lead, $text);

        $model->lead_id = $lead->id;
        $model->contact_id = $contact->id;
        $model->save();
    }
}
