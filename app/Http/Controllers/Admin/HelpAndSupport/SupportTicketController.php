<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\SupportTicketRequest;
use App\Jobs\SendEmailJob;
use App\Mail\SupportTicketNotifyCustomerMail;
use App\Models\SupportTicket;
use App\Repositories\SupportTicketRepository;
use App\Services\SupportTicketService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends BaseController
{
    /**
     * @param  SupportTicketRepository  $supportTicketRepo
     */
    public function __construct(
        private readonly SupportTicketRepositoryInterface $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface $supportTicketConvRepo,
    ) {}

    /**
     * @return \Illuminate\Contracts\View\View Index function is the starting point of a controller
     *                                         Index function is the starting point of a controller
     */
    public function index(?Request $request, ?string $type = null): View
    {
        $tickets = $this->supportTicketRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request->get('searchValue'),
            filters: ['priority' => $request['priority'], 'status' => $request['status']],
            dataLimit: getWebConfig('pagination_limit')
        );

        return view('admin-views.support-ticket.view', compact('tickets'));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $ticket = $this->supportTicketRepo->getFirstWhere(params: ['id' => $request['id']]);
        $status = $ticket['status'] == 'open' ? 'close' : 'open';
        $this->supportTicketRepo->update(id: $ticket['id'], data: ['status' => $status]);

        return response()->json(['message' => translate('Support_ticket_status_updated')]);
    }

    public function getView($id): View
    {
        $supportTicket = $this->supportTicketRepo->getListWhere(filters: ['id' => $id], relations: ['conversations'], dataLimit: 'all');

        return view('admin-views.support-ticket.singleView', compact('supportTicket'));
    }

    public function reply(SupportTicketRequest $request, SupportTicketService $supportTicketService): RedirectResponse
    {
        if ($request['media'] == null && $request['replay'] == null) {
            ToastMagic::warning(translate('type_something').'!');

            return back();
        }
        if ($request['replay'] && mb_strlen($request['replay']) > 189) {
            ToastMagic::warning(translate('you_cannot_send_more_than_189_characters_text_!'));

            return back();
        }
        $dataArray = $supportTicketService->getAddData(request: $request);
        $this->supportTicketConvRepo->add(data: $dataArray);
        $this->supportTicketRepo->update(id: $request['id'], data: ['status' => 'open']);

        $ticket = SupportTicket::with('customer')->find($request['id']);
        $customerEmail = $ticket?->customer?->email;
        $emailConfig = getWebConfig(name: 'mail_config');
        if (($emailConfig['status'] ?? 0) == 0) {
            $emailConfig = getWebConfig(name: 'mail_config_sendgrid');
        }
        if (($emailConfig['status'] ?? 0) == 1 && $customerEmail) {
            dispatch(new SendEmailJob($customerEmail, new SupportTicketNotifyCustomerMail((int) $request['id'])));
        }

        return back();
    }
}
