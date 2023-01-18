<?php

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Models\Account;
use App\Models\Site;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ToolsController extends Controller
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

    public function company(Request $request)
    {
        $leadId = $request->all()['leads']['status'][0]['id'];//TODO

        $lead = $this->amoApi->leads()->find($leadId);

        $contact = $lead->contact;

        $phone = $contact->cf('Телефон')->getValue();

        if ($phone) {

            $companiesSearch = $this->amoApi->contacts()->searchByPhone($phone);

            if ($companiesSearch !== null) {

                $company = $companiesSearch->first();

                $lead->company = $company;
                $lead->save();
//                $lead->attachElement($company->id, $element->id, $count = 1);
            } else {

                $company = $lead->createCompany();
                $company->name = $contact->name;
                $company->cf('Телефон')->setValue($phone);
                $company->save();
            }
        }
    }
}
