@extends('layouts.site')

@section('title', 'Privacy policy - Lens for Laravel')
@section('description', 'How the Lens for Laravel website handles personal data, Google Analytics, cookies, and your privacy choices.')
@section('skip-label', 'privacy policy')

@section('body')
    <main id="main-content" class="privacy-page section-pad" tabindex="-1">
        <nav class="docs-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Lens</a><span aria-hidden="true">/</span>
            <span aria-current="page">Privacy policy</span>
        </nav>

        <article class="docs-prose" aria-labelledby="privacy-title">
            <header>
                <p class="eyebrow">Privacy &amp; cookies</p>
                <h1 id="privacy-title">Privacy policy</h1>
                <p>This policy explains how personal data is processed when you visit the Lens for Laravel website,
                    why it is used, and how you can control optional analytics.</p>
                <p class="micro">Last updated: <time datetime="2026-10-20">20 October 2026</time></p>
            </header>

            <nav class="privacy-contents" aria-labelledby="privacy-contents-title">
                <h2 id="privacy-contents-title">On this page</h2>
                <ol>
                    <li><a href="#controller">Data controller and contact</a></li>
                    <li><a href="#data-and-purposes">Data, purposes, and legal bases</a></li>
                    <li><a href="#analytics">Google Analytics</a></li>
                    <li><a href="#cookies">Cookies and browser storage</a></li>
                    <li><a href="#privacy-choices">Your cookie choices</a></li>
                    <li><a href="#providers">Service providers</a></li>
                    <li><a href="#international-transfers">International transfers</a></li>
                    <li><a href="#retention">Data retention</a></li>
                    <li><a href="#your-rights">Your rights</a></li>
                    <li><a href="#policy-changes">Changes to this policy</a></li>
                </ol>
            </nav>

            <section aria-labelledby="controller">
                <h2 id="controller" tabindex="-1">1. Data controller and contact</h2>
                <p>The controller of personal data processed through this website is Jakub Lipiński, based in Poland.
                    For privacy questions or to exercise your rights, email
                    <a href="mailto:kontakt@lipinskijakub.pl">kontakt@lipinskijakub.pl</a>.</p>
                <p>This policy covers the website. It does not describe data processing in applications where you
                    install the Lens for Laravel package.</p>
            </section>

            <section aria-labelledby="data-and-purposes">
                <h2 id="data-and-purposes" tabindex="-1">2. Data, purposes, and legal bases</h2>
                <ul>
                    <li><strong>Website delivery and security.</strong> Technical request data may include your IP
                        address, requested URL, request time, browser information, referrer, and security events.
                        It is processed to deliver pages, diagnose problems, and prevent abuse. The legal basis is
                        the controller's legitimate interest in operating a reliable and secure website
                        (Article 6(1)(f) GDPR).</li>
                    <li><strong>Audience measurement.</strong> With your consent, Google Analytics measures visits,
                        traffic sources, and interactions to understand usage and improve the website.
                        The legal basis is consent (Article 6(1)(a) GDPR).</li>
                    <li><strong>Preferences.</strong> Browser storage remembers your cookie decision and selected
                        colour theme so that the website can respect your choices. The legal basis for processing
                        associated personal data is the legitimate interest in applying those preferences
                        (Article 6(1)(f) GDPR).</li>
                    <li><strong>Email correspondence.</strong> If you contact the controller, your email address and
                        message are used to handle your enquiry or privacy request. The legal basis is the legitimate
                        interest in responding to correspondence (Article 6(1)(f) GDPR) or compliance with a legal
                        obligation for applicable privacy requests (Article 6(1)(c) GDPR).</li>
                </ul>
                <p>The website does not provide user accounts, a newsletter, payment processing, or data collection
                    forms. Analytics consent is voluntary. Refusing it does not restrict access to the website.
                    Technical request data is necessary to serve and protect the site; if it cannot be processed,
                    access may not be possible.</p>
            </section>

            <section aria-labelledby="analytics">
                <h2 id="analytics" tabindex="-1">3. Google Analytics</h2>
                <p>The website uses Google Analytics 4 (GA4), provided by Google, to analyse page visits, referral
                    sources, device and browser information, approximate location, and interactions. Analytics data
                    may include cookie identifiers that distinguish browsers and sessions.</p>
                <p>Google Analytics is loaded only after you allow the Analytics category. Google Signals is disabled
                    and the property is not linked to Google Ads. Analytics is used to improve this website,
                    rather than to run advertising campaigns.</p>
                <p>For more information, read
                    <a href="https://policies.google.com/privacy">Google's privacy policy</a> and
                    <a href="https://policies.google.com/technologies/partner-sites">how Google uses information from sites that use its services</a>.</p>
            </section>

            <section aria-labelledby="cookies">
                <h2 id="cookies" tabindex="-1">4. Cookies and browser storage</h2>
                <ul>
                    <li><strong>Consent preferences:</strong> the <code>{{ config('consent.cookie.name') }}</code>
                        cookie remembers acceptance or refusal for {{ config('consent.retention_days') }} days
                        from your decision.</li>
                    <li><strong>Theme preference:</strong> <code>lens-theme</code> in local storage remembers your
                        chosen light or dark appearance until you change it or clear the site's browser storage.</li>
                    <li><strong>Session and security cookies:</strong> the application may set session and CSRF
                        protection cookies. The configured session inactivity period is
                        {{ config('session.lifetime') }} minutes. These cookies support the site's technical operation.</li>
                    <li><strong>Cloudflare security cookies:</strong> depending on the protection applied to your
                        request, Cloudflare may set cookies such as <code>cf_clearance</code> or <code>__cf_bm</code>
                        to handle security challenges and bot detection. Their purposes and lifetimes are described
                        in <a href="https://developers.cloudflare.com/fundamentals/reference/policies-compliances/cloudflare-cookies/">Cloudflare's cookie documentation</a>.</li>
                    <li><strong>Optional analytics cookies:</strong> after consent, GA4 may set <code>_ga</code> and
                        <code>_ga_&lt;container-id&gt;</code> to distinguish visitors and sessions. Google's default
                        expiry is two years and may be renewed on subsequent visits. Browser limits can shorten this
                        period. See <a href="https://support.google.com/analytics/answer/11397207?hl=en">Google's GA4 cookie documentation</a>.</li>
                </ul>
            </section>

            <section aria-labelledby="privacy-choices">
                <h2 id="privacy-choices" tabindex="-1">5. Your cookie choices</h2>
                <p>You can accept or reject optional analytics in the consent notice. You can also reopen preferences
                    at any time using the button below or the cookie preferences control on the website.</p>
                <p><button type="button" class="button" data-consent-open aria-haspopup="dialog"
                        aria-controls="consent-preferences">Manage cookie preferences</button></p>
                <p>To withdraw analytics consent, turn off Analytics and save your preferences, or reject optional
                    cookies. Withdrawal stops future optional analytics collection; the page may reload to stop
                    an already loaded tracker. It does not affect the lawfulness of processing before withdrawal.</p>
                <p>You can also delete cookies and local storage through your browser. Deleting your preference
                    cookie means you may be asked to choose again. Blocking technical or security cookies may affect
                    the website's operation.</p>
            </section>

            <section aria-labelledby="providers">
                <h2 id="providers" tabindex="-1">6. Service providers</h2>
                <p>Data is handled by providers where needed to deliver the following services:</p>
                <ul>
                    <li><strong>Hostinger:</strong> infrastructure for the virtual private server hosting this website.
                        See <a href="https://www.hostinger.com/legal/privacy-policy">Hostinger's privacy policy</a>.</li>
                    <li><strong>Cloudflare:</strong> traffic delivery and protection against malicious requests and
                        automated abuse. Cloudflare processes connection and request data as traffic passes through
                        its network. See <a href="https://www.cloudflare.com/privacypolicy/">Cloudflare's privacy policy</a>.</li>
                    <li><strong>Google:</strong> optional website analytics, activated after your consent, as described
                        above.</li>
                </ul>
                <p>Data may also be disclosed where required by law. Links to third-party websites, such as GitHub
                    or the support page, lead to services with their own privacy policies.</p>
            </section>

            <section aria-labelledby="international-transfers">
                <h2 id="international-transfers" tabindex="-1">7. International transfers</h2>
                <p>The providers above operate internationally. Personal data may be processed outside the European
                    Economic Area, including in the United States. Applicable transfer safeguards are described in
                    their data processing terms and may include an applicable European Commission adequacy decision
                    or Standard Contractual Clauses.</p>
                <p>Details are available in
                    <a href="https://business.safety.google/adsdatatransfers/">Google's international data transfer information</a>,
                    <a href="https://www.cloudflare.com/cloudflare-customer-dpa/">Cloudflare's Data Processing Addendum</a>, and
                    <a href="https://www.hostinger.com/legal/dpa">Hostinger's Data Processing Addendum</a>.
                    You can contact the controller for information about safeguards relevant to your data and how
                    to obtain a copy.</p>
            </section>

            <section aria-labelledby="retention">
                <h2 id="retention" tabindex="-1">8. Data retention</h2>
                <ul>
                    <li><strong>Consent decisions:</strong> stored in your browser for
                        {{ config('consent.retention_days') }} days from the decision.</li>
                    <li><strong>Analytics:</strong> GA4 uses the default two-month retention period for user-level
                        and event-level data subject to its retention setting. This setting does not limit standard
                        aggregated reports. New activity may reset the retention period for a user identifier.
                        Cookie expiry is separate from data retention. See
                        <a href="https://support.google.com/analytics/answer/7667196?hl=en">Google's data retention documentation</a>.</li>
                    <li><strong>Technical and security logs:</strong> retained for the period needed to diagnose
                        faults, investigate security incidents, and protect the service. The relevant period depends
                        on log rotation, the incident being investigated, and applicable legal obligations.</li>
                    <li><strong>Correspondence:</strong> retained for the time needed to resolve the enquiry and,
                        where necessary, meet legal obligations or establish, exercise, or defend legal claims.</li>
                </ul>
            </section>

            <section aria-labelledby="your-rights">
                <h2 id="your-rights" tabindex="-1">9. Your rights</h2>
                <p>Subject to the conditions of the GDPR, you may request access to your personal data, correction,
                    deletion, or restriction of processing. You may also have a right to data portability where
                    processing is automated and based on consent or a contract.</p>
                <p>You may object to processing based on legitimate interests on grounds relating to your particular
                    situation. Where processing relies on consent, you can withdraw it at any time.</p>
                <p>Send requests to <a href="mailto:kontakt@lipinskijakub.pl">kontakt@lipinskijakub.pl</a>.
                    The controller may request information necessary to verify your identity and locate the relevant
                    data. Requests are normally answered within one month; where the GDPR permits an extension,
                    you will be informed of the reasons.</p>
                <p>You may lodge a complaint with a supervisory authority, including the
                    <a href="https://uodo.gov.pl/en">President of the Personal Data Protection Office in Poland (UODO)</a>,
                    or the authority in the EU country where you live or work.</p>
                <p>The controller does not use your data to make solely automated decisions that produce legal or
                    similarly significant effects on you.</p>
            </section>

            <section aria-labelledby="policy-changes">
                <h2 id="policy-changes" tabindex="-1">10. Changes to this policy</h2>
                <p>This policy may be updated when the website's services, data processing, or legal requirements
                    change. The date at the top identifies the current version. Where a change requires renewed
                    consent, you will be asked to make a new choice.</p>
            </section>
        </article>
    </main>
@endsection
