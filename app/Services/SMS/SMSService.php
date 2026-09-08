<?php

namespace App\Services\SMS;

use App\Models\SMSLog;
use App\Models\SMSTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SMSService
{

    //For Single User
    public function send(string $mobile, string $message, int $templateId): array
    {
        try {

            $url            = config('sms.vinbox.url');
            $apiKey         = config('sms.vinbox.api_key');
            $senderId       = config('sms.vinbox.sender_id');
            // $templateId     = config('sms.vinbox.template_id');
            // $entityId       = config('sms.vinbox.entity_id');

            $template = SMSTemplate::where('template_id', $templateId)->where('status', 1)->first();
            
            if (!$template || $template === null) {
                return [
                    'success' => false,
                    'message' => 'SMS template not found or inactive.',
                ];
            }

            $payload = [
                'key'           => $apiKey,
                'from'          => $senderId,
                'to'            => [
                                        $mobile,
                                    ],
                'body'          => $message,
                'templateid'    => $template->template_id,
                'entityid'      => $template->entity_id,
            ];

            $response = Http::timeout(config('sms.vinbox.timeout', 30))
            ->withoutVerifying()
            ->acceptJson()
            ->asJson()
            ->post(
                $url,
                $payload
            );

            $responseData = json_decode($response->body(), true);
            //dd($responseData[1]['messageid']);

            Log::info('Vinbox SMS Response', [
                'status'    => $response->status(),
                'response'  => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('Vinbox SMS API failed', [
                    'status'    => $response->status(),
                    'response'  => $response->body(),
                ]);

                return [
                    'success'   => false,
                    'message'   => 'SMS gateway request failed.',
                    'status'    => $response->status(),
                    'response'  => $response->body(),
                ];
            }

            $log_data = SMSLog::create([
                'sms_template_id' => $template->id,
                'phone_number' => $mobile,
                'message' => $message,
                'template_id' => $template->template_id,
                'header' => $senderId,
                'entity_id' => $template->entity_id,
                'provider' => 'Vinbox',
                'status' => $response->status() == 200 ? 'sent' : 'failed',
                'gateway_message_id' => $responseData[1]['messageid'],
                'gateway_response' => $response->body(),
                'error_message' => $response->failed(),
                'requested_at' => now(),
                'sent_at' => now(),
            ]);

            return [
                'success'   => true,
                'message'   => 'SMS sent successfully.',
                'status'    => $response->status(),
                'response'  => $response->json(),
            ];

        } catch (Throwable $e) {

            Log::error('Vinbox SMS Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success'   => false,
                'message'   => 'Unable to send SMS.',
                'error'     => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS to multiple users at once
     */
    public function sendMultiple(array $mobiles, string $message, int $templateId ): array {
        try {
            $url            = config('sms.vinbox.url');
            $apiKey         = config('sms.vinbox.api_key');
            $senderId       = config('sms.vinbox.sender_id');

            $template = SMSTemplate::where('template_id', $templateId)->where('status', 1)->first();
            if (!$template) {
                return [
                    'success' => false,
                    'message' => 'SMS template not found or inactive.',
                ];
            }

            //Remove duplicate mobile numbers            
            $mobiles = array_values(array_unique($mobiles));

            $validMobiles = [];
            $invalidMobiles = [];
            foreach ($mobiles as $mobile) {
                $mobile = trim($mobile);
                if (preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
                    $validMobiles[] = $mobile;
                } else {
                    $invalidMobiles[] = $mobile;
                }
            }

            if (empty($validMobiles)) {
                return [
                    'success' => false,
                    'message' => 'No valid mobile numbers found.',
                    'invalid_mobiles' => $invalidMobiles,
                ];
            }

            // Vinbox Payload
            $payload = [
                'key'           => $apiKey,
                'from'          => $senderId,
                'to'            => $validMobiles,
                'body'          => $message,
                'templateid'    => $template->template_id,
                'entityid'      => $template->entity_id,
            ];

            $response = Http::timeout(config('sms.vinbox.timeout', 30))
            ->withoutVerifying()
            ->acceptJson()
            ->asJson()
            ->post(
                $url,
                $payload
            );

            $responseData = $response->json();

            //Log Gateway Response
            Log::info('Vinbox Multiple SMS Response', [
                'status'            => $response->status(),
                'response'          => $responseData,
                'recipient_count'               => count($validMobiles),
            ]);

            //HTTP Error
            if ($response->failed()) {

                Log::error('Vinbox Multiple SMS API failed', [
                    'status'    => $response->status(),
                    'response'  => $response->body(),
                ]);

                return [
                    'success'   => false,
                    'message'   => 'SMS gateway request failed.',
                    'status'    => $response->status(),
                    'response'  => $responseData,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Vinbox Application Status
            |--------------------------------------------------------------------------
            */
            $gatewayStatus = $responseData['status'] ?? null;
            $gatewayDescription = $responseData['description'] ?? null;

            /*
            |--------------------------------------------------------------------------
            | Gateway Failure
            |--------------------------------------------------------------------------
            */
            if ((string) $gatewayStatus !== '200') {

                Log::error('Vinbox Multiple SMS Gateway failed', [
                    'gateway_status'    => $gatewayStatus,
                    'description'       => $gatewayDescription,
                    'response'          => $responseData,
                ]);

                return [
                    'success'   => false,
                    'message'   => $gatewayDescription ?? 'SMS gateway rejected the request.',
                    'status'    => $gatewayStatus,
                    'response'  => $responseData,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Successful SMS
            |--------------------------------------------------------------------------
            |
            | Insert one log for each successful recipient.
            |
            */
            foreach ($validMobiles as $mobile) {

                SmsLog::create([
                    'sms_template_id' => $template->id,
                    'mobile' => $mobile,
                    'message' => $message,
                    'sender_id' => config('sms.vinbox.sender_id'),
                    'gateway_status' => $gatewayStatus,
                    'gateway_description' => $gatewayDescription,
                    'gateway_response' => $responseData,
                    'sent_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'SMS sent successfully.',
                'status' => $gatewayStatus,
                'response' => $responseData,
                'total' => count($validMobiles),
                'invalid' => $invalidMobiles,
            ];

        } catch (Throwable $e) {

            Log::error('Vinbox Multiple SMS Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to send SMS.',
                'error' => $e->getMessage(),
            ];
        }
    }
}