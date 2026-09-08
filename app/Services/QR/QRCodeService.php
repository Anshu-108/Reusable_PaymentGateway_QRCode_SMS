<?php

namespace App\Services\QR;

use App\Models\QRCode;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class QRCodeService
{
    public function generate(array $data): QRCode
    {  
        $qrId = 'QR-' . strtoupper(Str::random(12)); 
        $qrData = '';

        if (!empty($data['name'])) {
            $qrData .= "Name: {$data['name']}\n";
        }
        if (!empty($data['phone'])) {
            $qrData .= "Phone: {$data['phone']}\n";
        }
        if (!empty($data['email'])) {
            $qrData .= "Email: {$data['email']}\n";
        }
        if (!empty($data['website'])) {
            $qrData .= "Website: {$data['website']}\n";
        }
        if (!empty($data['address'])) {
            $qrData .= "Address: {$data['address']}\n";
        } 

        $renderer = new GDLibRenderer(500);
        $writer = new Writer($renderer);
        $qrImage = $writer->writeString($qrData); 
        
        $fileName = $qrId . '.png';
        $filePath = 'qr-codes/' . $fileName;
        Storage::disk('public')->put($filePath, $qrImage); 

        return QRCode::create([
            'qr_id'     => $qrId, 
            'name'      => $data['name'] ?? null, 
            'phone'     => $data['phone'] ?? null, 
            'email'     => $data['email'] ?? null, 
            'website'   => $data['website'] ?? null, 
            'address'   => $data['address'] ?? null, 
            'qr_data'   => $qrData, 
            'qr_image'  => $fileName
        ]);
    }
}