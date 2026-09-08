<?php

namespace App\Http\Controllers\SMS;

use App\Http\Controllers\Controller;
use App\Models\SMSTemplate;
use App\Services\SMS\SMSService;
use Illuminate\Http\Request;

class SMSController extends Controller
{
    public function __construct(
        protected SMSService $smsService
    ) {
    }

    public function index()
    {
        return view('sms.index');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'mobile'    => ['required'],
            'message'   => ['required','string','max:1000'],
        ]);

        $template = SMSTemplate::where('status', 1)->where('template_name','Feedback')->first();

        if (!$template) {
            return back()
                ->withInput()
                ->with('error', 'SMS template is not configured.');
        }

        $result = $this->smsService->send(
            $validated['mobile'],
            $validated['message'],
            $template->template_id
        );

        if ($result['success']) {
            return redirect()
                ->route('sms.index')
                ->with('success', 'SMS sent successfully.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $result['message']);
    }
}