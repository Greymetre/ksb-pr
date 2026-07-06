<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FieldKonnect Login</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

    .logo {
      font-size: 26px;
      font-weight: bold;
      color: #007bff;
    }

    .company-logos {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 20px;
    }

    .company-logos img {
      max-height: 40px;
      margin: 0 10px;
    }

    .form-control {
      border-radius: 5px;
    }

    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      border-radius: 5px;
    }

    .footer {
      font-size: 12px;
      margin-top: 20px;
      color: #888;
    }
    .btn-lg{
      border-radius: 30px;
    }
    .captcha-box {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .captcha-box img {
      border: 1px solid #ced4da;
      border-radius: 5px;
      height: 42px;
    }

    .password-field {
      position: relative;
    }

    .password-field .form-control {
      padding-right: 44px;
    }

    .password-toggle {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      border: 0;
      background: transparent;
      color: #6c757d;
      padding: 0;
      line-height: 1;
      cursor: pointer;
      z-index: 3;
    }

    .password-toggle:focus {
      outline: none;
      color: #007bff;
    }
  </style>
</head>

<body>

  <div class="container-fluid p-0">
    <div class="row h-100 no-gutters">
      <div class="col-md-6 left-side d-none d-md-block">
        <!-- <img src="{{ url('/').'/'.asset('assets/img/login_side.png') }}" alt="" class="side-img" width="100%"> -->
      </div>
      <div class="col-md-6 right-side text-center">
        <div class="logo mb-3"><img src="{{asset('assets/img/brand_logo.png') }}" alt="" width="100%"></div>
        <div class="login-box">
          <div class="german-logo-cont">
            <img src="{{ url('/').'/'.asset('assets/img/duke_logo.png') }}?" alt="Duke Logo" width="120" class="blueneba-logo">
            <!--<img src="{{ url('/').'/'.asset('assets/img/silver.png') }}" alt="" class="german-logo">-->
          </div>
          <p class="mb-3 font-weight-bold text-primary">Please log in to your account</p>
          @php
          $loginValue = old('email', session('login_captcha_login'));
          $showCaptcha = loginCaptchaRequired($loginValue, request()->ip());
          @endphp
          <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group text-left">
              <label for="email">Email address</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ $loginValue }}" placeholder="Enter email">
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group text-left">
              <label for="password">Password</label>
              <div class="password-field">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password">
                <button type="button" class="password-toggle" data-toggle-password="#password" aria-label="Show password" title="Show password">
                  <span class="material-symbols-outlined">visibility</span>
                </button>
              </div>
              @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            @if (Route::has('password.request'))
            <div class="form-group text-right mb-3">
              <a href="{{ route('password.request') }}" class="text-primary" style="font-size: 14px; text-decoration: none;">Forgot Password?</a>
            </div>
            @endif
            @if ($showCaptcha)
            <div class="form-group text-left">
              <label for="captcha">Security Code</label>
              <div class="captcha-box mb-2">
                <span id="captcha-image">{!! loginCaptchaImage() !!}</span>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="refresh-captcha">Refresh</button>
              </div>
              <input type="text" class="form-control @error('captcha') is-invalid @enderror" id="captcha" name="captcha" placeholder="Enter 5-character code">
              @error('captcha')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            @endif
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
            @if(session()->has('error'))
            <div class="alert alert-danger mt-3 mb-0 text-left">
              {{ session()->get('error') }}
            </div>
            @endif
            @if (session('status'))
            <div class="alert alert-success mt-3 mb-0 text-left">
              {{ session('status') }}
            </div>
            @endif
          </form>
        </div>
        <div class="footer text-primary">&copy; {{ date('Y') }} Field Konnect. All rights reserved</div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script>
    $('[data-toggle-password]').on('click', function() {
      let passwordField = $($(this).data('toggle-password'));
      let icon = $(this).find('.material-symbols-outlined');
      let isHidden = passwordField.attr('type') === 'password';

      passwordField.attr('type', isHidden ? 'text' : 'password');
      icon.text(isHidden ? 'visibility_off' : 'visibility');
      $(this).attr('aria-label', isHidden ? 'Hide password' : 'Show password');
      $(this).attr('title', isHidden ? 'Hide password' : 'Show password');
    });

    $("#refresh-captcha").on('click', function() {
      $.get("{{ url('/captcha-refresh') }}", function(data) {
        $("#captcha-image").html(data.captcha);
        $("#captcha").val('');
      });
    });
  </script>

</body>

</html>
