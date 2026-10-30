<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="row g-5">
                <div class="col-lg-4">
                    <a class="footer-brand" href="index">
                        <span class="footer-brand-mark" aria-hidden="true">SK</span>
                        <span>SK Driving School</span>
                    </a>
                    <p class="footer-intro">Helping New York learners build confidence, prepare for road tests, and move forward one lesson at a time.</p>
                    <span class="footer-license"><i class="bi bi-patch-check-fill me-2" aria-hidden="true"></i>NY DMV Certified • School #11644</span>
                </div>

                <div class="col-6 col-lg-2">
                    <h2 class="footer-heading">Explore</h2>
                    <ul class="footer-links">
                        <li><a href="index">Home</a></li>
                        <li><a href="packages">Driving Packages</a></li>
                        <li><a href="gallery">Gallery</a></li>
                        <li><a href="about">About Us</a></li>
                        <li><a href="faq">FAQs</a></li>
                        <li><a href="terms-and-condition">Terms &amp; Conditions</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2">
                    <h2 class="footer-heading">Get in touch</h2>
                    <ul class="footer-links">
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="tel:+16464000666">Call or text us</a></li>
                        <li><a href="mailto:skdrivingschoolny@gmail.com">Email our team</a></li>
                        <li><a href="https://www.google.com/maps/search/?api=1&amp;query=60-15%20Roosevelt%20Ave%2C%202nd%20Fl%2C%20Woodside%2C%20Queens%2C%20NY%2011377" target="_blank" rel="noopener noreferrer">Get directions</a></li>
                    </ul>
                </div>

                <div class="col-lg-4">
                    <h2 class="footer-heading">Visit our Queens campus</h2>
                    <address class="footer-contact-row">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <span>60-15 Roosevelt Ave, 2nd Fl<br>Woodside, Queens, NY 11377</span>
                    </address>
                    <a class="footer-contact-row footer-contact-link" href="tel:+16464000666">
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <span>(646) 400-0666</span>
                    </a>
                    <p class="footer-hours"><i class="bi bi-clock me-2" aria-hidden="true"></i>Mon–Sat: 7 am–10 pm <span aria-hidden="true">·</span> Sun: 10 am–5 pm</p>
                    <a class="footer-cta" href="packages">Find your driving package <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    <?php
                    if (!isset($_SESSION['newsletter_csrf']) || !is_string($_SESSION['newsletter_csrf'])) {
                        $_SESSION['newsletter_csrf'] = bin2hex(random_bytes(32));
                    }
                    ?>
                    <form class="admin-newsletter-form mt-4" method="post" action="newsletter-subscribe.php">
                        <label for="footer-newsletter-email">Driving tips &amp; updates</label>
                        <div class="input-group mt-2">
                            <input class="form-control" type="email" id="footer-newsletter-email" name="email" autocomplete="email" placeholder="Your email address" required>
                            <button class="btn btn-primary" type="submit" aria-label="Subscribe to newsletter"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['newsletter_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="newsletter-consent" name="consent" value="1" required>
                            <label class="form-check-label" for="newsletter-consent">I agree to receive occasional school news and driving tips.</label>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="mb-0">© <?= date('Y') ?> SK Driving School Inc. All rights reserved.</p>
            <a href="contact" class="footer-bottom-link">Questions? We’re here to help <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (Required for Square Payment Gateway and other UI tasks) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Your custom JS -->
<script src="assets/js/script.js"></script>
</body>
</html>