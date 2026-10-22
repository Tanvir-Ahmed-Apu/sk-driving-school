<?php
require_once 'includes/header.php';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>


<section class="hero-background position-relative">
    <div id="heroSlider" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="5000">

        <div class="carousel-inner h-100">

            <!-- SLIDE 1: BASIC -->
            <div class="carousel-item active h-100">
                <!-- Background Image -->
                <img src="assets/images/banner.png" class="d-block w-100 h-100 object-fit-cover position-absolute"
                    style="z-index: 0;" alt="Basic Package">
                <!-- Dark Overlay -->
                <div class="hero-overlay position-absolute w-100 h-100" style="z-index: 1;"></div>

                <!-- Content Container -->
                <div class="container position-relative d-flex align-items-center h-100" style="z-index: 2;">
                    <div class="row w-100">
                        <div class="col-lg-8 col-xl-7 hero-text-box py-5">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3">Basic Package</span>
                            <h1 class="hero-title display-3 fw-bolder lh-sm mb-4 text-white">
                                Start your journey.<br>
                                <span class="text-primary">Learn the basics.</span>
                            </h1>
                            <p class="hero-lead fs-4 mb-5 text-light opacity-75">
                                5 professional driving lessons, door-to-door pickup, and basic parking drills. Perfect
                                for beginners.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="packages.php"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">Choose Basic -
                                    $395</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: STANDARD -->
            <div class="carousel-item h-100">
                <!-- Background Image -->
                <img src="assets/images/woodside.jpg" class="d-block w-100 h-100 object-fit-cover position-absolute"
                    style="z-index: 0;" alt="Standard Package">
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
                                10 professional driving lessons, 5-hour pre-licensing course, and DMV road test booking
                                + escort.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="packages.php"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">Choose Standard
                                    - $790</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: INDIVIDUAL -->
            <div class="carousel-item h-100">
                <!-- Background Image -->
                <img src="assets/images/woodside1.jpg" class="d-block w-100 h-100 object-fit-cover position-absolute"
                    style="z-index: 0;" alt="Individual Package">
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
                                Just need a certified dual-control vehicle for your exam, or a quick 45-minute
                                refresher? We've got you.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="packages.php"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">Choose
                                    Individual</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Arrows to manually slide -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
        </button>
    </div>
</section>


<section class="how-it-works-section py-5">
    <div class="container py-4">

        <!-- Section Header -->
        <div class="text-center mb-5">
            <h2 class="text-dark fw-bold text-uppercase tracking-wide mb-2">How SK Driving School Works</h2>

            <div class="mx-auto bg-purple" style="height: 4px; width: 40px; border-radius: 2px;"></div>
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

                    <!-- Card 1 -->
                    <div class="col-md-6">
                        <div class="dark-card p-4 h-100 text-center position-relative">
                            <div class="icon-badge mx-auto"><i class="bi bi-camera-video"></i></div>
                            <h5 class="text-dark fw-bold mt-3 mb-2">INSTRUCTIONAL VIDEO</h5>
                            <p class="text-muted small mb-0">We have wide range of packages from 2 driving lesson
                                packages to 20 driving lesson packages.</p>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-md-6">
                        <div class="dark-card p-4 h-100 text-center position-relative">
                            <div class="icon-badge mx-auto"><i class="bi bi-hand-thumbs-up"></i></div>
                            <h5 class="text-dark fw-bold mt-3 mb-2">DRIVING LESSONS</h5>
                            <p class="text-muted small mb-0">Students have opportunity to set up their class schedule
                                according to their prefer time and date.</p>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col-md-6">
                        <div class="dark-card p-4 h-100 text-center position-relative">
                            <div class="icon-badge mx-auto"><i class="bi bi-car-front"></i></div>
                            <h5 class="text-dark fw-bold mt-3 mb-2">DRIVER CONFIDENCE</h5>
                            <p class="text-muted small mb-0">We teach driving lessons for beginners, intermediate and
                                advanced drivers. If you have some driving experience, we will take you on the road for
                                an assessment.</p>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="col-md-6">
                        <div class="dark-card p-4 h-100 text-center position-relative">
                            <div class="icon-badge mx-auto"><i class="bi bi-card-checklist"></i></div>
                            <h5 class="text-dark fw-bold mt-3 mb-2">CERTIFIED</h5>
                            <p class="text-muted small mb-0">We will help you to book your road test appointment. After
                                successfully passing the road test you can have your Driving license.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>


<section class="py-5 bg-white">
    <div class="container py-4 text-center">
        <h2 class="fw-bold">Choose Our Packages</h2>
        <p class="text-muted mb-5">Structured lessons tailored to your prior road experience and timeline.</p>

        <!-- Tabs -->
        <ul class="nav nav-pills justify-content-center mb-5" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active rounded-pill px-4" data-bs-toggle="pill"
                    data-bs-target="#basic">Basic</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-4" data-bs-toggle="pill"
                    data-bs-target="#standard">Standard</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill px-4" data-bs-toggle="pill"
                    data-bs-target="#individual">Individual</button>
            </li>
        </ul>


        <div class="tab-content text-start" id="pills-tabContent">
            <div class="tab-pane fade show active" id="basic">
                <div class="row justify-content-center g-4">


                    <div class="col-lg-4">
                        <div class="card border rounded-4 h-100 p-4 shadow-sm position-relative">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-light text-dark border">5 Lessons</span>
                                <span class="text-primary fw-semibold small">Starter</span>
                            </div>
                            <h4 class="fw-bold">Core Highway & Street</h4>
                            <p class="text-muted small">Perfect for novice drivers acquiring smooth pedal modulation...
                            </p>
                            <ul class="list-unstyled small text-muted my-4">
                                <li class="mb-2">✔ 5 x 45-min dual-control drives</li>
                                <li class="mb-2">✔ Doorstop pickup (select zones)</li>
                                <li class="mb-2">✔ Basic parking drills</li>
                            </ul>
                            <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0">$395</h3>
                                    <small class="text-muted">Total package</small>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-4">
                        <div class="card border-primary border-2 rounded-4 h-100 p-4 shadow-sm position-relative">
                            <span
                                class="badge bg-primary text-white position-absolute top-0 start-50 translate-middle rounded-pill px-3">MOST
                                POPULAR</span>
                            <div class="d-flex justify-content-between mb-3 mt-2">
                                <span class="badge bg-light text-dark border">10 Lessons + Exam</span>
                                <span class="text-primary fw-semibold small">Complete</span>
                            </div>
                            <h4 class="fw-bold">NYC Complete Licensing</h4>
                            <p class="text-muted small">Comprehensive curriculum taking you from zero experience
                                directly to your road exam.</p>
                            <ul class="list-unstyled small text-muted my-4">
                                <li class="mb-2">✔ 10 x 45-min dual-control drives</li>
                                <li class="mb-2">✔ Official 5-Hour Pre-Licensing course</li>
                                <li class="mb-2">✔ DMV Road Test appointment booking</li>
                                <li class="mb-2">✔ Car rental & instructor exam escort</li>
                            </ul>
                            <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0">$790</h3>
                                    <small class="text-muted">All fees included</small>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 rounded-pill mt-3 py-2">Enroll in Complete</button>
                        </div>
                    </div>


                    <div class="col-lg-4">
                        <div class="card border rounded-4 h-100 p-4 shadow-sm position-relative">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-light text-dark border">Exam Day Only</span>
                                <span class="text-primary fw-semibold small">Express</span>
                            </div>
                            <h4 class="fw-bold">Road Test Day Service</h4>
                            <p class="text-muted small">For confident drivers who already have their NYS certificate and
                                require a certified test vehicle.</p>
                            <ul class="list-unstyled small text-muted my-4">
                                <li class="mb-2">✔ 45-minute warm-up lesson before exam</li>
                                <li class="mb-2">✔ Certified Dual-Control sedan rental</li>
                                <li class="mb-2">✔ Licensed instructor accompanying</li>
                            </ul>
                            <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="fw-bold mb-0">$210</h3>
                                    <small class="text-muted">Flat fee</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
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
                                <a href="https://maps.google.com/?q=69-07+Roosevelt+Ave,+Woodside,+NY+11377"
                                    target="_blank" class="text-white text-decoration-none fw-semibold">
                                    69-07 Roosevelt Ave, Woodside, NY 11377
                                </a>
                            </div>
                        </li>

                        
                        <li class="d-flex align-items-start gap-3">
                            <i class="bi bi-clock-fill fs-5 mt-1"></i>
                            <div>
                                <small class="text-white-50 d-block lh-1 mb-1" style="font-size: 0.75rem;">WORKING
                                    HOURS</small>
                                <div class="fw-semibold">Mon – Fri: 7:00 AM – 10:00 PM</div>
                                <div class="text-white-50 small">Sat: 9 AM – 6 PM &bull; Sun: 10 AM – 5 PM</div>
                            </div>
                        </li>

                    </ul>

              
                    <div class="mt-auto">
                        <a href="contact.php"
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
                        src="https://maps.google.com/maps?q=69-07%20Roosevelt%20Ave%202nd%20floor,%20Woodside,%20NY%2011377&t=k&z=16&output=embed"
                        width="100%" height="480" style="border:0; border-radius: 4px;" allowfullscreen=""
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>