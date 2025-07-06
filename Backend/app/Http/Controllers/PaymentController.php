<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xendit\Invoice;
use Xendit\Xendit;
//install librarynya "composer require xendit/xendit-php:^2.19"

class PaymentController extends Controller
{
    public function __construct()
    {
        Xendit::setApiKey(env("XENDIT_SECRET_KEY"));
    }

    public function crateInvoice(Request $request){
        $request->validate([
            'amount' => 'required|numeric',
            'email' => 'required|email',
        ]);

        $param = [
            'external_id' => 'Inv-' . uniqid(),
            'amount' => $request->amount,
            'payer_email' => $request->email,
            'description' => "Test payment",
            'success_redirect_url' => 'http://127.0.0.1:8000/status?status=success', //redirect setelah pembayran berhasil
            'failure_redirect_url' => 'http://127.0.0.1:8000/status?status=failed', //redirect setelah pembayran gagal
        ];

        $invoice = Invoice::create($param);
        // dd($invoice);
        return redirect($invoice['invoice_url']); //redirect kehalaman pembayaran xendit
    }

    public function index(){
        return view('main.index');
    }

    public function status(){
        return view('main.status');
    }

    public function callback(Request $request){
        $tokenFromXendit = $request->header('x-callback-token'); //mengambil token header xendit dari request

        if($tokenFromXendit !== env('XENDIT_CALLBACK_TOKEN')){ //cek bahwa pengirim callback adalah xendit
            Log::warning('Unauthorization, Token tidak valid'); //membuat isi log di folder "storage/logs/laravel"
            return response()->json(['message' => "Token tidak valid"]);
        }

        $json = $request->all(); //semua request di simpan ke variabel json
        Log::info('Data Callback', $json);

        if($json['status'] == 'PAID'){
            //update data pembayaran dan cek data ada atau tidak ke database
            $data = [
                'email_pembayar' => $json['payer_email'], //isi request yg disimpan di variable json
                'jumlah' => $json['paid_amount'],
                'metode_pembayaran' => $json['payment_channel'],
            ];
            return response()->json(['message' => "Pembayaran ditemukan", 'success' => true, 'data' => $data]);
        }else{
            return response()->json(['message' => "Pembayaran tidak ditemukan", 'success' => false]);
        }
    }
}
