<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Privacy Policy - Maru Mehsana</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/site/logo-128.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @php $gaPage = ['site_language' => 'en']; @endphp
  @include('partials.analytics')
  <style>
    :root {
      --ink: #0f2447;
      --text: #3b4a63;
      --muted: #64748b;
      --blue: #1769b5;
      --line: #e5ebf2;
      --soft: #f6f9fd;
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; scroll-padding-top: 76px; }
    @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
    body {
      margin: 0;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 15.5px;
      line-height: 1.7;
      color: var(--text);
      background: #fff;
      -webkit-font-smoothing: antialiased;
    }
    a { color: var(--blue); text-decoration: none; }
    a:hover { text-decoration: underline; }
    .container { max-width: 1040px; margin: 0 auto; padding: 0 24px; }

    /* Header */
    .top { position: sticky; top: 0; z-index: 10; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border-bottom: 1px solid var(--line); }
    .top-inner { height: 60px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .brand { display: inline-flex; align-items: center; gap: 9px; color: var(--ink); font-size: 1.05rem; font-weight: 600; }
    .brand:hover { text-decoration: none; }
    .brand img { width: 32px; height: 32px; }
    .brand b { font-weight: 800; color: var(--blue); }
    .home-link { font-size: 0.88rem; font-weight: 600; color: var(--muted); }
    .home-link:hover { color: var(--ink); text-decoration: none; }

    /* Page title */
    .page-head { background: var(--soft); border-bottom: 1px solid var(--line); }
    .page-head .container { padding-top: 28px; padding-bottom: 24px; }
    .crumbs { font-size: 0.82rem; color: var(--muted); }
    .crumbs a { color: var(--muted); }
    .crumbs span { margin: 0 6px; }
    h1 { margin: 6px 0 0; font-size: 1.9rem; font-weight: 800; line-height: 1.25; letter-spacing: -0.01em; color: var(--ink); }

    /* Body */
    .layout { display: grid; grid-template-columns: 200px minmax(0, 1fr); gap: 56px; padding-top: 32px; padding-bottom: 48px; }
    .toc { position: sticky; top: 84px; align-self: start; }
    .toc-title { margin: 0 0 8px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
    .toc ol { list-style: none; margin: 0; padding: 0; border-left: 1px solid var(--line); }
    .toc a { display: block; padding: 4px 0 4px 14px; margin-left: -1px; border-left: 2px solid transparent; font-size: 0.86rem; line-height: 1.45; color: var(--muted); }
    .toc a:hover { color: var(--ink); border-left-color: var(--blue); text-decoration: none; }

    article { max-width: 720px; }
    .lead p { margin: 0 0 10px; font-size: 1rem; color: var(--ink); }
    .lead { padding-bottom: 6px; }
    section { padding-top: 22px; }
    h2 { margin: 0 0 6px; font-size: 1.08rem; font-weight: 700; line-height: 1.4; color: var(--ink); }
    section p { margin: 0 0 8px; }
    section ul { margin: 0 0 10px; padding-left: 20px; }
    section li { margin: 0 0 4px; }
    code { font-size: 0.9em; padding: 1px 5px; border-radius: 4px; background: var(--soft); color: var(--ink); }
    .updated { margin: 6px 0 0; font-size: 0.85rem; color: var(--muted); }
    strong { color: var(--ink); font-weight: 700; }

    .foot { border-top: 1px solid var(--line); }
    .foot .container { padding-top: 16px; padding-bottom: 20px; font-size: 0.8rem; color: var(--muted); }

    /* Contents becomes a collapsible box on small screens */
    .toc-mobile { display: none; }
    @media (max-width: 820px) {
      .layout { grid-template-columns: minmax(0, 1fr); gap: 0; padding-top: 18px; }
      .toc { display: none; }
      .toc-mobile { display: block; margin-bottom: 8px; border: 1px solid var(--line); border-radius: 10px; }
      .toc-mobile summary { padding: 10px 14px; font-size: 0.88rem; font-weight: 700; color: var(--ink); cursor: pointer; }
      .toc-mobile ol { margin: 0; padding: 0 14px 10px 32px; font-size: 0.88rem; }
      .toc-mobile li { padding: 3px 0; }
    }
    @media (max-width: 560px) {
      body { font-size: 15px; }
      .container { padding: 0 16px; }
      .page-head .container { padding-top: 20px; padding-bottom: 18px; }
      h1 { font-size: 1.6rem; }
    }
  </style>
</head>
<body>
  <header class="top">
    <div class="container top-inner">
      <a href="{{ url('/') }}" class="brand"><img src="{{ asset('assets/site/logo-128.png') }}" width="32" height="32" alt=""><span>Maru <b>Mehsana</b></span></a>
      <a href="{{ url('/') }}" class="home-link">← Home</a>
    </div>
  </header>

  <div class="page-head">
    <div class="container">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ url('/') }}">Home</a><span>/</span>Privacy Policy</nav>
      <h1>Privacy Policy</h1>
      <p class="updated">Last updated: 14 November 2026</p>
    </div>
  </div>

  <div class="container layout">
    <nav class="toc" aria-label="Contents">
      <p class="toc-title">Contents</p>
      <ol>
        <li><a href="#information-collection">1. Information We Collect</a></li>
        <li><a href="#user-accounts">2. User Accounts</a></li>
        <li><a href="#location-data">3. Location Data</a></li>
        <li><a href="#analytics">4. Analytics and Cookies</a></li>
        <li><a href="#advertising">5. Advertising</a></li>
        <li><a href="#data-sharing">6. Data Sharing</a></li>
        <li><a href="#retention">7. Data Retention</a></li>
        <li><a href="#your-choices">8. Your Choices</a></li>
        <li><a href="#security">9. Security</a></li>
        <li><a href="#changes">10. Changes to This Policy</a></li>
        <li><a href="#contact">11. Contact Us</a></li>
      </ol>
    </nav>

    <article>
      <details class="toc-mobile">
        <summary>Contents</summary>
        <ol>
          <li><a href="#information-collection">Information We Collect</a></li>
          <li><a href="#user-accounts">User Accounts</a></li>
          <li><a href="#location-data">Location Data</a></li>
          <li><a href="#analytics">Analytics and Cookies</a></li>
          <li><a href="#advertising">Advertising</a></li>
          <li><a href="#data-sharing">Data Sharing</a></li>
          <li><a href="#retention">Data Retention</a></li>
          <li><a href="#your-choices">Your Choices</a></li>
          <li><a href="#security">Security</a></li>
          <li><a href="#changes">Changes to This Policy</a></li>
          <li><a href="#contact">Contact Us</a></li>
        </ol>
      </details>

      <div class="lead">
        <p>Welcome to Maru Mehsana, the mobile application (“App”) and the website (“Website”) operated by <strong>Krishiv Technologies</strong>.</p>
        <p>This Privacy Policy explains what information we collect when you use the App or the Website, why we collect it and the choices you have.</p>
      </div>

      <section id="information-collection">
        <h2>1. Information We Collect</h2>
        <p>You can browse business listings, tourist places, news and jobs without creating an account or telling us who you are.</p>
        <p><strong>Website usage statistics.</strong> On the Website we use Google Analytics 4 to collect anonymous usage statistics, such as which pages are visited and which buttons are clicked. See <a href="#analytics">Analytics and Cookies</a> for details. We do not send your name, phone number or e-mail address to Google Analytics.</p>
        <p><strong>Information you choose to send us.</strong> If you use the “Add your business” form, we receive the business name, owner name, mobile number and WhatsApp number you enter. We use these details only to contact you about listing your business.</p>
      </section>

      <section id="user-accounts">
        <h2>2. User Accounts</h2>
        <p>The App and the Website do not require you to sign up or log in at this time. In the future, we may introduce user accounts to enhance your experience. If so, we will update this Privacy Policy accordingly.</p>
      </section>

      <section id="location-data">
        <h2>3. Location Data</h2>
        <p>We do not collect or track your precise location. Google Analytics estimates an approximate, city-level location (for example, the city and country) from your IP address so that we can see roughly where our visitors come from. Google Analytics 4 does not log or store IP addresses.</p>
      </section>

      <section id="analytics">
        <h2>4. Analytics and Cookies</h2>
        <p>We use <strong>Google Analytics 4</strong>, a web analytics service provided by Google, to understand how the Website is used and to make it better. Google Analytics sets the following first-party cookies in your browser:</p>
        <ul>
          <li><code>_ga</code> – distinguishes one browser from another with a random identifier.</li>
          <li><code>_ga_&lt;ID&gt;</code> – keeps the state of your current visit.</li>
        </ul>
        <p>The information collected includes:</p>
        <ul>
          <li>pages you visit and how long you stay on them;</li>
          <li>an approximate, city-level location;</li>
          <li>device type, operating system, browser and screen size;</li>
          <li>how you found the Website (for example, a search engine or a shared link);</li>
          <li>actions on the page, such as button clicks (for example, “Call” or “Open in app”), searches and filter changes.</li>
        </ul>
        <p>Phone numbers and e-mail addresses are removed from search terms before they are sent, and we never send your name, phone number, e-mail address or what you type into forms. We use this information only in aggregated form, to see which pages and features are useful and to fix problems.</p>
        <p>Google processes this information on our behalf. You can read how Google uses data in the <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy Policy</a>.</p>
      </section>

      <section id="advertising">
        <h2>5. Advertising</h2>
        <p>We do not show advertising on the Website. Google Analytics advertising features (Google signals and ad personalisation) are turned off, and the information collected is not used to show you personalised ads.</p>
      </section>

      <section id="data-sharing">
        <h2>6. Data Sharing</h2>
        <p>We do not sell your information. Website usage statistics are processed by Google as our analytics provider, as described above. Details sent through the “Add your business” form are used only by our team. We may disclose information if required by law.</p>
      </section>

      <section id="retention">
        <h2>7. Data Retention</h2>
        <p>Google Analytics keeps user-level and event-level data for <strong>14 months</strong>, after which it is deleted automatically. Aggregated reports (for example, total visits per month) may be kept longer.</p>
      </section>

      <section id="your-choices">
        <h2>8. Your Choices</h2>
        <p>Google Analytics cookies are used only if you choose <strong>Accept</strong> in the cookie notice shown on your first visit. If you choose <strong>Decline</strong> or make no choice, no analytics cookies are set and Google Analytics only receives basic, cookieless visit counts. Your choice is saved for 12 months in a small cookie named <code>mm_consent</code>. You can change or withdraw your consent at any time, as easily as you gave it, using <a href="#" data-consent-open>Cookie settings</a> (also at the bottom of every page); withdrawing consent deletes the Google Analytics cookies from your browser.</p>
        <ul>
          <li>Install the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">Google Analytics Opt-out Browser Add-on</a> to stop Google Analytics on all websites.</li>
          <li>Block or delete cookies in your browser settings. The Website keeps working without them.</li>
          <li>Use your browser's private or incognito mode, which removes cookies when you close it.</li>
        </ul>
      </section>

      <section id="security">
        <h2>9. Security</h2>
        <p>We are committed to keeping your data safe and use appropriate security practices to protect the information we receive.</p>
      </section>

      <section id="changes">
        <h2>10. Changes to This Policy</h2>
        <p>This policy may be updated when we add new features (like user accounts). The “Last updated” date at the top shows when it was last changed. We recommend checking back periodically for any updates.</p>
      </section>

      <section id="contact">
        <h2>11. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy or your data, please contact us at:</p>
        <p><a href="mailto:kvkdgt12345@gmail.com">kvkdgt12345@gmail.com</a></p>
      </section>
    </article>
  </div>

  <footer class="foot">
    <div class="container">© {{ date('Y') }} Maru Mehsana</div>
  </footer>
  @include('partials.consent', ['lang' => 'en'])
</body>
</html>
