<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $setting->value ?? 'Website' }} - Login</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            font-family: 'Nunito', sans-serif;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .bg-login-image {
            background: url('https://static.vecteezy.com/system/resources/previews/012/494/550/non_2x/inventory-control-system-concept-professional-manager-and-worker-are-checking-goods-and-stock-supply-inventory-management-with-goods-demand-vector.jpg');
            background-size: 100%;
            background-position: center;
            border-top-left-radius: 1rem;
            border-bottom-left-radius: 1rem;
            background-repeat: no-repeat;
        }

        .btn-user {
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-user:hover {
            background: #fff;
            color: #2575fc;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .form-control-user {
            border-radius: 50px;
            padding: 1rem 1.5rem;
        }

        .text-gray-900 {
            color: #fff !important;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: #ff6b6b;
        }

        .form-group input:focus {
            box-shadow: 0 0 10px rgba(37, 117, 252, 0.4);
            border-color: #2575fc;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="row justify-content-center align-items-center vh-100">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center mb-4">
                                        <h1 class="h4 text-black">Welcome Back to {{ $setting->value ?? 'Website' }}!
                                        </h1>
                                    </div>

                                    <form method="POST" action="{{ route('login') }}" class="user">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <input type="email"
                                                class="form-control form-control-user @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email') }}" required autocomplete="email"
                                                autofocus placeholder="Enter Email Address...">
                                            @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <input type="password"
                                                class="form-control form-control-user @error('password') is-invalid @enderror"
                                                name="password" required autocomplete="current-password"
                                                placeholder="Password">
                                            @error('password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

</body>

</html>
