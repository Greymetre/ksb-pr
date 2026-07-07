<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password - {{ config('app.name') }}</title>

  <style>
    :root {
      --navy: #071634;
      --deep: #050e24;
      --blue: #1f6bff;
      --cyan: #22d3ee;
      --green: #12d18e;
      --muted: #7d8fbf;
      --line: rgba(90, 130, 220, .2);
      --panel: rgba(9, 22, 52, .72);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      min-height: 100%;
    }

    body {
      color: #e8f0ff;
      background: var(--deep);
      font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      overflow-x: hidden;
    }

    .login-stage {
      display: grid;
      grid-template-columns: minmax(0, 1.2fr) minmax(430px, .8fr);
      min-height: 100vh;
      background: #050e24;
    }

    .scene {
      position: relative;
      min-height: 100vh;
      overflow: hidden;
      background:
        radial-gradient(130% 100% at 30% 20%, #0d2358 0, transparent 55%),
        radial-gradient(110% 90% at 90% 90%, #0a1b45 0, transparent 60%),
        #050e24;
    }

    #globe {
      position: absolute;
      inset: 0;
      z-index: 1;
      width: 100%;
      height: 100%;
    }

    .grain {
      position: absolute;
      inset: 0;
      opacity: .4;
      background-image: radial-gradient(rgba(120, 160, 255, .5) 1px, transparent 1px);
      background-size: 34px 34px;
      -webkit-mask-image: radial-gradient(120% 90% at 40% 40%, #000 20%, transparent 78%);
      mask-image: radial-gradient(120% 90% at 40% 40%, #000 20%, transparent 78%);
      z-index: 2;
    }

    .brand {
      position: absolute;
      top: 40px;
      left: 44px;
      z-index: 6;
    }

    .fk-logo {
      position: relative;
      display: inline-block;
    }

    .fk-logo img {
      display: block;
      width: auto;
      height: 40px;
      object-fit: contain;
      filter: drop-shadow(0 2px 12px rgba(34, 211, 238, .28));
    }

    .fk-logo .sheen {
      position: absolute;
      inset: 0;
      pointer-events: none;
      background:
        repeating-linear-gradient(0deg, rgba(34, 211, 238, .14) 0 1px, transparent 1px 4px),
        linear-gradient(90deg, transparent 40%, rgba(34, 211, 238, .25) 46%, rgba(190, 255, 255, 1) 50%, rgba(34, 211, 238, .25) 54%, transparent 60%);
      background-size: 100% 100%, 300% 100%;
      -webkit-mask: url("{{ asset('assets/img/fieldkonnect_login_logo.png') }}") center / contain no-repeat;
      mask: url("{{ asset('assets/img/fieldkonnect_login_logo.png') }}") center / contain no-repeat;
      animation: scan 3.8s cubic-bezier(.65, 0, .35, 1) infinite;
    }

    @keyframes scan {
      0% {
        background-position: 0 0, 150% 0;
        opacity: 1;
      }

      62% {
        background-position: 0 0, -150% 0;
        opacity: 1;
      }

      70%,
      100% {
        background-position: 0 0, -150% 0;
        opacity: 0;
      }
    }

    .brand small,
    .lhead small {
      display: block;
      margin-top: 7px;
      color: #6d82c0;
      font-size: 8px;
      font-weight: 700;
      letter-spacing: 2.4px;
      text-transform: uppercase;
    }

    .live-pill {
      position: absolute;
      top: 44px;
      right: 44px;
      z-index: 6;
      display: flex;
      gap: 7px;
      align-items: center;
      padding: 6px 12px;
      color: var(--cyan);
      background: rgba(8, 20, 50, .5);
      border: 1px solid rgba(34, 211, 238, .35);
      border-radius: 999px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      backdrop-filter: blur(8px);
    }

    .live-pill i {
      width: 6px;
      height: 6px;
      background: var(--cyan);
      border-radius: 50%;
      box-shadow: 0 0 8px var(--cyan);
      animation: blink 1.4s infinite;
    }

    @keyframes blink {
      50% {
        opacity: .3;
      }
    }

    .cap {
      position: absolute;
      top: 104px;
      left: 50%;
      z-index: 6;
      display: flex;
      gap: 12px;
      align-items: center;
      max-width: 420px;
      padding: 10px 16px;
      background: rgba(9, 22, 52, .92);
      border: 1px solid rgba(34, 211, 238, .3);
      border-radius: 14px;
      box-shadow: 0 10px 40px -10px rgba(0, 0, 0, .6);
      transform: translateX(-50%);
    }

    .cap i {
      flex: none;
      padding: 5px 8px;
      color: #04121f;
      font-size: 11px;
      font-style: normal;
      font-weight: 800;
      background: linear-gradient(135deg, #22d3ee, #3b82f6);
      border-radius: 8px;
    }

    .cap b {
      display: block;
      color: #fff;
      font-size: 12.5px;
      letter-spacing: .4px;
    }

    .cap span {
      display: block;
      margin-top: 2px;
      color: #a9bce6;
      font-size: 11px;
      line-height: 1.4;
    }

    .hud {
      position: absolute;
      z-index: 5;
      padding: 12px 15px;
      background: rgba(9, 22, 52, .6);
      border: 1px solid var(--line);
      border-radius: 14px;
      backdrop-filter: blur(10px);
    }

    .hud.h1 {
      top: 200px;
      left: 44px;
    }

    .hud.h3 {
      top: 200px;
      right: 44px;
    }

    .hud span,
    .hud em {
      display: block;
      color: #8fa4d8;
      font-size: 10px;
      font-style: normal;
    }

    .hud b {
      display: block;
      margin: 3px 0 1px;
      color: #fff;
      font-size: 24px;
      line-height: 1;
    }

    .hud b.c {
      color: var(--cyan);
    }

    .pitch {
      position: absolute;
      left: 44px;
      right: 44px;
      bottom: 44px;
      z-index: 6;
      max-width: 780px;
    }

    .pitch .e {
      margin-bottom: 13px;
      color: var(--cyan);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2.2px;
      text-transform: uppercase;
    }

    .pitch h1 {
      max-width: 700px;
      margin: 0;
      color: #fff;
      font-size: clamp(36px, 5vw, 70px);
      font-weight: 800;
      line-height: .98;
    }

    .pitch h1 em {
      color: var(--cyan);
      font-style: normal;
    }

    .pitch p {
      max-width: 660px;
      margin: 18px 0 0;
      color: #a9bce6;
      font-size: 16px;
      line-height: 1.65;
    }

    .feats {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      max-width: 780px;
      margin-top: 22px;
    }

    .feats span {
      display: inline-flex;
      gap: 7px;
      align-items: center;
      padding: 8px 10px;
      color: #bfd0f1;
      background: rgba(9, 22, 52, .62);
      border: 1px solid rgba(90, 130, 220, .22);
      border-radius: 999px;
      font-size: 11px;
      white-space: nowrap;
    }

    .feats svg {
      width: 14px;
      height: 14px;
      color: var(--cyan);
    }

    .panel {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 36px;
      background:
        radial-gradient(85% 70% at 50% 15%, rgba(31, 107, 255, .18), transparent 58%),
        linear-gradient(180deg, rgba(8, 18, 45, .95), rgba(5, 14, 36, 1));
      border-left: 1px solid rgba(90, 130, 220, .18);
    }

    .login {
      width: min(100%, 430px);
      padding: 34px;
      background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(245, 248, 255, .95));
      border: 1px solid rgba(255, 255, 255, .7);
      border-radius: 24px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, .35);
      color: #0f1f43;
    }

    .lhead {
      text-align: center;
    }

    .lhead .fk-logo img {
      height: 45px;
      max-width: 100%;
    }

    .clientrow {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 76px;
      margin: 24px 0 19px;
      background: #fff;
      border: 1px solid rgba(15, 31, 67, .08);
      border-radius: 18px;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .6), 0 15px 45px rgba(31, 107, 255, .12);
    }

    .clientrow img {
      display: block;
      width: auto;
      max-width: 140px;
      max-height: 58px;
      object-fit: contain;
    }

    .login h2 {
      margin: 0;
      color: #0b1b3f;
      font-size: 30px;
      font-weight: 800;
      letter-spacing: -.2px;
      text-align: center;
    }

    .sub {
      margin: 8px 0 24px;
      color: #6b7898;
      font-size: 14px;
      line-height: 1.5;
      text-align: center;
    }

    .field {
      margin-bottom: 17px;
      text-align: left;
    }

    .field label {
      display: block;
      margin-bottom: 8px;
      color: #25385d;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .7px;
      text-transform: uppercase;
    }

    .ctrl,
    .password-field {
      position: relative;
    }

    .form-control {
      width: 100%;
      height: 52px;
      padding: 0 46px 0 15px;
      color: #0f1f43;
      background: #f7f9fe;
      border: 1px solid #dbe5f5;
      border-radius: 15px;
      font: 500 15px/1 Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .form-control:focus {
      background: #fff;
      border-color: rgba(31, 107, 255, .72);
      box-shadow: 0 0 0 4px rgba(31, 107, 255, .12);
    }

    .form-control.is-invalid {
      border-color: #dc3545;
      box-shadow: 0 0 0 4px rgba(220, 53, 69, .1);
    }

    .field-icon,
    .password-toggle {
      position: absolute;
      top: 50%;
      right: 14px;
      transform: translateY(-50%);
    }

    .field-icon {
      width: 20px;
      height: 20px;
      color: #7e8faf;
      pointer-events: none;
    }

    .password-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 26px;
      height: 26px;
      color: #7e8faf;
      background: transparent;
      border: 0;
      cursor: pointer;
    }

    .password-toggle svg {
      width: 20px;
      height: 20px;
    }

    .password-toggle:focus {
      color: var(--blue);
      outline: none;
    }

    .login-row {
      display: flex;
      justify-content: flex-end;
      margin: -2px 0 19px;
    }

    .fg {
      color: var(--blue);
      font-size: 13px;
      font-weight: 700;
      text-decoration: none;
    }

    .fg:hover {
      color: #174fbe;
      text-decoration: none;
    }

    .captcha-box {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .captcha-box span {
      display: inline-flex;
      align-items: center;
      min-height: 42px;
      padding: 5px;
      background: #fff;
      border: 1px solid #dbe5f5;
      border-radius: 12px;
    }

    .captcha-box img,
    .captcha-box svg {
      max-height: 42px;
    }

    .captcha-refresh {
      height: 38px;
      padding: 0 13px;
      color: #25385d;
      background: #eef3fb;
      border: 1px solid #dbe5f5;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
    }

    .login-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      height: 54px;
      color: #fff;
      background: linear-gradient(135deg, #1f6bff, #22d3ee);
      border: 0;
      border-radius: 999px;
      box-shadow: 0 14px 30px rgba(31, 107, 255, .28);
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 34px rgba(31, 107, 255, .35);
    }

    .login-btn svg {
      width: 19px;
      height: 19px;
    }

    .invalid-feedback,
    .alert {
      display: block;
      margin-top: 8px;
      font-size: 12px;
      line-height: 1.4;
    }

    .invalid-feedback {
      color: #dc3545;
    }

    .alert {
      padding: 11px 13px;
      border-radius: 12px;
      text-align: left;
    }

    .alert-danger {
      color: #8b1e2d;
      background: #ffe9ed;
      border: 1px solid #ffc9d2;
    }

    .alert-success {
      color: #0f6848;
      background: #e8fbf4;
      border: 1px solid #bff0dc;
    }

    .foot {
      margin-top: 20px;
      color: #7d8fbf;
      font-size: 12px;
      text-align: center;
    }

    .foot b {
      color: #33466c;
    }

    @media (max-width: 1180px) {
      .login-stage {
        grid-template-columns: 1fr 430px;
      }

      .hud {
        display: none;
      }

      .pitch h1 {
        font-size: 46px;
      }
    }

    @media (max-width: 900px) {
      body {
        overflow-y: auto;
      }

      .login-stage {
        display: block;
      }

      .scene {
        min-height: 420px;
      }

      .panel {
        min-height: auto;
        padding: 28px 18px 34px;
        border-left: 0;
      }

      .brand {
        top: 24px;
        left: 22px;
      }

      .live-pill {
        top: 27px;
        right: 20px;
      }

      .cap {
        top: 82px;
        width: calc(100% - 36px);
      }

      .pitch {
        left: 22px;
        right: 22px;
        bottom: 22px;
      }

      .pitch h1 {
        font-size: 38px;
      }

      .pitch p {
        font-size: 14px;
      }

      .feats {
        max-height: 86px;
        overflow: hidden;
      }
    }

    @media (max-width: 520px) {
      .scene {
        min-height: 360px;
      }

      .brand .fk-logo img {
        height: 30px;
      }

      .brand small,
      .live-pill,
      .cap span,
      .feats {
        display: none;
      }

      .cap {
        top: 70px;
        justify-content: center;
        width: auto;
        max-width: calc(100% - 36px);
      }

      .pitch h1 {
        font-size: 32px;
      }

      .pitch p {
        margin-top: 12px;
      }

      .login {
        padding: 25px 18px;
        border-radius: 20px;
      }

      .lhead .fk-logo img {
        height: 36px;
      }

      .clientrow {
        height: 68px;
        margin: 19px 0 16px;
      }

      .login h2 {
        font-size: 26px;
      }
    }
    /* Reference-match overrides for the standalone FieldKonnect login */
    body {
      overflow: hidden;
    }

    .login-stage {
      grid-template-columns: 1.2fr .8fr;
      height: 100vh;
      min-height: 0;
    }

    .scene {
      min-height: 0;
    }

    .live-pill {
      display: none;
    }

    .pitch {
      right: auto;
      bottom: 56px;
      max-width: 470px;
    }

    .pitch .e {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-bottom: 12px;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 3px;
    }

    .pitch .e:before {
      content: "";
      width: 26px;
      height: 2px;
      background: var(--cyan);
    }

    .pitch h1 {
      max-width: none;
      font-size: 31px;
      line-height: 1.16;
    }

    .pitch h1 em {
      color: transparent;
      background: linear-gradient(90deg, #7cb8ff, #22d3ee);
      -webkit-background-clip: text;
      background-clip: text;
    }

    .pitch p {
      max-width: none;
      margin-top: 12px;
      font-size: 14px;
      line-height: 1.6;
    }

    .feats {
      gap: 7px;
      max-width: none;
      margin-top: 16px;
    }

    .feats span {
      position: relative;
      gap: 6px;
      padding: 6px 10px;
      overflow: hidden;
      color: #cbd9ff;
      background: rgba(9, 22, 52, .85);
      border-color: var(--line);
      font-size: 9.5px;
      font-weight: 700;
      letter-spacing: .5px;
      transition: all .45s ease;
    }

    .feats svg {
      width: 12px;
      height: 12px;
      flex: none;
    }

    .feats span.on {
      color: #fff;
      background: rgba(34, 211, 238, .13);
      border-color: rgba(34, 211, 238, .7);
      box-shadow: 0 0 22px rgba(34, 211, 238, .3);
    }

    .feats span.on:after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, #22d3ee, #12d18e);
      transform-origin: left;
      animation: chipfill 4.2s linear both;
    }

    @keyframes chipfill {
      from { transform: scaleX(0); }
      to { transform: scaleX(1); }
    }

    .panel {
      min-height: 0;
      padding: 0;
      overflow: hidden;
      background: linear-gradient(180deg, #081a40, #050e24);
      border-left: 1px solid var(--line);
    }

    .panel::before {
      content: "";
      position: absolute;
      top: -150px;
      right: -130px;
      width: 460px;
      height: 460px;
      background: radial-gradient(circle, rgba(34, 211, 238, .14), transparent 70%);
      border-radius: 50%;
    }

    .login {
      position: relative;
      z-index: 2;
      width: min(392px, 86%);
      padding: 40px 36px 30px;
      color: #e8f0ff;
      background: rgba(12, 26, 60, .55);
      border: 1px solid rgba(120, 160, 255, .18);
      border-radius: 24px;
      box-shadow: 0 40px 100px -30px rgba(0, 0, 0, .7), inset 0 1px 0 rgba(255, 255, 255, .1);
      backdrop-filter: blur(24px) saturate(1.2);
    }

    .lhead {
      margin-bottom: 8px;
    }

    .lhead .fk-logo img {
      height: 44px;
    }

    .lhead small {
      letter-spacing: 4px;
    }

    .clientrow {
      height: auto;
      margin: 18px 0 22px;
      padding: 12px;
      background: rgba(120, 160, 255, .06);
      border: 1px solid rgba(120, 160, 255, .18);
      border-radius: 12px;
      box-shadow: none;
    }

    .clientrow img {
      max-width: 140px;
      max-height: 34px;
    }

    .login h2 {
      color: #fff;
      font-size: 24px;
      text-align: left;
    }

    .sub {
      color: var(--muted);
      font-size: 13px;
      text-align: left;
    }

    .field label {
      color: var(--muted);
      font-size: 11px;
    }

    .form-control {
      height: 51px;
      padding: 0 44px;
      color: #e8f0ff;
      background: rgba(8, 20, 50, .6);
      border: 1px solid var(--line);
      border-radius: 13px;
      font-size: 14px;
    }

    .form-control::placeholder {
      color: #5a6a95;
    }

    .form-control:focus {
      color: #e8f0ff;
      background: rgba(12, 26, 60, .85);
      border-color: var(--cyan);
      box-shadow: 0 0 0 3px rgba(34, 211, 238, .15), 0 0 20px rgba(34, 211, 238, .12);
    }

    .field-icon {
      right: auto;
      left: 15px;
      color: #5a6a95;
    }

    .ctrl:focus-within .field-icon {
      color: var(--cyan);
    }

    .password-toggle {
      right: 12px;
      width: 34px;
      height: 34px;
      color: #5a6a95;
      border-radius: 9px;
    }

    .password-toggle svg {
      width: 24px;
      height: 24px;
    }

    .login-row {
      align-items: center;
      justify-content: space-between;
      margin: 6px 0 22px;
    }

    .rm {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      font-size: 12.5px;
      font-weight: 700;
      letter-spacing: .6px;
      text-transform: uppercase;
      cursor: pointer;
    }

    .rm input {
      display: none;
    }

    .bx {
      display: grid;
      place-items: center;
      width: 18px;
      height: 18px;
      border: 1px solid #3a4d80;
      border-radius: 5px;
    }

    .bx svg {
      width: 11px;
      height: 11px;
      color: #050e24;
      opacity: 0;
    }

    .rm input:checked + .bx {
      background: var(--cyan);
      border-color: var(--cyan);
    }

    .rm input:checked + .bx svg {
      opacity: 1;
    }

    .fg,
    .fg:hover {
      color: var(--cyan);
      font-size: 12.5px;
    }

    .back-row {
      margin-top: 18px;
      text-align: center;
    }

    .password-help {
      margin-top: 8px;
      color: #7d8fbf;
      font-size: 11px;
      line-height: 1.45;
    }

    .password-help .text-success {
      color: #12d18e;
    }

    .password-help .text-danger {
      color: #ff8a9d;
    }

    .form-control.is-valid {
      border-color: #12d18e;
      box-shadow: 0 0 0 3px rgba(18, 209, 142, .15);
    }

    .captcha-box span,
    .captcha-refresh {
      color: var(--cyan);
      background: rgba(8, 20, 50, .6);
      border-color: var(--line);
    }

    .login-btn {
      height: 53px;
      color: #04121f;
      background: linear-gradient(135deg, #22d3ee, #3b82f6);
      border-radius: 13px;
      box-shadow: 0 0 32px rgba(34, 211, 238, .35);
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 46px rgba(34, 211, 238, .55);
    }

    .foot {
      color: #5a6a95;
      font-size: 10px;
    }

    .foot b {
      color: var(--cyan);
    }

    @media (max-width: 1100px) {
      body {
        overflow: auto;
      }

      .login-stage {
        display: grid;
        grid-template-columns: 1fr;
        height: auto;
        min-height: 100vh;
      }

      .scene {
        height: 58vh;
        min-height: 500px;
        border-bottom: 1px solid var(--line);
      }

      .panel {
        min-height: auto;
        padding: 40px 0;
        border-left: none;
      }

      .pitch {
        right: 30px;
        max-width: none;
      }

      .pitch h1 {
        font-size: 26px;
      }
    }

    @media (max-width: 640px) {
      .scene {
        height: auto;
        min-height: 0;
        padding-bottom: 24px;
      }

      .cap {
        position: relative;
        top: auto;
        left: auto;
        width: auto;
        max-width: none;
        margin: 86px 18px 0;
        transform: none;
      }

      .cap span,
      .feats {
        display: flex;
      }

      .pitch {
        position: relative;
        left: auto;
        right: auto;
        bottom: auto;
        margin: 210px 18px 0;
      }

      .pitch h1 {
        font-size: 21px;
      }

      .pitch p {
        font-size: 12.5px;
      }

      .feats {
        gap: 6px;
        max-height: none;
        overflow: visible;
      }

      .feats span {
        padding: 5px 9px;
        font-size: 8.5px;
      }

      .login {
        width: min(392px, 91%);
        padding: 30px 22px 22px;
      }
    }
  </style>
</head>

<body>
  <main class="login-stage">
    <section class="scene" aria-label="FieldKonnect territory overview">
      <canvas id="globe" width="960" height="800"></canvas>
      <div class="grain"></div>

      <div class="brand">
        <span class="fk-logo">
          <img src="{{ asset('assets/img/fieldkonnect_login_logo.png') }}" alt="FieldKonnect">
          <span class="sheen"></span>
        </span>
        <small>Powered by Greymetre</small>
      </div>

      <div class="live-pill"><i></i>LIVE TERRITORY</div>

      <div class="cap" id="cap">
        <i>1/14</i>
        <div>
          <b>Customer Management</b>
          <span>Every customer mapped & managed in one place</span>
        </div>
      </div>

      <div class="hud h1"><span>New leads captured</span><b class="c">243</b><em>+18% today</em></div>
      <div class="hud h3"><span>Orders in transit</span><b>128</b><em>across 6 states</em></div>

      <div class="pitch">
        <div class="e">One Platform · Powerful Modules</div>
        <h1>Watch your entire field <em>come alive</em>.</h1>
        <p>Customers, leads, orders, live employee tracking and loyalty rewards moving in real time across your territory.</p>
        <div class="feats">
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"></circle><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"></path><path d="M16 4.5a3.5 3.5 0 0 1 0 7"></path><path d="M17.5 14.3c2.1.8 3.5 2.6 3.5 5.7"></path></svg>Customer Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h18l-7 8v6l-4 2v-8L3 4Z"></path></svg>Lead Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h12l1.5 13h-15L6 7Z"></path><path d="M9 10V6a3 3 0 0 1 6 0v4"></path></svg>Order Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-5.6-7-11a7 7 0 0 1 14 0c0 5.4-7 11-7 11Z"></path><circle cx="12" cy="10" r="2.6"></circle></svg>Employee Live Tracking</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1L3.2 9.4l6.1-.9L12 3Z"></path></svg>Loyalty Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8 12 3 3 8v8l9 5 9-5V8Z"></path><path d="M3 8l9 5 9-5M12 13v8"></path></svg>Product Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="3"></rect><path d="m8.5 12.5 2.5 2.5 5-5.5"></path></svg>Task Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="18" r="2.5"></circle><circle cx="18" cy="6" r="2.5"></circle><path d="M8 17c6 0 4-10 8-10" stroke-dasharray="3 3"></path></svg>Beat Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"></path><path d="M9 8h6M9 12h6"></path></svg>Expense Management</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"></path></svg>Dashboards</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="3"></rect><path d="M3 9h18M8 2v4M16 2v4"></path></svg>Material Planning</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v5l3.5 2"></path></svg>Scheduling &amp; Execution</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h16"></path><circle cx="18" cy="12" r="2.5"></circle></svg>DDMRP</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 4h8M10 4v6L4 20h16l-6-10V4"></path></svg>TOC</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="login">
        <div class="lhead">
          <span class="fk-logo">
            <img src="{{ asset('assets/img/fieldkonnect_login_logo.png') }}" alt="FieldKonnect">
            <span class="sheen"></span>
          </span>
          <small>Range · Availability · Reach · Engagement</small>
        </div>

        <div class="clientrow">
          <img src="{{ asset('assets/img/duke_logo_new.png') }}" alt="Duke Pipes">
        </div>

        <h2>Reset password</h2>
        <p class="sub">Enter your email address and choose a new password for your account.</p>

        <form method="POST" action="{{ route('password.update') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $request->route('token') }}">

          <div class="field">
            <label for="email">Email address</label>
            <div class="ctrl">
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $request->email) }}" placeholder="you@company.com" required autofocus>
              <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
            </div>
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="field">
            <label for="password">New Password</label>
            <div class="password-field">
              <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter new password" minlength="12" required>
              <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
              <button type="button" class="password-toggle" data-toggle-password="#password" aria-label="Show password" title="Show password">
                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a17.8 17.8 0 0 1-3.2 4.2"></path><path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7c1.6 0 3-.4 4.2-1"></path></svg>
              </button>
            </div>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="password-help">
              Minimum 12 characters with uppercase, lowercase, number, and special character.
            </div>
            <div id="password-strength" class="password-help"></div>
          </div>

          <div class="field">
            <label for="password_confirmation">Confirm New Password</label>
            <div class="password-field">
              <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" minlength="12" required>
              <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
              <button type="button" class="password-toggle" data-toggle-password="#password_confirmation" aria-label="Show password confirmation" title="Show password">
                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a17.8 17.8 0 0 1-3.2 4.2"></path><path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7c1.6 0 3-.4 4.2-1"></path></svg>
              </button>
            </div>
            @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <button type="submit" class="login-btn">
            Reset Password
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
          </button>

          @if(session()->has('error'))
          <div class="alert alert-danger">
            {{ session()->get('error') }}
          </div>
          @endif
          @if (session('status'))
          <div class="alert alert-success">
            {{ session('status') }}
          </div>
          @endif
        </form>

        <div class="back-row">
          <a href="{{ route('login') }}" class="fg">Back to Login</a>
        </div>

        <div class="foot">&copy; {{ date('Y') }} <b>FieldKonnect</b>. All rights reserved</div>
      </div>
    </section>
  </main>

  <script>
    @if(session()->has('error'))
      console.error('Reset password issue:', @json(session()->get('error')));
    @endif

    @if(session('status'))
      console.info('Reset password flow completed:', @json(session('status')));
    @endif

    (function() {
      var email = document.getElementById('email');
      var password = document.getElementById('password');
      var confirmation = document.getElementById('password_confirmation');
      var strength = document.getElementById('password-strength');

      @if(empty(old('email', $request->email)))
      if (email) email.focus();
      @else
      if (password) password.focus();
      @endif

      function validatePassword(value) {
        var missing = [];
        if (value.length < 12) missing.push('12 characters');
        if (!/[A-Z]/.test(value)) missing.push('uppercase letter');
        if (!/[a-z]/.test(value)) missing.push('lowercase letter');
        if (!/[0-9]/.test(value)) missing.push('number');
        if (!/[^A-Za-z0-9]/.test(value)) missing.push('special character');
        return missing;
      }

      function setClass(el, valid) {
        if (!el) return;
        el.classList.toggle('is-valid', valid === true);
        el.classList.toggle('is-invalid', valid === false);
      }

      if (password) {
        password.addEventListener('keyup', function() {
          var missing = validatePassword(password.value);
          if (password.value.length === 0) {
            setClass(password, null);
            if (strength) strength.innerHTML = '';
            return;
          }

          if (missing.length === 0) {
            setClass(password, true);
            if (strength) strength.innerHTML = '<span class="text-success">Password meets all requirements</span>';
          } else {
            setClass(password, false);
            if (strength) strength.innerHTML = '<span class="text-danger">Missing: ' + missing.join(', ') + '</span>';
          }
        });
      }

      if (confirmation) {
        confirmation.addEventListener('keyup', function() {
          if (confirmation.value.length === 0) {
            setClass(confirmation, null);
            return;
          }
          setClass(confirmation, password && confirmation.value === password.value);
        });
      }

      document.querySelectorAll('[data-toggle-password]').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
          var passwordField = document.querySelector(toggle.getAttribute('data-toggle-password'));
          if (!passwordField) return;

          var isHidden = passwordField.getAttribute('type') === 'password';
          passwordField.setAttribute('type', isHidden ? 'text' : 'password');
          toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
          toggle.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
          toggle.querySelector('.eye-on').style.display = isHidden ? 'none' : '';
          toggle.querySelector('.eye-off').style.display = isHidden ? '' : 'none';
        });
      });

      var sc = document.querySelector('.scene'), c = document.getElementById('globe');
      if (!sc || !c || !c.getContext) return;
      var x = c.getContext('2d'), W = 0, H = 0;
      function rs() { W = c.width = sc.clientWidth || 960; H = c.height = sc.clientHeight || 800; }
      rs(); window.addEventListener('resize', rs);
      var chips = [].slice.call(document.querySelectorAll('.feats span'));
      var cap = document.getElementById('cap');
      var h1 = document.querySelector('.hud.h1'), h3 = document.querySelector('.hud.h3');
      var HUDS = [
        [['Active customers', '1.2 L', '+ 4.2K this month'], ['Towns covered', '860+', 'across 14 states']],
        [['New leads captured', '18.4K', '+ 18% today'], ['Leads converted', '6.2K', 'this quarter']],
        [['Orders booked', '₹4.8 Cr', 'this month'], ['Orders in transit', '128', 'across 6 states']],
        [['Reps live now', '342', 'on the map'], ['Km tracked today', '12.6K', 'auto-logged']],
        [['Points issued', '32 L', 'this year'], ['Rewards redeemed', '₹18 L', '+ 9% this month']],
        [['SKUs in catalogue', '2.4K', 'with live pricing'], ['New launches', '36', 'this quarter']],
        [['Tasks assigned', '5.8K', 'this week'], ['Completed on time', '92%', '+ 6% vs last week']],
        [['Beats planned', '1.4K', 'this month'], ['Visit adherence', '96%', 'on schedule']],
        [['Claims processed', '₹42 L', 'this month'], ['Avg approval time', '6 hrs', '40% faster']],
        [['Data points daily', '8.2 L', 'streaming live'], ['Reports auto-built', '120', 'every day']],
        [['Material planned', '₹6.5 Cr', 'this quarter'], ['Stockout risk', '38%', 'vs last year']],
        [['Jobs scheduled', '3.2K', 'this week'], ['On-time execution', '94%', '+ 5%']],
        [['Buffer health', '91%', 'in green zone'], ['Inventory freed', '₹2.1 Cr', 'this year']],
        [['Throughput gain', '22%', 'year on year'], ['Constraints resolved', '14', 'this year']]
      ];
      function setHud(el, d) {
        if (!el) return;
        el.querySelector('span').textContent = d[0];
        el.querySelector('b').textContent = d[1];
        el.querySelector('em').textContent = d[2];
        el.style.animation = 'none'; void el.offsetWidth; el.style.animation = 'fadein .6s both';
      }
      var CAPT = [
        ['Customer Management', 'Every customer mapped & managed in one place'],
        ['Lead Management', 'Every enquiry from the field captured - no lead slips away'],
        ['Order Management', 'Book & dispatch orders straight from the field'],
        ['Employee Live Tracking', 'Watch your whole team move live on the map'],
        ['Loyalty Management', 'Points & rewards that keep customers coming back'],
        ['Product Management', 'Full catalogue & pricing at every rep\'s fingertips'],
        ['Task Management', 'Assign tasks & watch them get done in the field'],
        ['Beat Management', 'Planned beats & routes - every visit on schedule'],
        ['Expense Management', 'Field expenses claimed, tracked & approved digitally'],
        ['Dashboards', 'Live dashboards that turn field data into decisions'],
        ['Material Planning', 'Right material, right place, right time - planned ahead'],
        ['Scheduling & Execution', 'Plans turned into schedules, executed on the ground'],
        ['DDMRP', 'Demand-driven buffers that keep stock flowing, never piling'],
        ['TOC', 'Find the constraint, fix the flow - throughput up']
      ];
      function lbl(p, txt, col, a, dy) { x.save(); x.globalAlpha = a; x.fillStyle = col; x.font = '600 9px Sora, Inter, sans-serif'; x.textAlign = 'center'; x.fillText(txt, p[0], p[1] + (dy == null ? 22 : dy)); x.restore(); }
      var HQ = [.46, .38];
      var SR = [[.72, .14], [.86, .40], [.70, .64], [.24, .16], [.14, .48]];
      var PH = 4200;
      function pxy(p) { return [p[0] * W, p[1] * H]; }
      function route(a, b) { var mx = (a[0] + b[0]) / 2, my = (a[1] + b[1]) / 2, dx = b[0] - a[0], dy = b[1] - a[1], L = Math.hypot(dx, dy) || 1; return [mx - dy / L * L * .22, my + dx / L * L * .22]; }
      function qp(a, cp, b, t) { var u = 1 - t; return [u * u * a[0] + 2 * u * t * cp[0] + t * t * b[0], u * u * a[1] + 2 * u * t * cp[1] + t * t * b[1]]; }
      function qlen(a, cp, b) { var L = 0, p = a; for (var i = 1; i <= 12; i++) { var q = qp(a, cp, b, i / 12); L += Math.hypot(q[0] - p[0], q[1] - p[1]); p = q; } return L; }
      function strokeRoute(a, cp, b, from, to, col, lw, dash) {
        var L = qlen(a, cp, b); x.save(); x.strokeStyle = col; x.lineWidth = lw; x.lineCap = 'round';
        if (dash) { x.setLineDash([6, 7]); x.lineDashOffset = -Date.now() / 28; } else { x.setLineDash([L * (to - from), L * 2]); x.lineDashOffset = -L * from; }
        x.beginPath(); x.moveTo(a[0], a[1]); x.quadraticCurveTo(cp[0], cp[1], b[0], b[1]); x.stroke(); x.restore();
      }
      function dot(p, r, col, blur) { x.fillStyle = col; if (blur) { x.shadowColor = col; x.shadowBlur = blur; } x.beginPath(); x.arc(p[0], p[1], r, 0, 7); x.fill(); x.shadowBlur = 0; }
      function ring(p, r, col, lw) { x.strokeStyle = col; x.lineWidth = lw || 1.2; x.beginPath(); x.arc(p[0], p[1], r, 0, 7); x.stroke(); }
      function star(px, py, r, rot, col) { x.fillStyle = col; x.shadowColor = col; x.shadowBlur = 8; x.beginPath(); for (var i = 0; i < 10; i++) { var an = rot + i * Math.PI / 5, rr = i % 2 ? r * .45 : r; x.lineTo(px + Math.cos(an) * rr, py + Math.sin(an) * rr); } x.closePath(); x.fill(); x.shadowBlur = 0; }
      function person(p, a) { x.save(); x.globalAlpha = a; x.strokeStyle = '#22d3ee'; x.lineWidth = 1.6; x.fillStyle = '#22d3ee'; x.beginPath(); x.arc(p[0], p[1] - 19, 3, 0, 7); x.fill(); x.beginPath(); x.arc(p[0], p[1] - 9, 5.5, Math.PI, 2 * Math.PI); x.stroke(); x.restore(); }
      function box(p, s, col) { x.save(); x.fillStyle = col; x.shadowColor = col; x.shadowBlur = 10; x.fillRect(p[0] - s, p[1] - s, s * 2, s * 2); x.shadowBlur = 0; x.strokeStyle = 'rgba(5,14,36,.9)'; x.lineWidth = 1; x.beginPath(); x.moveTo(p[0] - s, p[1] - s * .2); x.lineTo(p[0] + s, p[1] - s * .2); x.stroke(); x.restore(); }
      var t0 = Date.now(), last = -1, sparks = [], stars = [], flash = 0;
      function frame() {
        var now = Date.now() - t0, phase = Math.floor(now / PH) % 14, pp = (now % PH) / PH, env = Math.min(1, pp * 5, (1 - pp) * 5);
        var vis = phase < 10 ? phase : [5, 7, 2, 9][phase - 10];
        if (phase !== last) {
          last = phase; sparks = []; stars = []; flash = 0;
          for (var i = 0; i < chips.length; i++) chips[i].classList.toggle('on', i === phase);
          if (cap) {
            cap.classList.remove('sw'); void cap.offsetWidth; cap.classList.add('sw');
            cap.querySelector('i').textContent = (phase + 1) + '/14';
            cap.querySelector('b').textContent = CAPT[phase][0];
            cap.querySelector('span').textContent = CAPT[phase][1];
          }
          setHud(h1, HUDS[phase][0]); setHud(h3, HUDS[phase][1]);
        }
        x.clearRect(0, 0, W, H);
        var hq = pxy(HQ), S = [], CP = [];
        for (var i = 0; i < 5; i++) { S.push(pxy(SR[i])); CP.push(route(hq, S[i])); }
        var gr = x.createRadialGradient(hq[0], hq[1], 20, hq[0], hq[1], W * .42); gr.addColorStop(0, 'rgba(31,107,255,.10)'); gr.addColorStop(1, 'rgba(31,107,255,0)'); x.fillStyle = gr; x.fillRect(0, 0, W, H);
        for (var i = 0; i < 5; i++) strokeRoute(hq, CP[i], S[i], 0, 1, 'rgba(90,130,220,.16)', 1);
        ring(hq, W * .11, 'rgba(90,130,220,.10)'); ring(hq, W * .2, 'rgba(90,130,220,.07)');
        for (var i = 0; i < 5; i++) { dot(S[i], 4.5, 'rgba(124,200,255,.9)', 8); ring(S[i], 9, 'rgba(124,200,255,.25)'); }
        dot(hq, 7, '#22d3ee', 18); ring(hq, 13 + 3 * Math.sin(now / 350), 'rgba(34,211,238,.4)', 1.4);
        x.save(); x.translate(hq[0], hq[1]); x.rotate(now / 900); x.strokeStyle = 'rgba(34,211,238,.5)'; x.lineWidth = 1.4; x.beginPath(); x.arc(0, 0, 19, 0, 1.5); x.stroke(); x.beginPath(); x.arc(0, 0, 19, Math.PI, Math.PI + 1.5); x.stroke(); x.restore();
        lbl(hq, 'YOUR BUSINESS', '#7cc8ff', .75, 34);
        if (vis === 0) {
          for (var k = 0; k < 5; k++) { var ap = pp * 6.5 - k; if (ap <= 0) continue; var a1 = Math.min(1, ap); strokeRoute(hq, CP[k], S[k], 0, Math.min(1, ap * 1.3), 'rgba(34,211,238,.55)', 1.5); if (a1 >= 1) { var f = ((now / 900) + k * .2) % 1; ring(S[k], 9 + f * 20, 'rgba(34,211,238,' + (.5 * (1 - f) * env) + ')', 1.4); } person(S[k], a1 * env); lbl(S[k], 'CUSTOMER', '#22d3ee', .8 * a1 * env); }
        } else if (vis === 1) {
          if (Math.random() < .16 && sparks.length < 26) { var side = Math.floor(Math.random() * 4), sx, sy; if (side === 0) { sx = Math.random() * W; sy = -10; } else if (side === 1) { sx = W + 10; sy = Math.random() * H * .7; } else if (side === 2) { sx = Math.random() * W; sy = H * .72; } else { sx = -10; sy = Math.random() * H * .7; } sparks.push({ a: [sx, sy], cp: route([sx, sy], hq), t: 0, sp: .007 + Math.random() * .007 }); }
          for (var i = sparks.length - 1; i >= 0; i--) { var s = sparks[i]; s.t += s.sp; if (s.t >= 1) { sparks.splice(i, 1); flash = 1; continue; } var p = qp(s.a, s.cp, hq, s.t), pb = qp(s.a, s.cp, hq, Math.max(0, s.t - .06)); var cy2 = s.t > .55; x.strokeStyle = cy2 ? 'rgba(34,211,238,.8)' : 'rgba(150,165,200,.45)'; x.lineWidth = 1.6; x.beginPath(); x.moveTo(pb[0], pb[1]); x.lineTo(p[0], p[1]); x.stroke(); dot(p, cy2 ? 2.6 : 2, cy2 ? '#22d3ee' : 'rgba(150,165,200,.8)', cy2 ? 8 : 0); if (cy2 && i % 3 === 0) lbl(p, 'NEW LEAD', '#22d3ee', .7, -8); }
          if (flash > 0) { ring(hq, 13 + (1 - flash) * 26, 'rgba(34,211,238,' + (flash * .7) + ')', 2); flash -= .04; }
        } else if (vis === 2) {
          for (var i = 0; i < 4; i++) { var k = i % 5, tb = pp * 1.9 - i * .22; if (tb < 0 || tb > 1.15) continue; var tt = Math.min(1, tb); strokeRoute(hq, CP[k], S[k], 0, 1, 'rgba(34,211,238,.35)', 1.3, true); if (tb <= 1) { var p = qp(hq, CP[k], S[k], tt); box(p, 4.5, '#22d3ee'); lbl(p, 'ORDER', '#22d3ee', .8, -10); } else { var f = (tb - 1) / .15; ring(S[k], 9 + f * 18, 'rgba(18,209,142,' + (.7 * (1 - f)) + ')', 1.6); lbl(S[k], 'DELIVERED', '#12d18e', .8 * (1 - f)); } }
        } else if (vis === 3) {
          var Rr = W * .24, ang = now / 650; var cg = x.createRadialGradient(hq[0], hq[1], 0, hq[0], hq[1], Rr); cg.addColorStop(0, 'rgba(18,209,142,.10)'); cg.addColorStop(1, 'rgba(18,209,142,0)'); x.save(); x.beginPath(); x.moveTo(hq[0], hq[1]); x.arc(hq[0], hq[1], Rr, ang, ang + .6); x.closePath(); x.fillStyle = cg; x.fill(); x.restore(); x.strokeStyle = 'rgba(18,209,142,.5)'; x.lineWidth = 1.4; x.beginPath(); x.moveTo(hq[0], hq[1]); x.lineTo(hq[0] + Math.cos(ang + .6) * Rr, hq[1] + Math.sin(ang + .6) * Rr); x.stroke(); ring(hq, Rr * .5, 'rgba(18,209,142,.14)'); ring(hq, Rr, 'rgba(18,209,142,.14)');
          for (var i = 0; i < 4; i++) { var k = i, tt = Math.abs(((now * .00022 + i * .27) % 2) - 1); strokeRoute(hq, CP[k], S[k], Math.max(0, tt - .2), tt, 'rgba(18,209,142,.5)', 2); var p = qp(hq, CP[k], S[k], tt); dot(p, 3.6, '#12d18e', 12); ring(p, 8 + 3 * Math.sin(now / 300 + i), 'rgba(18,209,142,.4)'); lbl(p, 'REP ' + (i + 1) + ' · LIVE', '#12d18e', .85, -10); }
        } else if (vis === 4) {
          if (Math.random() < .14 && stars.length < 18) { var k = Math.floor(Math.random() * 5); stars.push({ a: S[k].slice(), cp: route(S[k], hq), t: 0, sp: .008 + Math.random() * .008 }); }
          for (var i = stars.length - 1; i >= 0; i--) { var s = stars[i]; s.t += s.sp; if (s.t >= 1) { stars.splice(i, 1); continue; } var p = qp(s.a, s.cp, hq, s.t); star(p[0], p[1], 5.5 * (1 - s.t * .4), now / 300 + i, '#12d18e'); if (i % 4 === 0) lbl(p, '+1 PT', '#12d18e', .75, -9); }
          for (var k = 0; k < 5; k++) { var f = ((now / 1100) + k * .23) % 1; ring(S[k], 9 + f * 16, 'rgba(18,209,142,' + (.45 * (1 - f) * env) + ')', 1.3); }
          x.strokeStyle = '#12d18e'; x.lineWidth = 3; x.lineCap = 'round'; x.shadowColor = '#12d18e'; x.shadowBlur = 10; x.beginPath(); x.arc(hq[0], hq[1], 17, -Math.PI / 2, -Math.PI / 2 + pp * 2 * Math.PI); x.stroke(); x.shadowBlur = 0; lbl(hq, 'REWARDS', '#12d18e', .85, -26);
        } else if (vis === 5) {
          for (var k = 0; k < 6; k++) { var ap = pp * 7.5 - k; if (ap <= 0) continue; var a1 = Math.min(1, ap); var gx = hq[0] + (k % 3 - 1) * 46, gy = hq[1] - 64 - Math.floor(k / 3) * 40; x.save(); x.globalAlpha = a1 * env; x.strokeStyle = '#22d3ee'; x.lineWidth = 1.4; x.fillStyle = 'rgba(34,211,238,.12)'; x.beginPath(); x.roundRect(gx - 14, gy - 11, 28, 22, 5); x.fill(); x.stroke(); dot([gx - 6, gy - 3], 2.2, '#7cc8ff', 0); x.strokeStyle = 'rgba(124,200,255,.7)'; x.lineWidth = 1.2; x.beginPath(); x.moveTo(gx - 1, gy + 3); x.lineTo(gx + 9, gy + 3); x.moveTo(gx - 1, gy - 3); x.lineTo(gx + 9, gy - 3); x.stroke(); x.restore(); }
          lbl(hq, 'CATALOGUE · PRICING', '#22d3ee', .85 * env, -26);
        } else if (vis === 6) {
          for (var k = 0; k < 5; k++) { var ap = pp * 6.5 - k; if (ap <= 0) continue; var a1 = Math.min(1, ap); var p = S[k]; x.save(); x.globalAlpha = a1 * env; x.strokeStyle = '#12d18e'; x.lineWidth = 1.8; x.beginPath(); x.roundRect(p[0] - 9, p[1] - 33, 18, 18, 4); x.stroke(); if (a1 >= 1) { x.beginPath(); x.moveTo(p[0] - 4, p[1] - 24); x.lineTo(p[0] - 1, p[1] - 21); x.lineTo(p[0] + 5, p[1] - 29); x.stroke(); lbl(p, 'TASK DONE', '#12d18e', .85 * env); } x.restore(); }
        } else if (vis === 7) {
          var order = [3, 4, 2, 1, 0], chain = [hq]; for (var i = 0; i < 5; i++) chain.push(S[order[i]]);
          for (var i = 0; i < 5; i++) { x.strokeStyle = 'rgba(90,130,220,.3)'; x.lineWidth = 1.2; x.setLineDash([5, 6]); x.beginPath(); x.moveTo(chain[i][0], chain[i][1]); x.lineTo(chain[i + 1][0], chain[i + 1][1]); x.stroke(); x.setLineDash([]); lbl(S[order[i]], 'STOP ' + (i + 1), '#7cc8ff', .8 * env); }
          var tt = pp * 5, seg = Math.min(4, Math.floor(tt)), f = Math.min(1, tt - seg); for (var i = 0; i < seg; i++) { x.strokeStyle = 'rgba(34,211,238,.7)'; x.lineWidth = 2; x.beginPath(); x.moveTo(chain[i][0], chain[i][1]); x.lineTo(chain[i + 1][0], chain[i + 1][1]); x.stroke(); } var a = chain[seg], b = chain[seg + 1], p = [a[0] + (b[0] - a[0]) * f, a[1] + (b[1] - a[1]) * f]; x.strokeStyle = 'rgba(34,211,238,.7)'; x.lineWidth = 2; x.beginPath(); x.moveTo(a[0], a[1]); x.lineTo(p[0], p[1]); x.stroke(); dot(p, 4, '#22d3ee', 12); ring(p, 9, 'rgba(34,211,238,.4)');
        } else if (vis === 8) {
          if (Math.random() < .14 && stars.length < 16) { var k = Math.floor(Math.random() * 5); stars.push({ a: S[k].slice(), cp: route(S[k], hq), t: 0, sp: .008 + Math.random() * .008 }); }
          for (var i = stars.length - 1; i >= 0; i--) { var s = stars[i]; s.t += s.sp; if (s.t >= 1) { stars.splice(i, 1); continue; } var p = qp(s.a, s.cp, hq, s.t); dot(p, 6, '#12d18e', 10); x.fillStyle = '#04121f'; x.font = '700 8px Sora, Inter, sans-serif'; x.textAlign = 'center'; x.fillText('₹', p[0], p[1] + 3); }
          x.strokeStyle = '#12d18e'; x.lineWidth = 3; x.lineCap = 'round'; x.beginPath(); x.arc(hq[0], hq[1], 17, -Math.PI / 2, -Math.PI / 2 + pp * 2 * Math.PI); x.stroke(); lbl(hq, 'CLAIMED → APPROVED', '#12d18e', .85 * env, -26);
        } else {
          var bw = 16, bx0 = hq[0] - 70, by = hq[1] - 58, hts = [.5, .85, .6, 1, .75];
          for (var k = 0; k < 5; k++) { var ap = Math.max(0, Math.min(1, pp * 6 - k * .6)), hh = hts[k] * 54 * ap; x.save(); x.globalAlpha = env; var bg = x.createLinearGradient(0, by - hh, 0, by); bg.addColorStop(0, '#22d3ee'); bg.addColorStop(1, 'rgba(34,211,238,.15)'); x.fillStyle = bg; x.beginPath(); x.roundRect(bx0 + k * (bw + 12), by - hh, bw, hh, 3); x.fill(); x.restore(); }
          x.save(); x.globalAlpha = env; x.strokeStyle = '#12d18e'; x.lineWidth = 2; x.lineJoin = 'round'; x.beginPath(); for (var k = 0; k < 5; k++) { var px2 = bx0 + k * (bw + 12) + bw / 2, py2 = by - hts[k] * 54 - 12; k === 0 ? x.moveTo(px2, py2) : x.lineTo(px2, py2); } x.stroke(); x.restore(); lbl(hq, 'LIVE INSIGHTS', '#22d3ee', .85 * env, -26);
        }
        requestAnimationFrame(frame);
      }
      frame();
    })();
  </script>
</body>

</html>
