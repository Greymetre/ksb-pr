<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password - {{ config('app.name') }}</title>

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
      min-height: 100vh;
    }

    .left-side {
      background: url("{{ asset('assets/img/login.png') }}") no-repeat center center;
      background-size: cover;
      min-height: 100vh;
    }

    .right-side {
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      min-height: 100vh;
      padding: 24px 0;
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

    .password-help {
      font-size: 12px;
      color: #666;
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

          <p class="mb-3 font-weight-bold text-primary">Reset Password</p>
          <p class="description">Enter your email address and choose a new password for your account.</p>

          <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group text-left">
              <label for="email">Email address</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email address" value="{{ old('email', $request->email) }}" required autofocus>
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group text-left">
              <label for="password">New Password</label>
              <div class="password-field">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter new password" minlength="12" required>
                <button type="button" class="password-toggle" data-toggle-password="#password" aria-label="Show password" title="Show password">
                  <span class="material-symbols-outlined">visibility</span>
                </button>
              </div>
              @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="password-help mt-2">
                Minimum 12 characters with uppercase, lowercase, number, and special character.
              </div>
              <div id="password-strength" class="mt-2 password-help"></div>
            </div>

            <div class="form-group text-left">
              <label for="password_confirmation">Confirm New Password</label>
              <div class="password-field">
                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" minlength="12" required>
                <button type="button" class="password-toggle" data-toggle-password="#password_confirmation" aria-label="Show password confirmation" title="Show password">
                  <span class="material-symbols-outlined">visibility</span>
                </button>
              </div>
              @error('password_confirmation')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
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
      @if(empty(old('email', $request->email)))
        $('#email').trigger('focus');
      @else
        $('#password').trigger('focus');
      @endif

      function validatePassword(password) {
        var missing = [];
        if (password.length < 12) missing.push('12 characters');
        if (!/[A-Z]/.test(password)) missing.push('uppercase letter');
        if (!/[a-z]/.test(password)) missing.push('lowercase letter');
        if (!/[0-9]/.test(password)) missing.push('number');
        if (!/[^A-Za-z0-9]/.test(password)) missing.push('special character');
        return missing;
      }

      $('#password').on('keyup', function() {
        var password = $(this).val();
        var missing = validatePassword(password);

        if (password.length === 0) {
          $('#password-strength').html('');
          $(this).removeClass('is-valid is-invalid');
          return;
        }

        if (missing.length === 0) {
          $(this).removeClass('is-invalid').addClass('is-valid');
          $('#password-strength').html('<span class="text-success">Password meets all requirements</span>');
        } else {
          $(this).removeClass('is-valid').addClass('is-invalid');
          $('#password-strength').html('<span class="text-danger">Missing: ' + missing.join(', ') + '</span>');
        }
      });

      $('#password_confirmation').on('keyup', function() {
        if ($(this).val().length === 0) {
          $(this).removeClass('is-valid is-invalid');
          return;
        }

        $(this).toggleClass('is-valid', $(this).val() === $('#password').val());
        $(this).toggleClass('is-invalid', $(this).val() !== $('#password').val());
      });

      $('[data-toggle-password]').on('click', function() {
        var passwordField = $($(this).data('toggle-password'));
        var icon = $(this).find('.material-symbols-outlined');
        var isHidden = passwordField.attr('type') === 'password';

        passwordField.attr('type', isHidden ? 'text' : 'password');
        icon.text(isHidden ? 'visibility_off' : 'visibility');
        $(this).attr('aria-label', isHidden ? 'Hide password' : 'Show password');
        $(this).attr('title', isHidden ? 'Hide password' : 'Show password');
      });
    });
  </script>
</body>

</html>
