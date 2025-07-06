<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    @php
        $payment = request('status');
        if($payment == 'success'){
            $status = "Berhasil";
        }else{
            $status = "Gagal";
        }
    @endphp
    <h1>Status Pembayaran {{ $status }}</h1>

    @if (request('status') === 'success') <!-- jika request method getnya success "http://127.0.0.1:8000/status?status=success" -->
        <script>
            Swal.fire({
                title: 'Pembayaran berhasil!',
                text: 'Terima kasih telah melakukan pembayaran.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if (request('status') === 'failed') <!-- jika request method getnya failed "http://127.0.0.1:8000/status?status=failed" -->
        <script>
            Swal.fire({
                title: 'Pembayaran gagal!',
                text: 'Pembayaran gagal silahkan coba kembali.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</body>
</html>
