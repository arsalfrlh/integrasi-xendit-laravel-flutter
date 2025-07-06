<?php

namespace App\Http\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
//install library Qr "composer require endroid/qr-code:^4.0"
//jka error "composer require endroid/qr-code:^4.0 -W"
//jika error Unable to generate image: check your GD installation buka file "C:\xampp\php\php.ini"| cari ";extension=gd" hapus tanda ";"

class PaymentApiController extends Controller
{
    public function createBankPayment(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'bank' => 'required',
            'jumlah' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all(), 'success' => false]);
        }

        $serverKey = env('XENDIT_SECRET_KEY');
        try{
            $response = Http::withBasicAuth($serverKey, '')->post('https://api.xendit.co/callback_virtual_accounts',[
                'external_id' => 'Ref-' . uniqid(),
                'bank_code' => $request->bank,
                'name' => $request->name,
                'country' => 'ID',
                'currency' => 'IDR',
                'is_single_use' => true,
                'is_closed' => true,
                'expected_amount' => $request->jumlah,
            ]);

            $data = $response->json();
            if($response->successful()){ //cek apakah request response berhasil
                return response()->json(['message' => "Transaksi berhasil dibuat", 'success' => true, 'method' => "bank", 'data' => $data]);
            }else{
                return response()->json(['message' => $data, 'success' => false]);
            }
        }catch (Exception $e){
            return response()->json(['message' => $e->getMessage(), 'success' => false]);
        }
    }

    public function createCstorePayment(Request $request){
        $validator = Validator::make($request->all(),[
            'jumlah' => 'required|numeric',
            'name' => 'required',
            'store' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all(), 'success' => false]);
        }

        $serverKey = env('XENDIT_SECRET_KEY');
        try{
            $response = Http::withBasicAuth($serverKey, '')->post('https://api.xendit.co/fixed_payment_code',[
                'external_id' => 'Ref-' . uniqid(),
                'retail_outlet_name' => $request->store,
                'name' => $request->name,
                'is_single_use' => true,
                'expected_amount' => $request->jumlah
            ]);

            $data = $response->json();
            if($response->successful()){
                return response()->json(['message' => "Transaksi berhasil dibuat", 'success' => true, 'method' => "store", 'data' => $data]);
            }else{
                return response()->json(['message' => $data, 'success' => false]);
            }
        }catch(Exception $e){
            return response()->json(['message' => $e->getMessage(), 'success' => false]);
        }
    }

    public function createQrPayment(Request $request){
        $validator = Validator::make($request->all(),[
            'jumlah' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all(), 'success' => false]);
        }

        $serverKey = env('XENDIT_SECRET_KEY');
        try{
            $response = Http::withBasicAuth($serverKey, '')->withHeaders(['api-version' => '2022-07-31'])->post('https://api.xendit.co/qr_codes',[
                'reference_id' => 'Ref-' . uniqid(),
                'type' => "DYNAMIC",
                'currency' => "IDR",
                'amount' => $request->jumlah
            ]);

            $json = $response->json();

            if($response->successful()){
                $qrString = $json['qr_string'];
                $result = Builder::create()->writer(new PngWriter())->data($qrString)->size(300)->margin(10)->build(); //membuat qr dengan string dari variable qrString

                $nameQR = $json['reference_id'] . '.png'; //nama qr dari response
                $path = public_path('qrcode/'.$nameQR);

                file_put_contents($path, $result->getString()); //memindahkan file| mengambil hasil gambar dari variabel result
                $data = [
                    'payment' => $json,
                    'qr_image' => $nameQR,
                ];
                return response()->json(['message' => "Transaksi berhasil dibuat", 'success' => true, 'method' => "qr", 'data' => $data]);
            }else{
                return response()->json(['message' => $json, 'success' => false]);
            }
        }catch(Exception $e){
            return response()->json(['message' => $e->getMessage(), 'success' => false]);
        }
    }

    public function callback(Request $request){
        $tokenFromXendit = $request->header('x-callback-token');

        if($tokenFromXendit !== env('XENDIT_CALLBACK_TOKEN')){
            Log::warning("Unauthorization, Token tidak valid");
            return response()->json(['message' => 'Token tidak valid', 'success' => false]);
        }

        $json = $request->all();
        Log::info("Callback PaymentApi", $json);

        $bank = ['BCA', 'BRI', 'BNI', 'CIMB', 'MANDIRI'];
        if(isset($json['data']['status']) && $json['data']['status'] === 'SUCCEEDED'){ //QR Code| isset cek data apakah ada di callback?
            //update data pembayaran dan cek data ada atau tidak ke database
            $data = [
                'reference_id' => $json['data']['reference_id'],
                'name' => $json['data']['payment_detail']['name'],
                'jumlah' => $json['data']['amount'],
            ];
            return response()->json(['message' => "Pembayaran ditemukan", 'success' => true, 'data' => $data]);
        }else if(isset($json['status']) && $json['status'] === 'SETTLING'){ //store payment
            $data = [
                'reference_id' => $json['external_id'],
                'name' => $json['name'],
                'store' => $json['retail_outlet_name'],
                'jumlah' => $json['amount'],
            ];
            return response()->json(['message' => "Pembayaran ditemukan", 'success' => true, 'data' => $data]);
        }else if(isset($json['bank_code']) && in_array($json['bank_code'], $bank)){ //bank payment
            $data = [
                'reference_id' => $json['external_id'],
                'bank' => $json['bank_code'],
                'total' => $json['amount'],
            ];
            return response()->json(['message' => "Pembayaran ditemukan", 'success' => true, 'data' => $data]);
        }else{
            return response()->json(['message' => "Pembayaran tidak ditemukan", 'success' => false]);
        }
    }
}
