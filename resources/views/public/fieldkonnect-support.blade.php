<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FieldKonnect Support</title>
    <style>
        :root {
            --bg: #050e24;
            --panel: rgba(9, 20, 48, .68);
            --panel-strong: rgba(12, 30, 68, .82);
            --border: rgba(105, 145, 220, .24);
            --border-strong: rgba(34, 211, 238, .36);
            --cyan: #22d3ee;
            --blue: #3b82f6;
            --green: #12d18e;
            --text: #e8f0ff;
            --muted: #8fa1cc;
            --soft: #cbd8f6;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(90% 70% at 18% 10%, rgba(34, 211, 238, .16), transparent 54%),
                radial-gradient(80% 70% at 92% 88%, rgba(59, 130, 246, .18), transparent 60%),
                linear-gradient(180deg, #071735 0%, #050e24 58%, #030917 100%);
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(125, 160, 230, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(125, 160, 230, .04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,.8), transparent 85%);
        }

        a { color: inherit; text-decoration: none; }

        .support-shell {
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            padding: 28px 0 46px;
            position: relative;
        }

        .support-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 28px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            width: 146px;
            height: auto;
            display: block;
            filter: drop-shadow(0 10px 22px rgba(34, 211, 238, .14));
        }

        .brand span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2.2px;
            text-transform: uppercase;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            height: 40px;
            padding: 0 16px;
            border: 1px solid var(--border-strong);
            border-radius: 999px;
            background: rgba(34, 211, 238, .08);
            color: var(--cyan);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .status-pill i {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--cyan);
            box-shadow: 0 0 14px var(--cyan);
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
            gap: 20px;
            align-items: stretch;
        }

        .glass {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--panel);
            backdrop-filter: blur(18px);
            box-shadow: 0 36px 90px -48px rgba(0, 0, 0, .95);
            overflow: hidden;
        }

        .hero-copy {
            position: relative;
            padding: 48px;
            min-height: 474px;
        }

        .hero-copy::after {
            content: "";
            position: absolute;
            inset: 0;
            width: 160px;
            background: linear-gradient(90deg, transparent, rgba(34, 211, 238, .10), transparent);
            animation: sweep 7s linear infinite;
            pointer-events: none;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
            color: var(--cyan);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 2.6px;
            text-transform: uppercase;
        }

        .kicker svg { width: 18px; height: 18px; }

        h1 {
            max-width: 710px;
            margin: 0;
            color: #fff;
            font-size: clamp(38px, 5.2vw, 76px);
            line-height: .98;
            letter-spacing: -1.7px;
            font-weight: 900;
        }

        .lead {
            max-width: 650px;
            margin: 22px 0 0;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.65;
        }

        .cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 20px;
            border-radius: 14px;
            border: 1px solid var(--border);
            font-size: 13px;
            font-weight: 800;
            transition: .2s ease;
        }

        .btn svg { width: 18px; height: 18px; }

        .btn-primary {
            color: #061125;
            border: 0;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            box-shadow: 0 18px 36px -22px var(--cyan);
        }

        .btn-ghost {
            color: var(--soft);
            background: rgba(8, 20, 50, .62);
        }

        .btn:hover {
            transform: translateY(-2px);
            border-color: var(--border-strong);
            box-shadow: 0 22px 50px -32px rgba(34, 211, 238, .8);
        }

        .contact-card {
            padding: 24px;
            display: grid;
            gap: 14px;
        }

        .contact-card h2 {
            margin: 0 0 6px;
            color: #fff;
            font-size: 22px;
            line-height: 1.2;
            font-weight: 850;
        }

        .contact-card p {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .contact-link {
            display: grid;
            grid-template-columns: 46px 1fr;
            gap: 13px;
            align-items: center;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 17px;
            background: rgba(7, 19, 46, .72);
            transition: .2s ease;
        }

        .contact-link:hover {
            border-color: var(--border-strong);
            background: rgba(9, 28, 64, .82);
        }

        .icon-box {
            width: 46px;
            height: 46px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            color: var(--cyan);
            border: 1px solid rgba(34, 211, 238, .34);
            background: rgba(34, 211, 238, .08);
        }

        .icon-box svg { width: 22px; height: 22px; }

        .contact-link small {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }

        .contact-link b {
            display: block;
            color: #fff;
            font-size: 16px;
            word-break: break-word;
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .mini-card {
            padding: 20px;
        }

        .mini-card b {
            display: block;
            margin: 12px 0 6px;
            color: #fff;
            font-size: 16px;
        }

        .mini-card span {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .footer-note {
            margin-top: 18px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @keyframes sweep {
            from { transform: translateX(-170%); }
            to { transform: translateX(760%); }
        }

        @media (max-width: 900px) {
            .hero { grid-template-columns: 1fr; }
            .hero-copy { min-height: auto; padding: 34px 24px; }
            .support-grid { grid-template-columns: 1fr; }
            .support-nav { align-items: flex-start; flex-direction: column; }
        }

        @media (max-width: 520px) {
            .support-shell { width: min(100% - 24px, 1180px); padding-top: 18px; }
            .brand img { width: 128px; }
            .status-pill { height: 36px; font-size: 10px; }
            .cta-row .btn { width: 100%; }
            h1 { font-size: 38px; }
        }
    </style>
</head>
<body>
    <main class="support-shell">
        <nav class="support-nav" aria-label="Support header">
            <a class="brand" href="{{ url('/') }}">
                <img src="{{ asset('assets/img/fieldkonnect_login_logo.png') }}" alt="FieldKonnect">
                <span>Powered by Greymetre</span>
            </a>
            <span class="status-pill"><i></i>Support Desk</span>
        </nav>

        <section class="hero">
            <div class="glass hero-copy">
                <span class="kicker">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"></path><path d="M8 9h8M8 13h5"></path></svg>
                    FieldKonnect Support
                </span>
                <h1>We are here to keep your field operations moving.</h1>
                <p class="lead">Get help for login issues, mobile app access, field tracking, reports, imports, dashboards, orders, customers, and module workflows. Reach our support team directly and we will guide you to the right resolution.</p>

                <div class="cta-row">
                    <a class="btn btn-primary" href="tel:+919713113280">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.7.6 2.5a2 2 0 0 1-.5 2.1L8 9.5a16 16 0 0 0 6.5 6.5l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.6.5 2.5.6A2 2 0 0 1 22 16.9Z"></path></svg>
                        Call 9713113280
                    </a>
                    <a class="btn btn-ghost" href="mailto:info@greymetre.io?subject=FieldKonnect%20Support%20Request">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                        Email Support
                    </a>
                </div>
            </div>

            <aside class="glass contact-card" aria-label="Support contact details">
                <div>
                    <h2>Contact Support</h2>
                    <p>For faster support, include your company name, user mobile number, module name, and screenshot or error message.</p>
                </div>

                <a class="contact-link" href="tel:+919713113280">
                    <span class="icon-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.7.6 2.5a2 2 0 0 1-.5 2.1L8 9.5a16 16 0 0 0 6.5 6.5l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.6.5 2.5.6A2 2 0 0 1 22 16.9Z"></path></svg>
                    </span>
                    <span><small>Phone</small><b>9713113280</b></span>
                </a>

                <a class="contact-link" href="mailto:info@greymetre.io?subject=FieldKonnect%20Support%20Request">
                    <span class="icon-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                    </span>
                    <span><small>Email</small><b>info@greymetre.io</b></span>
                </a>

                <a class="contact-link" href="https://wa.me/919713113280?text=Hello%20FieldKonnect%20Support%2C%20I%20need%20help." target="_blank" rel="noopener">
                    <span class="icon-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.4-4.7A8.5 8.5 0 1 1 20.5 11.7Z"></path><path d="M8.8 8.6c.2 3.2 3.2 5.8 6.1 6.5l1.3-1.3"></path></svg>
                    </span>
                    <span><small>WhatsApp</small><b>Start Chat</b></span>
                </a>
            </aside>
        </section>

        <section class="support-grid" aria-label="Support categories">
            <div class="glass mini-card">
                <span class="icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"></path></svg>
                </span>
                <b>Application Help</b>
                <span>Support for login, permissions, master data, imports, exports, and dashboard access.</span>
            </div>
            <div class="glass mini-card">
                <span class="icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="7" y="2" width="10" height="20" rx="2"></rect><path d="M11 18h2"></path></svg>
                </span>
                <b>Mobile App Support</b>
                <span>Help with attendance, live tracking, check-in, beat plans, customer visits, and sync issues.</span>
            </div>
            <div class="glass mini-card">
                <span class="icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V5"></path><path d="M4 19h16"></path><path d="M8 16v-5"></path><path d="M12 16V8"></path><path d="M16 16v-3"></path></svg>
                </span>
                <b>Reports & Data</b>
                <span>Assistance with report mismatch, filters, data visibility, Excel downloads, and summaries.</span>
            </div>
        </section>

        <p class="footer-note">FieldKonnect Support by Greymetre. This page is public and does not require login.</p>
    </main>
</body>
</html>
