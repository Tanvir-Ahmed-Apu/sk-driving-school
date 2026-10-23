<?php
$pageTitle = 'Terms & Conditions | SK Driving School';
require_once 'includes/header.php';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main class="terms-page">
    <section class="terms-hero py-5">
        <div class="container py-3">
            <span class="checkout-eyebrow"><i class="bi bi-file-earmark-text me-2" aria-hidden="true"></i>Student information</span>
            <h1 class="display-5 fw-bold mt-3 mb-3">Terms &amp; Conditions</h1>
            <p class="lead text-muted mb-0">Please review these terms for prepaid lessons and services, payments, and refund requests.</p>
        </div>
    </section>

    <section class="terms-content py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-8">
                    <article class="terms-card p-4 p-md-5">
                        <section class="terms-section" aria-labelledby="prepayment-heading">
                            <div class="terms-section-heading">
                                <span class="terms-icon"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></span>
                                <h2 class="h4 fw-bold mb-0" id="prepayment-heading">Prepaid lessons and services</h2>
                            </div>
                            <p>Except for contracts executed by schools licensed by the New York State Education Department and subject to the refund provisions of regulations promulgated by that Department, prepayment for lessons and other services shall be subject to refund as follows:</p>
                            <p>If the student, having given prior notice of at least 24 hours, withdraws from or discontinues a prepaid course of instruction or series of lessons before completion, or from any other service for which prepayment has been made, or if the school is unable or unwilling to complete such prepaid course of instruction or series of lessons, or to provide such other prepaid service, all payments made by the student to the school shall be refunded except:</p>
                            <ol class="terms-exceptions" type="i">
                                <li>An amount equal to the enrollment fee, if any, specified in the contract or expressly receipted for, not to exceed the greater of $10 or 10 percent of the specified cost of the course of instruction or series of lessons; and</li>
                                <li>The school’s per-lesson tuition charge for each lesson already taken by the student. This charge shall be determined by dividing the total cost of the course of instruction or series of lessons by the number of lessons included.</li>
                            </ol>
                        </section>

                        <section class="terms-section" aria-labelledby="online-payment-heading">
                            <div class="terms-section-heading">
                                <span class="terms-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span>
                                <h2 class="h4 fw-bold mb-0" id="online-payment-heading">Website payments and timing</h2>
                            </div>
                            <ul class="terms-list">
                                <li>A 2% deduction will be applied to website payments that are returned.</li>
                                <li>If a payment is incomplete because incorrect information was provided at the time of payment, the school is not responsible for the resulting issue.</li>
                                <li>You will be contacted within 72 hours after your payment has been completed. Payment is not refundable during those 72 hours.</li>
                                <li>If you request a refund between services already provided to you, you must show reasonable cause.</li>
                            </ul>
                        </section>

                        <section class="terms-section mb-0" aria-labelledby="withdrawal-heading">
                            <div class="terms-section-heading">
                                <span class="terms-icon"><i class="bi bi-journal-check" aria-hidden="true"></i></span>
                                <h2 class="h4 fw-bold mb-0" id="withdrawal-heading">Withdrawal from prepaid services</h2>
                            </div>
                            <p class="mb-0">If the student gives at least 24 hours’ prior notice of withdrawal from or discontinuation of a prepaid course of instruction, series of lessons, or another service for which payment has been made—or if the school is unable or unwilling to complete the prepaid course, lessons, or other service—all payments made by the student to the school shall be refunded, subject to the applicable refund provisions above.</p>
                        </section>

                        <div class="terms-contact-note mt-4">
                            <i class="bi bi-question-circle" aria-hidden="true"></i>
                            <p class="mb-0">Questions about a payment or refund? <a href="contact.php">Contact our team</a> or call/text <a href="tel:+16464000666">(646) 400-0666</a>.</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
