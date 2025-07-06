<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="card" style="width: 40rem;">
        <div class="card-body">
            @if ($errors->any())
                <div class="pt-3 alert-danger">
                    <ul>
                        @foreach ($errors->all() as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <h1>Pembayaran Xendit</h1>
            <form method="POST" action="/payment">
                @csrf
            <div class="form-group">
                <label for="formGroupExampleInput">Email</label>
                <input type="email" class="form-control" id="formGroupExampleInput" placeholder="Email" name="email">
            </div>
            <div class="form-group">
                <label for="formGroupExampleInput2">Jumlah</label>
                <input type="text" class="form-control" id="formGroupExampleInput2" placeholder="Jumlah" name="amount">
            </div>
            <div class="form-group">
                <input type="submit" value="Bayar" class="btn btn-success">
            </div>
            </form>
        </div>
        </div>
    </div>
</body>
</html>