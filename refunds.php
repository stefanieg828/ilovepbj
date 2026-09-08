<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/legal-layout.inc.php';

pbj_legal_render_start(
    'Cancel & refunds',
    'How subscriptions work, how to cancel, and when refunds apply.'
);
?>
            <h2>Subscriptions</h2>
            <p>
                ilovepbj ops paid plans are <strong>monthly subscriptions</strong> billed through
                <strong>Stripe</strong>. Unless you cancel, your plan renews automatically each billing period
                at the then-current rate for your plan.
            </p>
            <p>
                Plan prices (Individual, crew sizes, and custom quotes) are shown on our
                <a href="/#plans">plans</a> section and confirmed at Stripe Checkout.
            </p>

            <h2>How to cancel</h2>
            <p>You can cancel anytime by emailing:</p>
            <p>
                <a href="mailto:nutsaboutpbj@ilovepbj.shop?subject=Cancel%20subscription">nutsaboutpbj@ilovepbj.shop</a>
            </p>
            <p>Please include:</p>
            <ul>
                <li>The email on your account</li>
                <li>Your restaurant / house name (if any)</li>
                <li>That you’d like to cancel</li>
            </ul>
            <p>
                We’ll cancel the Stripe subscription so it <strong>does not renew</strong> for the next period.
                You’ll usually keep access through the end of the period you’ve already paid for, unless we agree otherwise in writing.
            </p>

            <h2>Refunds</h2>
            <p>
                Because the service is digital and available as soon as access is granted,
                <strong>we generally do not offer automatic pro-rata refunds</strong> for unused days in a billing period after you cancel mid-cycle.
            </p>
            <p>We <strong>will</strong> look at refunds case-by-case when, for example:</p>
            <ul>
                <li>You were charged twice for the same period by mistake</li>
                <li>There was a clear billing error on our side</li>
                <li>You contact us promptly about an accidental signup/charge (ideally within <strong>7 days</strong> of the charge)</li>
            </ul>
            <p>
                Approved refunds are issued back to the original payment method via Stripe and can take several business days to appear on your statement.
            </p>

            <h2>Failed payments</h2>
            <p>
                If a renewal payment fails, Stripe may retry. If payment can’t be collected,
                we may restrict or suspend access until the subscription is current or canceled.
            </p>

            <h2>Receipts &amp; card updates</h2>
            <p>
                Stripe emails receipts for successful charges when configured.
                To update a card or request help with a charge, email
                <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
                and we’ll help through Stripe.
            </p>

            <h2>Free trial</h2>
            <p>
                Self-serve plans include a <strong>14-day free trial</strong> with no card required.
                You will not be charged unless you subscribe through Stripe checkout.
                When the trial ends, access pauses until you subscribe (your house data is kept so you can continue after paying).
                One trial per account; we may end or refuse trials that look abusive.
            </p>
            <h2>Free / pending access</h2>
            <p>
                Manually approved tester accounts (if any) may be ended at any time.
                Deleting an account does not automatically cancel a Stripe subscription — please email us to cancel billing if you still have an active subscription.
            </p>

            <h2>Contact</h2>
            <p>
                Billing, cancel, or refund questions:
                <a href="mailto:nutsaboutpbj@ilovepbj.shop">nutsaboutpbj@ilovepbj.shop</a>
            </p>
            <p>
                Related:
                <a href="/terms">Terms of Service</a> ·
                <a href="/privacy">Privacy Policy</a>
            </p>
<?php
pbj_legal_render_end();
