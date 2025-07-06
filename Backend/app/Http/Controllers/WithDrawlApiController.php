<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WithDrawlApiController extends Controller
{
    public function craeteWithDrawl(Request $request){
        $validator = Validator::make($request->all(),[
            'jumlah' => 'required|numeric',
            'bank' => 'required',
            'nama_pemilik_bank' => 'required',
            'nomor_bank' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all(), 'success' => false]);
        }

        $serverKey = env('XENDIT_SECRET_KEY');
        try{
            $response = Http::withBasicAuth($serverKey, '')->post('https://api.xendit.co/disbursements',[
                'external_id' => 'Ref-' . uniqid(),
                'amount' => $request->jumlah,
                'bank_code' => $request->bank,
                'account_holder_name' => $request->nama_pemilik_bank,
                'account_number' => $request->nomor_bank,
                'description' => "Mengambil uang dari server",
            ]);

            $data = $response->json();
            if($response->successful()){
                return response()->json(['message' => "Penarikan berhasil, mohon tunggu uang anda sedang di Transfer", 'success' => true, 'data' => $data]);
            }else{
                return response()->json(['message' => $data, 'success' => false]);
            }
        }catch(Exception $e){
            return response()->json(['message' => $e->getMessage(), 'success' => false]);
        }
    }

    public function callback(Request $request){
        $tokenFromXendit = $request->header('x-callback-token');

        if($tokenFromXendit !== env('XENDIT_CALLBACK_TOKEN')){
            Log::warning('Unauthorization, Token tidak valid');
            return response()->json(['message' => 'Token tidak valid', 'success' => false]);
        }

        $json = $request->all();
        Log::info("Data Callback Withdrawl", $json);
        
        if($request->status === 'COMPLETED'){
            //update data pembayaran dan cek data ada atau tidak ke database
            $data = [
                'reference_id' => $request->external_id,
                'pemilik_rekening' => $request->account_holder_name,
                'bank' => $request->bank_code,
                'jumlah' => $request->amount,
                'deskripsi' => $request->disbursement_description,
            ];
            return response()->json(['message' => "Withdrawl berhasil di proses", 'success' => true, 'data' => $data]);
        }else{
            return response()->json(['message' => "Pembayaran tidak ditemukan atau status gagal", 'success' => false]);
        }
    }
}
