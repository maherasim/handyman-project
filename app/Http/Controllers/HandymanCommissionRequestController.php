<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HandymanCommissionRequest;
use App\Models\HelpDesk;
use App\Models\HelpDeskActivityMapping;
use App\Models\User;
use App\Traits\NotificationTrait;

class HandymanCommissionRequestController extends Controller
{
    use NotificationTrait;

    /**
     * Provider submits a commission change request.
     * Auto-creates a helpdesk thread so all three parties can communicate.
     */
    public function store(Request $request)
    {
        $request->validate([
            'handyman_id'           => 'required|exists:users,id',
            'requested_commission'  => 'required|numeric|min:1|max:99',
        ]);

        $provider = auth()->user();
        $handyman = User::findOrFail($request->handyman_id);

        // Only the provider who owns this handyman can request
        if ($handyman->provider_id !== $provider->id) {
            return redirect()->back()->withErrors(__('messages.not_authorized'));
        }

        // Block if a pending request already exists
        $existing = HandymanCommissionRequest::where('handyman_id', $handyman->id)
            ->where('provider_id', $provider->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(__('messages.commission_request_already_pending'));
        }

        // Create helpdesk thread for 3-way communication
        $reason  = $request->input('reason', '');
        $subject = __('messages.commission_request_helpdesk_subject', [
            'handyman' => $handyman->display_name,
            'current'  => $handyman->handyman_commission,
            'new'      => $request->requested_commission,
        ]);
        $description = __('messages.commission_request_helpdesk_body', [
            'provider'  => $provider->display_name,
            'handyman'  => $handyman->display_name,
            'current'   => $handyman->handyman_commission,
            'new'       => $request->requested_commission,
            'reason'    => $reason ?: '—',
        ]);

        $helpdesk = HelpDesk::create([
            'subject'     => $subject,
            'employee_id' => $provider->id,
            'email'       => $provider->email,
            'description' => $description,
            'status'      => '0',
        ]);

        // Opening message in the helpdesk activity
        HelpDeskActivityMapping::create([
            'helpdesk_id'   => $helpdesk->id,
            'sender_id'     => $provider->id,
            'receiver_id'   => admin_id(),
            'messages'      => $description,
            'activity_type' => 'add_helpdesk',
        ]);

        // Create the commission request record
        $commissionRequest = HandymanCommissionRequest::create([
            'handyman_id'           => $handyman->id,
            'provider_id'           => $provider->id,
            'current_commission'    => $handyman->handyman_commission,
            'requested_commission'  => $request->requested_commission,
            'status'                => 'pending',
            'helpdesk_id'           => $helpdesk->id,
            'provider_agreed'       => true,
        ]);

        // Notify admin
        try {
            $activity_data = [
                'activity_type' => 'add_helpdesk',
                'helpdesk_id'   => $helpdesk->id,
                'sender_id'     => $provider->id,
                'receiver_id'   => admin_id(),
                'helpdesk'      => $helpdesk,
            ];
            $this->sendNotification($activity_data);
        } catch (\Throwable $th) {
            \Log::warning('Commission request notification failed: ' . $th->getMessage());
        }

        return redirect()->back()->withSuccess(__('messages.commission_request_sent'));
    }

    /**
     * Admin: list all pending commission requests.
     */
    public function index()
    {
        $auth_user = authSession();
        if (!$auth_user->hasAnyRole(['admin', 'demo_admin'])) {
            return redirect()->back()->withErrors(__('messages.not_authorized'));
        }

        $pageTitle = __('messages.commission_requests');
        $requests  = HandymanCommissionRequest::with(['handyman', 'provider', 'helpdesk'])
            ->orderByRaw("FIELD(status,'pending','approved','rejected')")
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('commission_request.index', compact('pageTitle', 'requests', 'auth_user'));
    }

    /**
     * Admin: approve a request — provider can now edit commission.
     */
    public function approve($id)
    {
        $auth_user = authSession();
        if (!$auth_user->hasAnyRole(['admin', 'demo_admin'])) {
            return redirect()->back()->withErrors(__('messages.not_authorized'));
        }

        $commissionRequest = HandymanCommissionRequest::findOrFail($id);

        if (!$commissionRequest->isPending()) {
            return redirect()->back()->withErrors(__('messages.commission_request_already_actioned'));
        }

        $commissionRequest->update([
            'status'       => 'approved',
            'admin_notes'  => request('admin_notes'),
        ]);

        // Add admin reply to the helpdesk thread
        if ($commissionRequest->helpdesk_id) {
            HelpDeskActivityMapping::create([
                'helpdesk_id'   => $commissionRequest->helpdesk_id,
                'sender_id'     => $auth_user->id,
                'receiver_id'   => $commissionRequest->provider_id,
                'messages'      => __('messages.commission_request_approved_message', [
                    'new' => $commissionRequest->requested_commission,
                ]),
                'activity_type' => 'reply_helpdesk',
            ]);
        }

        return redirect()->route('commission-request.index')
            ->withSuccess(__('messages.commission_request_approved'));
    }

    /**
     * Admin: reject a request.
     */
    public function reject($id)
    {
        $auth_user = authSession();
        if (!$auth_user->hasAnyRole(['admin', 'demo_admin'])) {
            return redirect()->back()->withErrors(__('messages.not_authorized'));
        }

        $commissionRequest = HandymanCommissionRequest::findOrFail($id);

        if (!$commissionRequest->isPending()) {
            return redirect()->back()->withErrors(__('messages.commission_request_already_actioned'));
        }

        $commissionRequest->update([
            'status'      => 'rejected',
            'admin_notes' => request('admin_notes'),
        ]);

        if ($commissionRequest->helpdesk_id) {
            HelpDeskActivityMapping::create([
                'helpdesk_id'   => $commissionRequest->helpdesk_id,
                'sender_id'     => $auth_user->id,
                'receiver_id'   => $commissionRequest->provider_id,
                'messages'      => __('messages.commission_request_rejected_message', [
                    'reason' => request('admin_notes', '—'),
                ]),
                'activity_type' => 'reply_helpdesk',
            ]);
        }

        return redirect()->route('commission-request.index')
            ->withSuccess(__('messages.commission_request_rejected'));
    }

    /**
     * Provider: edit commission form (only when request is approved).
     */
    public function edit($id)
    {
        $commissionRequest = HandymanCommissionRequest::findOrFail($id);
        $provider          = auth()->user();

        if ($commissionRequest->provider_id !== $provider->id || !$commissionRequest->isApproved()) {
            return redirect()->back()->withErrors(__('messages.not_authorized'));
        }

        $handyman  = $commissionRequest->handyman;
        $pageTitle = __('messages.edit_commission');
        $auth_user = authSession();

        return view('commission_request.edit', compact('commissionRequest', 'handyman', 'pageTitle', 'auth_user'));
    }

    /**
     * Provider: save the approved commission change.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'handyman_commission' => 'required|numeric|min:1|max:99',
        ]);

        $commissionRequest = HandymanCommissionRequest::findOrFail($id);
        $provider          = auth()->user();

        if ($commissionRequest->provider_id !== $provider->id || !$commissionRequest->isApproved()) {
            return redirect()->back()->withErrors(__('messages.not_authorized'));
        }

        $handyman = $commissionRequest->handyman;
        $handyman->update(['handyman_commission' => $request->handyman_commission]);

        // Mark request as completed by deleting it (soft-delete keeps history)
        $commissionRequest->delete();

        return redirect()->route('handyman.detail', $handyman->id)
            ->withSuccess(__('messages.commission_updated_successfully'));
    }
}
