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
    </div>
  </div>

  <div class="container layout">
    <nav class="toc" aria-label="Contents">
      <p class="toc-title">Contents</p>
      <ol>
        <li><a href="#information-collection">1. Information Collection</a></li>
        <li><a href="#user-accounts">2. User Accounts</a></li>
        <li><a href="#location-data">3. Location Data</a></li>
        <li><a href="#advertising-and-analytics">4. Advertising and Analytics</a></li>
        <li><a href="#data-sharing">5. Data Sharing</a></li>
        <li><a href="#security">6. Security</a></li>
        <li><a href="#changes">7. Changes to This Policy</a></li>
        <li><a href="#contact">8. Contact Us</a></li>
      </ol>
    </nav>

    <article>
      <details class="toc-mobile">
        <summary>Contents</summary>
        <ol>
          <li><a href="#information-collection">Information Collection</a></li>
          <li><a href="#user-accounts">User Accounts</a></li>
          <li><a href="#location-data">Location Data</a></li>
          <li><a href="#advertising-and-analytics">Advertising and Analytics</a></li>
          <li><a href="#data-sharing">Data Sharing</a></li>
          <li><a href="#security">Security</a></li>
          <li><a href="#changes">Changes to This Policy</a></li>
          <li><a href="#contact">Contact Us</a></li>
        </ol>
      </details>

      <div class="lead">
        <p>Welcome to the Maru Mehsana mobile application (“App”), operated by <strong>Krishiv Technologies</strong>.</p>
        <p>This Privacy Policy explains how we handle your information when you use the App.</p>
      </div>

      <section id="information-collection">
        <h2>1. Information Collection</h2>
        <p>As of now, the Maru Mehsana App does <strong>not collect any personal information</strong> from users. You can browse business listings and tourist places freely without sharing any personal data.</p>
      </section>

      <section id="user-accounts">
        <h2>2. User Accounts</h2>
        <p>The App does not require you to sign up or log in at this time. In the future, we may introduce user accounts to enhance your experience. If so, we will update this Privacy Policy accordingly.</p>
      </section>

      <section id="location-data">
        <h2>3. Location Data</h2>
        <p>We do not collect or track your location at this time.</p>
      </section>

      <section id="advertising-and-analytics">
        <h2>4. Advertising and Analytics</h2>
        <p>We currently do not use any advertising or analytics tools. If this changes in the future, we will provide clear information and options to manage your preferences.</p>
      </section>

      <section id="data-sharing">
        <h2>5. Data Sharing</h2>
        <p>Since no data is collected, no data is shared with any third parties.</p>
      </section>

      <section id="security">
        <h2>6. Security</h2>
        <p>We are committed to keeping your data safe. In the future, if any personal information is collected, we will implement appropriate security practices.</p>
      </section>

      <section id="changes">
        <h2>7. Changes to This Policy</h2>
        <p>This policy may be updated when new features (like user accounts or analytics) are added. We recommend checking back periodically for any updates.</p>
      </section>

      <section id="contact">
        <h2>8. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at:</p>
        <p><a href="mailto:kvkdgt12345@gmail.com">kvkdgt12345@gmail.com</a></p>
      </section>
    </article>
  </div>

  <footer class="foot">
    <div class="container">© {{ date('Y') }} Maru Mehsana</div>
  </footer>
</body>
</html>
