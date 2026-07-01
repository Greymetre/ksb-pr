<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password - {{ config('app.name') }}</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <style>
    body,
    html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f4f4;
    }

    .container-fluid {
      height: 100vh;
    }

    .left-side {
      background: url("{{ asset('assets/img/login.png') }}") no-repeat center center;
      background-size: cover;
      height: 100%;
    }

    .right-side {
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }

    .login-box {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      min-width: 400px;
      max-width: 450px;
    }

    .form-control {
      border-radius: 5px;
    }

    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      border-radius: 5px;
    }

    .btn-lg {
      border-radius: 30px;
    }

    .description {
      color: #666;
      font-size: 14px;
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .footer {
      font-size: 12px;
      margin-top: 20px;
      color: #888;
    }
  </style>
</head>

<body>
  <div class="container-fluid p-0">
    <div class="row h-100 no-gutters">
      <div class="col-md-6 left-side d-none d-md-block"></div>
      <div class="col-md-6 right-side text-center">
        <div class="logo mb-3"><img src="{{ asset('assets/img/brand_logo.png') }}" alt="" width="100%"></div>
        <div class="login-box">
          <div class="german-logo-cont">
            <img src="{{ asset('assets/img/ksb-logo-data.svg') }}?" alt="ksb" width="160" class="blueneba-logo">
          </div>

          <p class="mb-3 font-weight-bold text-primary">Forgot Password?</p>
          <p class="description">Enter your email address and we will send password reset instructions if the account exists.</p>

          <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group text-left">
              <label for="email">Email address</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email address" value="{{ old('email') }}" required autofocus>
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Send Password Reset Link</button>

            @if (session('status'))
            <div class="alert alert-success mt-3 mb-0 text-left">
              {{ session('status') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger mt-3 mb-0 text-left">
              {{ session('error') }}
            </div>
            @endif
          </form>

          <div class="mt-3">
            <a href="{{ route('login') }}" class="text-muted" style="font-size: 14px;">Back to Login</a>
          </div>
        </div>
        <div class="footer text-primary">&copy; {{ date('Y') }} Field Konnect. All rights reserved</div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script>
    $(function() {
      $('#email').trigger('focus');
    });
  </script>
</body>

</html>
