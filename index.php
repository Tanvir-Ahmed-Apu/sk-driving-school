<?php
require_once 'includes/header.php';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>


<main>
<section class="hero-background position-relative">
    <div id="heroSlider" class="carousel slide carousel-fade h-100" aria-label="Driving school services">

        <div class="carousel-inner h-100">

            <!-- SLIDE 1: BASIC -->
            <div class="carousel-item active h-100">
                <!-- Background Image -->
                <img src="assets/images/banner.png" class="d-block w-100 h-100 object-fit-cover position-absolute"
                    style="z-index: 0;" alt="Driving student learning with a professional instructor">
                <!-- Dark Overlay -->
                <div class="hero-overlay position-absolute w-100 h-100" style="z-index: 1;"></div>

                <!-- Content Container -->
                <div class="container position-relative d-flex align-items-center h-100" style="z-index: 2;">
                    <div class="row w-100">
                        <div class="col-lg-8 col-xl-7 hero-text-box py-5">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3">
                                <i class="bi bi-patch-check-fill me-1" aria-hidden="true"></i> NY DMV Approved School #11644
                            </span>
                            <h1 class="hero-title display-3 fw-bolder lh-sm mb-4 text-white">
                                Start your journey.<br>
                                <span class="text-primary">Learn the basics.</span>
                            </h1>
                            <p class="hero-lead fs-4 mb-5 text-light opacity-75">
                                Build confidence behind the wheel with patient instruction, practical skills, and a plan that fits your experience.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="packages" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow">
                                    Explore Packages <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                                </a>
                                <a href="contact" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold">
                                    Schedule an Assessment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: STANDARD -->
            <div class="carousel-item h-100">
                <!-- Background Image -->
                <img src="assets/images/woodside.jpg" class="d-block w-100 h-100 object-fit-cover position-absolute"
                    style="z-index: 0;" alt="Driving school training location in Woodside, Queens">
                <!-- Dark Overlay -->
                <div class="hero-overlay position-absolute w-100 h-100" style="z-index: 1;"></div>

                <!-- Content Container -->
                <div class="container position-relative d-flex align-items-center h-100" style="z-index: 2;">
                    <div class="row w-100">
                        <div class="col-lg-8 col-xl-7 hero-text-box py-5">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3">Standard
                                Package</span>
                            <h1 class="hero-title display-3 fw-bolder lh-sm mb-4 text-white">
                                Master the road.<br>
                                <span class="text-primary">Pass the test.</span>
                            </h1>
                            <p class="hero-lead fs-4 mb-5 text-light opacity-75">
                                Get focused practice and road-test preparation from instructors who know New York roads.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="packages" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow">
                                    Compare Packages <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                                </a>
                                <a href="faq" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold">
                                    View FAQs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: INDIVIDUAL -->
            <div class="carousel-item h-100">
                <!-- Background Image -->
                <img src="assets/images/woodside1.jpg" class="d-block w-100 h-100 object-fit-cover position-absolute"
                    style="z-index: 0;" alt="Driving lesson on a New York City street">
                <!-- Dark Overlay -->
                <div class="hero-overlay position-absolute w-100 h-100" style="z-index: 1;"></div>

                <!-- Content Container -->
                <div class="container position-relative d-flex align-items-center h-100" style="z-index: 2;">
                    <div class="row w-100">
                        <div class="col-lg-8 col-xl-7 hero-text-box py-5">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3">Individual
                                Service</span>
                            <h1 class="hero-title display-3 fw-bolder lh-sm mb-4 text-white">
                                Need a car?<br>
                                <span class="text-primary">Book a single lesson.</span>
                            </h1>
                            <p class="hero-lead fs-4 mb-5 text-light opacity-75">
                                Need a single lesson, highway practice, or help getting to your road test? We can help you choose the right service.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="packages" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow">
                                    View Individual Services <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                                </a>
                                <a href="contact" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold">
                                    Talk to Our Team
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Show beginner driving lessons"></button>
            <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="1" aria-label="Show road test preparation"></button>
            <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="2" aria-label="Show individual driving services"></button>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev" aria-label="Previous slide">
            <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next" aria-label="Next slide">
            <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
        </button>
    </div>
</section>


<section class="how-it-works-section py-5">
    <div class="container py-4">
        <div class="home-section-heading text-center mb-5">
            <span class="text-primary fw-bold small text-uppercase tracking-wide">Your next step starts here</span>
            <h2 class="fw-bold mt-2 mb-3">Learn to drive with a plan that fits you</h2>
            <p class="text-muted mx-auto mb-0">From your first lesson to road-test day, we’ll help you know what to expect.</p>
        </div>

        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center">
                <div class="license-wrapper">
                    <img src="assets/images/License_Jamaica.jpg" alt="NY DMV Driving School License"
                        class="img-fluid rounded shadow-lg license-img">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="home-feature-card p-4 h-100">
                            <span class="home-feature-icon"><i class="bi bi-signpost-2" aria-hidden="true"></i></span>
                            <h3 class="h5 fw-bold mt-3 mb-2">Start with your goals</h3>
                            <p class="text-muted small mb-0">Tell us your experience and what you want to work on. We’ll help you choose a good starting point.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="home-feature-card p-4 h-100">
                            <span class="home-feature-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                            <h3 class="h5 fw-bold mt-3 mb-2">Schedule your lessons</h3>
                            <p class="text-muted small mb-0">Choose lesson times that work with your schedule and build your skills one drive at a time.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="home-feature-card p-4 h-100">
                            <span class="home-feature-icon"><i class="bi bi-car-front" aria-hidden="true"></i></span>
                            <h3 class="h5 fw-bold mt-3 mb-2">Practice with support</h3>
                            <p class="text-muted small mb-0">Work on the road skills that matter to you with guidance from a professional driving instructor.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="home-feature-card p-4 h-100">
                            <span class="home-feature-icon"><i class="bi bi-award" aria-hidden="true"></i></span>
                            <h3 class="h5 fw-bold mt-3 mb-2">Prepare for test day</h3>
                            <p class="text-muted small mb-0">Get help preparing for your road test, including vehicle and instructor options.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-packages-section py-5">
    <div class="container py-4">
        <div class="home-section-heading text-center mb-5">
            <span class="text-primary fw-bold small text-uppercase tracking-wide">Driving lessons & services</span>
            <h2 class="fw-bold mt-2 mb-3">Find the right way to get started</h2>
            <p class="text-muted mx-auto mb-0">Explore regional package options, build your skills, or book the service you need.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <article class="home-package-card h-100">
                    <span class="home-feature-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill mt-4 mb-2 align-self-start">For new drivers</span>
                    <h3 class="h4 fw-bold">Basic Packages</h3>
                    <p class="text-muted">Start with foundational driving lessons and practice essential road skills at your pace.</p>
                    <ul class="list-unstyled small text-muted mb-4">
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>45-minute driving lessons</li>
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Parking and road skills practice</li>
                        <li><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Multiple package sizes</li>
                    </ul>
                    <a href="packages" class="btn btn-outline-primary rounded-pill fw-semibold mt-auto">Explore Regional Packages <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                </article>
            </div>
            <div class="col-md-6 col-lg-4">
                <article class="home-package-card home-package-featured h-100">
                    <span class="home-feature-icon"><i class="bi bi-award" aria-hidden="true"></i></span>
                    <span class="badge bg-white text-primary rounded-pill mt-4 mb-2 align-self-start">Build confidence</span>
                    <h3 class="h4 fw-bold">Standard Packages</h3>
                    <p class="text-muted">Get more time behind the wheel and prepare for your next milestone with a structured plan.</p>
                    <ul class="list-unstyled small text-muted mb-4">
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Extended lesson bundles</li>
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Road test preparation options</li>
                        <li><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Support from a professional instructor</li>
                    </ul>
                    <a href="packages" class="btn btn-primary rounded-pill fw-semibold mt-auto">Compare Packages <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                </article>
            </div>
            <div class="col-md-6 col-lg-4">
                <article class="home-package-card h-100">
                    <span class="home-feature-icon"><i class="bi bi-car-front" aria-hidden="true"></i></span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill mt-4 mb-2 align-self-start">Book what you need</span>
                    <h3 class="h4 fw-bold">Individual Services</h3>
                    <p class="text-muted">Choose a single lesson, highway practice, or road-test vehicle service—without committing to a bundle.</p>
                    <ul class="list-unstyled small text-muted mb-4">
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Individual driving lessons</li>
                        <li class="mb-2"><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Highway driving practice</li>
                        <li><i class="bi bi-check2 text-primary me-2" aria-hidden="true"></i>Road test car options</li>
                    </ul>
                    <a href="packages" class="btn btn-outline-primary rounded-pill fw-semibold mt-auto">View Individual Services <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                </article>
            </div>
        </div>
        <p class="text-center small text-muted mt-4 mb-0">Package availability and pricing vary by service area. <a href="packages" class="fw-semibold">Choose your region to see current details.</a></p>
    </div>
</section>


<section class="location-section py-5 bg-white">
    <div class="container py-4">
        <div class="row g-4 g-lg-5">


            <div class="col-lg-5">
                <div class="blue-contact-card p-4 p-md-5 h-100 d-flex flex-column">

                  
                    <h4 class="text-white fw-bold mb-4" style="font-size: 1.5rem;">
                        SK Driving School
                    </h4>

                    
                    <ul class="list-unstyled text-white mb-5 d-flex flex-column gap-3 custom-contact-list">

                      
                        <li class="d-flex align-items-center gap-3">
                            <i class="bi bi-telephone-fill fs-5"></i>
                            <div>
                                <small class="text-white-50 d-block lh-1" style="font-size: 0.75rem;">PHONE</small>
                                <a href="tel:+16464000666" class="text-white text-decoration-none fw-bold fs-5">
                                    (646) 400-0666
                                </a>
                            </div>
                        </li>

                      
                        <li class="d-flex align-items-start gap-3">
                            <i class="bi bi-geo-alt-fill fs-5 mt-1"></i>
                            <div>
                                <small class="text-white-50 d-block lh-1 mb-1"
                                    style="font-size: 0.75rem;">LOCATION</small>
                                <a href="https://maps.google.com/?q=60-15+Roosevelt+Ave,+2nd+Fl,+Woodside,+Queens,+NY+11377"
                                    target="_blank" rel="noopener noreferrer" class="text-white text-decoration-none fw-semibold">
                                    60-15 Roosevelt Ave, 2nd Fl, Woodside, Queens, NY 11377
                                </a>
                            </div>
                        </li>

                        
                        <li class="d-flex align-items-start gap-3">
                            <i class="bi bi-clock-fill fs-5 mt-1"></i>
                            <div>
                                <small class="text-white-50 d-block lh-1 mb-1" style="font-size: 0.75rem;">WORKING
                                    HOURS</small>
                                <div class="fw-semibold">Mon – Sat: 7:00 AM – 10:00 PM</div>
                                <div class="text-white-50 small">Sun: 10:00 AM – 5:00 PM</div>
                            </div>
                        </li>

                    </ul>

              
                    <div class="mt-auto">
                        <a href="contact"
                            class="btn bg-white text-primary contact-btn px-4 py-3 fw-bold rounded-pill d-inline-flex align-items-center gap-2">
                            Contact Us <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                </div>
            </div>


            <div class="col-lg-7">
                <div class="map-wrapper">
                    <h3 class="text-dark fw-bold mb-4">Our Location</h3>

                    <iframe
                        src="https://maps.google.com/maps?q=60-15%20Roosevelt%20Ave%2C%202nd%20Fl%2C%20Woodside%2C%20Queens%2C%20NY%2011377&amp;z=16&amp;output=embed"
                        width="100%" height="480" title="Map to SK Driving School at 60-15 Roosevelt Avenue, Woodside, Queens"
                        style="border:0; border-radius: 12px;" allowfullscreen=""
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</section>

</main>

<?php include 'includes/footer.php'; ?>