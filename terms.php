<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/legal-layout.inc.php';

pbj_legal_render_start(
    'Terms of Service',
    'The friendly ground rules for using ilovepbj ops.'
);
?>
            <h2>Agreement</h2>
            <p>
                By creating an account, logging in, or using
                <a href="https://ilovepbj.shop">ilovepbj.shop</a>
                (“the service,” “ilovepbj ops”), you agree to these Terms and our
                <a href="/privacy">Privacy Policy</a>.
                If you use the service on behalf of a restaurant or company, you represent that you have authority to bind that organization.
            </p>

            <h2>The service</h2>
            <p>
                ilovepbj ops is a web app that helps restaurant teams with day-to-day operations — for example FOH and BOH hubs,
                checklists, messaging, team tools, and related features. Features may vary by plan (Individual vs crew sizes)
                and may change as we improve the product.
            </p>
            <p>
                The service is a productivity and coordination tool. It is <strong>not</strong> legal, tax, health-department,
                HR, or food-safety certification advice. You’re responsible for complying with laws and policies that apply to your business.
            </p>

            <h2>Accounts &amp; access</h2>
            <ul>
                <li>You must provide accurate account information and keep your password confidential.</li>
                <li>You’re responsible for activity under your login.</li>
                <li>Some accounts may remain <strong>pending</strong> until payment succeeds or a platform admin approves access.</li>
                <li>Restaurant “house” invite codes let teammates join a shared workspace. Owners/managers should only share codes with trusted staff.</li>
                <li>We may suspend or terminate accounts that violate these Terms, pose a security risk, or remain unpaid after failed billing.</li>
            </ul>

            <h2>Plans &amp; billing</h2>
            <p>
                New self-serve accounts may start with a <strong>14-day free trial</strong> with no card required.
                During the trial you get full access to the plan you chose. When the trial ends, you need an active paid
                subscription (via Stripe) to keep using the house. We may limit one free trial per account.
            </p>
            <p>
                Paid plans are subscriptions billed through Stripe. Prices and plan limits are described on our home page and at checkout.
                Cancellation, renewals, and refunds are described in our
                <a href="/refunds">Cancel &amp; refunds</a> policy, which is part of these Terms.
            </p>

            <h2>Acceptable use</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Break the law or store illegal content in the service.</li>
                <li>Harass others, spam, or abuse messaging features.</li>
                <li>Attempt to access other customers’ houses or data without authorization.</li>
                <li>Probe, disrupt, or overload the service, or reverse engineer it except where allowed by law.</li>
                <li>Resell the service or scrape it for competing products without our written permission.</li>
                <li>Upload malware or content that infringes someone else’s rights.</li>
            </ul>

            <h2>Your content</h2>
            <p>
                You (and your restaurant) own the content you enter into the service (recipes notes, rosters, messages, etc.).
                You grant us a limited license to host, process, and display that content solely to operate the service for you.
                You’re responsible for having the rights to the content you upload.
            </p>

            <h2>Our brand &amp; software</h2>
            <p>
                The ilovepbj name, design, software, and materials we provide are ours (or our licensors’).
                These Terms don’t transfer ownership of our IP to you.
            </p>

            <h2>Third-party services</h2>
            <p>
                Payments use Stripe. Your use of Stripe is also subject to Stripe’s terms.
                We aren’t responsible for outages or decisions of third-party providers beyond our reasonable control.
            </p>

            <h2>Disclaimer</h2>
            <p>
                THE SERVICE IS PROVIDED “AS IS” AND “AS AVAILABLE.” TO THE FULLEST EXTENT PERMITTED BY LAW,
                WE DISCLAIM WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.
                We don’t guarantee uninterrupted or error-free operation.
            </p>

            <h2>Limitation of liability</h2>
            <p>
                TO THE FULLEST EXTENT PERMITTED BY LAW, WE ARE NOT LIABLE FOR INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL,
                OR LOST-PROFITS DAMAGES, OR FOR LOSS OF DATA, ARISING FROM YOUR USE OF THE SERVICE.
                OUR TOTAL LIABILITY FOR ANY CLAIM RELATED TO THE SERVICE IS LIMITED TO THE AMOUNTS YOU PAID US FOR THE SERVICE
                IN THE THREE (3) MONTHS BEFORE THE CLAIM (OR $50 IF YOU PAID NOTHING).
            </p>
            <p>
                Some places don’t allow certain limitations; in those places, our liability is limited to the maximum allowed by law.
            </p>

            <h2>Indemnity</h2>
            <p>
                You agree to defend and hold us harmless from claims arising out of your content, your restaurant’s use of the service,
                or your violation of these Terms, to the extent permitted by law.
            </p>

            <h2>Changes</h2>
            <p>
                We may update these Terms by posting a new version with an updated effective date.
                If changes are material, we’ll try to give reasonable notice (for example via the site or email).
                Continued use after the effective date means you accept the updated Terms.
            </p>

            <h2>Contact</h2>
            <p>
                Questions about these Terms:
                <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
            </p>
<?php
pbj_legal_render_end();
