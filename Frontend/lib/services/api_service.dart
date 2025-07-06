import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  final String baseUrl = 'http://10.0.2.2:8000/api';

  Future<Map<String, dynamic>> createPayment(String name, int jumlah, String? bank, String? store, String method)async{
    try{
      if(method == 'bank'){
        final response = await http.post(Uri.parse('$baseUrl/payment/bank'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          "name": name,
          "bank": bank,
          "jumlah": jumlah,
        }));

        if(response.statusCode == 200){
          return json.decode(response.body);
        }else{
          return{
            "success": false,
            "message": json.decode(response.body),
          };
        }

      }else if(method == 'store'){
        final response = await http.post(Uri.parse('$baseUrl/payment/store'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          "name": name,
          "store": store,
          "jumlah": jumlah,
        }));

        if(response.statusCode == 200){
          return json.decode(response.body);
        }else{
          return{
            "success": false,
            "message": json.decode(response.body),
          };
        }

      }else if(method == 'qr'){
        final response = await http.post(Uri.parse('$baseUrl/payment/qr'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          "jumlah": jumlah,
        }));

        if(response.statusCode == 200){
          return json.decode(response.body);
        }else{
          return{
            "success": false,
            "message": json.decode(response.body),
          };
        }

      }else{
        return{
          "success": false,
          "message": "Harap pilih metode pembayaran",
        };
      }
    }catch(e){
      return{
        "success": false,
        "message": e,
      };
    }
  }

  Future<Map<String, dynamic>> createWithdrawl(int jumlah, String bank, String pemilikRekening, String noRekening)async{
    try{
      final response = await http.post(Uri.parse('$baseUrl/withdrawl'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        "jumlah": jumlah,
        "bank": bank,
        "nama_pemilik_bank": pemilikRekening,
        "nomor_bank": noRekening,
      }));
      if(response.statusCode == 200){
        return json.decode(response.body);
      }else{
        return{
          "success": false,
          "message": json.decode(response.body),
        };
      }
    }catch(e){
      return{
        "success": false,
        "message": e,
      };
    }
  }
}
