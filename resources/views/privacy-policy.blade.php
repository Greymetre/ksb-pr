<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - FieldKonnect</title>
    <style>
        :root {
            --bg: #050e24;
            --panel: rgba(9, 20, 48, .68);
            --panel-strong: rgba(12, 30, 68, .86);
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

        html { scroll-behavior: smooth; }

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
            mask-image: linear-gradient(180deg, rgba(0,0,0,.8), transparent 88%);
        }

        a { color: inherit; text-decoration: none; }

        .policy-shell {
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            padding: 28px 0 46px;
            position: relative;
        }

        .policy-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 26px;
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

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-pill,
        .nav-link {
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

        .nav-link {
            color: var(--soft);
            border-color: var(--border);
            background: rgba(8, 20, 50, .62);
            letter-spacing: 1px;
            text-transform: none;
        }

        .status-pill i {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--cyan);
            box-shadow: 0 0 14px var(--cyan);
        }

        .hero,
        .policy-card,
        .toc-card {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--panel);
            backdrop-filter: blur(18px);
            box-shadow: 0 36px 90px -48px rgba(0, 0, 0, .95);
            overflow: hidden;
        }

        .hero {
            position: relative;
            padding: 44px 46px;
            margin-bottom: 18px;
        }

        .hero::after {
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
            max-width: 760px;
            margin: 0;
            color: #fff;
            font-size: clamp(38px, 5vw, 68px);
            line-height: 1;
            letter-spacing: -1.5px;
            font-weight: 900;
        }

        .lead {
            max-width: 760px;
            margin: 22px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 26px;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 13px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(8, 20, 50, .62);
            color: var(--soft);
            font-size: 12px;
            font-weight: 700;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .toc-card {
            position: sticky;
            top: 18px;
            padding: 18px;
        }

        .toc-card b {
            display: block;
            margin-bottom: 12px;
            color: #fff;
            font-size: 15px;
        }

        .toc-card a {
            display: block;
            padding: 9px 10px;
            border-radius: 11px;
            color: var(--muted);
            font-size: 12.5px;
            line-height: 1.35;
            transition: .18s ease;
        }

        .toc-card a:hover {
            color: var(--cyan);
            background: rgba(34, 211, 238, .08);
        }

        .policy-card {
            padding: 28px;
        }

        .policy-section {
            padding: 22px 0;
            border-bottom: 1px solid rgba(105, 145, 220, .16);
        }

        .policy-section:first-child { padding-top: 0; }
        .policy-section:last-child { border-bottom: 0; padding-bottom: 0; }

        h2 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 22px;
            line-height: 1.2;
            font-weight: 850;
            letter-spacing: -.2px;
        }

        h3 {
            margin: 18px 0 9px;
            color: var(--soft);
            font-size: 15px;
            font-weight: 800;
        }

        p,
        li {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.75;
        }

        p { margin: 0 0 12px; }
        p:last-child { margin-bottom: 0; }

        strong { color: var(--text); }

        ul {
            margin: 10px 0 0;
            padding-left: 18px;
        }

        li { margin-bottom: 7px; }

        .contact-panel {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .contact-link {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 10px;
            min-height: 42px;
            padding: 0 15px;
            border-radius: 13px;
            border: 1px solid var(--border-strong);
            background: rgba(34, 211, 238, .08);
            color: var(--cyan);
            font-size: 13px;
            font-weight: 800;
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

        @media (max-width: 960px) {
            .content-grid { grid-template-columns: 1fr; }
            .toc-card { position: relative; top: auto; }
        }

        @media (max-width: 640px) {
            .policy-shell { width: min(100% - 24px, 1180px); padding-top: 18px; }
            .policy-nav { align-items: flex-start; flex-direction: column; }
            .brand img { width: 128px; }
            .hero { padding: 32px 22px; }
            .policy-card { padding: 22px; }
            h1 { font-size: 38px; }
            .nav-actions { width: 100%; }
            .status-pill, .nav-link { height: 36px; font-size: 10px; }
        }
    </style>
</head>
<body>
    <main class="policy-shell">
        <nav class="policy-nav" aria-label="Privacy header">
            <a class="brand" href="{{ url('/') }}">
                <img src="{{ asset('assets/img/fieldkonnect_login_logo.png') }}" alt="FieldKonnect">
                <span>Powered by Greymetre</span>
            </a>
            <div class="nav-actions">
                <a class="nav-link" href="{{ url('fieldkonnect-support') }}">Support</a>
                <span class="status-pill"><i></i>Privacy Policy</span>
            </div>
        </nav>

        <section class="hero">
            <span class="kicker">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path><path d="m9 12 2 2 4-5"></path></svg>
                FieldKonnect Data Protection
            </span>
            <h1>Privacy Policy</h1>
            <p class="lead">This Privacy Policy explains how Greymetre Consultants Pvt. Ltd. collects, uses, stores, and protects information through the FieldKonnect mobile application.</p>
            <div class="meta-row">
                <span class="meta-chip">Last Updated: April 30, 2026</span>
                <span class="meta-chip">Internal Workforce Application</span>
                <span class="meta-chip">India Data Storage</span>
            </div>
        </section>

        <div class="content-grid">
            <aside class="toc-card" aria-label="Privacy policy sections">
                <b>Policy Sections</b>
                <a href="#about">1. About the App</a>
                <a href="#information">2. Information We Collect</a>
                <a href="#usage">3. How We Use Your Information</a>
                <a href="#sharing">4. Data Sharing</a>
                <a href="#security">5. Data Storage and Security</a>
                <a href="#retention">6. Data Retention</a>
                <a href="#rights">7. Your Rights</a>
                <a href="#children">8. Children's Privacy</a>
                <a href="#changes">9. Changes to This Privacy Policy</a>
                <a href="#contact">10. Contact Us</a>
            </aside>

            <article class="policy-card">
                <section class="policy-section">
                    <p>This Privacy Policy explains how <strong>Greymetre Consultants Pvt. Ltd.</strong> ("Greymetre", "we", "us", or "our") collects, uses, and protects personal information of its employees through the <strong>FieldKonnect</strong> mobile application ("App").</p>
                </section>

                <section class="policy-section" id="about">
                    <h2>1. About the App</h2>
                    <p>FieldKonnect is an internal mobile application developed exclusively for Greymetre employees. It is used for field workforce management and allows employees to:</p>
                    <ul>
                        <li>Perform daily Punch In and Punch Out attendance.</li>
                        <li>Visit customer shops and perform check-in at customer locations.</li>
                        <li>Create and update customer or shop profiles.</li>
                        <li>Record visit details, notes, and other relevant business information.</li>
                        <li>Track field activities for operational efficiency.</li>
                    </ul>
                </section>

                <section class="policy-section" id="information">
                    <h2>2. Information We Collect</h2>
                    <p>We collect the following information when you use the App:</p>

                    <h3>A. Personal & Account Information</h3>
                    <ul>
                        <li>Name, Employee ID, phone number, and email address.</li>
                        <li>Login credentials such as username and password.</li>
                    </ul>

                    <h3>B. Location Data</h3>
                    <ul>
                        <li><strong>Punch In / Punch Out Location:</strong> We capture your current GPS location only when you Punch In or Punch Out.</li>
                        <li><strong>Customer Visit Location:</strong> We capture your current GPS location only when you check in at a customer shop.</li>
                        <li>Location is not collected continuously or in the background. It is captured only at the specific moment when you perform Punch In, Punch Out, or Customer Check-in.</li>
                    </ul>

                    <h3>C. Usage & Activity Data</h3>
                    <ul>
                        <li>Punch In and Punch Out timestamps.</li>
                        <li>Customer check-in timestamps and visit records.</li>
                        <li>Details of customer shops created or updated by you, including shop name, address, contact details, photos, remarks, and related business information.</li>
                        <li>App usage logs and activity history.</li>
                    </ul>

                    <h3>D. Device Information</h3>
                    <ul>
                        <li>Device model, operating system version, and unique device identifiers.</li>
                    </ul>
                </section>

                <section class="policy-section" id="usage">
                    <h2>3. How We Use Your Information</h2>
                    <p>We use the collected data only for legitimate internal business purposes, including:</p>
                    <ul>
                        <li>Monitoring daily employee attendance.</li>
                        <li>Verifying that employees have physically visited assigned customer shops.</li>
                        <li>Maintaining accurate records of field visits and customer interactions.</li>
                        <li>Creating and managing customer databases for business operations.</li>
                        <li>Generating internal reports for management and improving workforce productivity.</li>
                        <li>Ensuring compliance with company policies.</li>
                    </ul>
                </section>

                <section class="policy-section" id="sharing">
                    <h2>4. Data Sharing</h2>
                    <p>We do not sell, rent, or trade your personal data. Your data is used strictly within Greymetre Consultants Pvt. Ltd. for internal purposes only.</p>
                    <p>We may share your data with third parties only in the following limited cases:</p>
                    <ul>
                        <li>With trusted service providers who help us with app hosting, cloud storage, or technical maintenance under strict confidentiality agreements.</li>
                        <li>When required by applicable law, court order, or government authority.</li>
                    </ul>
                </section>

                <section class="policy-section" id="security">
                    <h2>5. Data Storage and Security</h2>
                    <p>Your data is stored securely on servers located in India. We implement reasonable technical and organizational security measures to protect your information from unauthorized access, loss, or misuse.</p>
                </section>

                <section class="policy-section" id="retention">
                    <h2>6. Data Retention</h2>
                    <p>We retain your personal information and location data only for as long as necessary to fulfill the purposes outlined in this policy or as required by law. You may request deletion of your data by contacting us.</p>
                </section>

                <section class="policy-section" id="rights">
                    <h2>7. Your Rights</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access the personal data we hold about you.</li>
                        <li>Request correction of any inaccurate or incomplete data.</li>
                        <li>Request deletion of your data, subject to legal and business requirements.</li>
                    </ul>
                    <p>To exercise any of these rights, please contact us using the details provided below.</p>
                </section>

                <section class="policy-section" id="children">
                    <h2>8. Children's Privacy</h2>
                    <p>The FieldKonnect App is intended only for adult employees 18 years and above of Greymetre Consultants Pvt. Ltd.</p>
                </section>

                <section class="policy-section" id="changes">
                    <h2>9. Changes to This Privacy Policy</h2>
                    <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated "Last Updated" date. We encourage you to review this policy periodically.</p>
                </section>

                <section class="policy-section" id="contact">
                    <h2>10. Contact Us</h2>
                    <p>If you have any questions or concerns regarding this Privacy Policy or how your data is handled, please contact us at:</p>
                    <p><strong>Greymetre Consultants Pvt. Ltd.</strong></p>
                    <div class="contact-panel">
                        <a class="contact-link" href="mailto:info@greymetre.io?subject=FieldKonnect%20Privacy%20Policy">info@greymetre.io</a>
                        <a class="contact-link" href="{{ url('fieldkonnect-support') }}">Open FieldKonnect Support</a>
                    </div>
                </section>

                <section class="policy-section">
                    <p>By using the FieldKonnect application, you consent to the collection and use of your information as described in this Privacy Policy.</p>
                </section>
            </article>
        </div>

        <p class="footer-note">FieldKonnect Privacy Policy by Greymetre. This page is public and does not require login.</p>
    </main>
</body>
</html>
