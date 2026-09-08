<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/legal-layout.inc.php';

pbj_legal_render_start(
    'Privacy Policy',
    'How we handle information when you use ilovepbj ops — simply, and with respect.'
);
?>
            <h2>Who we are</h2>
            <p>
                <strong>ilovepbj ops</strong> is a restaurant operations app at
                <a href="https://ilovepbj.shop">https://ilovepbj.shop</a>
                (“we,” “us,” “the service”). This policy explains what we collect, why, and how you can reach us.
            </p>

            <h2>What we collect</h2>
            <ul>
                <li><strong>Account details</strong> — name, username, email, password (stored as a secure hash), role, and access status.</li>
                <li><strong>Restaurant / house data</strong> — content you and your team put into the app (checklists, notes, rosters, inventory notes, messages, settings, and similar ops information).</li>
                <li><strong>Billing metadata</strong> — plan choice, payment status, and Stripe customer/subscription identifiers. <strong>We do not store full card numbers</strong>; card payments are handled by Stripe.</li>
                <li><strong>Technical data</strong> — session cookies needed to keep you logged in, and basic server logs (for example IP address, browser type, and request times) for security and reliability.</li>
            </ul>

            <h2>How we use information</h2>
            <ul>
                <li>To provide and improve the service (login, hubs, messaging, permissions, themes).</li>
                <li>To process subscriptions and keep access in sync with payment status.</li>
                <li>To support you (password help, account questions, product ideas you email us).</li>
                <li>To protect the service (fraud, abuse, and security monitoring).</li>
            </ul>

            <h2>Payments (Stripe)</h2>
            <p>
                Paid plans are billed through <strong>Stripe</strong>. Stripe’s privacy practices apply to payment data they process.
                We receive confirmation of successful payment and limited billing metadata so we can unlock your account and plan features.
            </p>

            <h2>Sharing</h2>
            <p>We do <strong>not sell</strong> your personal information. We share data only as needed to run the service, for example:</p>
            <ul>
                <li><strong>Stripe</strong> — payments and subscriptions.</li>
                <li><strong>Hosting / infrastructure</strong> — servers that store the app and database.</li>
                <li><strong>Legal requirements</strong> — if required by law or to protect rights and safety.</li>
            </ul>
            <p>
                People you invite into your restaurant “house” can see content shared inside that house according to roles and permissions you configure.
            </p>

            <h2>Cookies &amp; sessions</h2>
            <p>
                We use essential cookies/sessions so you stay signed in and preferences (such as theme) can work.
                We do not run third-party advertising trackers on the core app for ad targeting.
            </p>

            <h2>Retention</h2>
            <p>
                We keep account and house data while your account is active and as needed for billing, security, and legal obligations.
                If an account is permanently deleted (for example by a platform admin so you can re-register), associated login data is removed from our active database, subject to backups that expire on a normal backup schedule.
            </p>

            <h2>Your choices</h2>
            <ul>
                <li>Update profile details where the app allows.</li>
                <li>Request access, correction, or deletion of your account by emailing us.</li>
                <li>Cancel a paid subscription per our <a href="/refunds">Cancel &amp; refunds</a> policy.</li>
            </ul>

            <h2>Security</h2>
            <p>
                We use reasonable technical and organizational measures (including HTTPS, hashed passwords, and access controls).
                No method of transmission or storage is 100% secure; please use a strong unique password.
            </p>

            <h2>Children</h2>
            <p>
                The service is intended for business use by adults working in restaurants. It is not directed at children under 13.
            </p>

            <h2>Changes</h2>
            <p>
                We may update this policy from time to time. We’ll post the new effective date on this page.
                Continued use of the service after changes means you accept the updated policy.
            </p>

            <h2>Contact</h2>
            <p>
                Privacy questions:
                <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
            </p>
<?php
pbj_legal_render_end();
