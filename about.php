<?php
require_once 'includes/header.php';

$lessonAreas = [
    'Queens Village',
    'Hollis',
    'Hempstead',
    'Laurelton',
    'Jamaica',
    'Rego Park',
    'Ozone Park',
    'Middle Village',
    'Glendale',
    'Maspeth',
    'Jackson Heights',
    'Astoria',
    'Woodside',
    'Sunnyside',
];

$roadTestAreas = [
    'Astoria Heights',
    'Jamaica',
    'Cunningham Park',
    'Laurelton',
    'Rosedale',
    'Springfield',
    'Fresh Meadows',
    'Kissena Park',
    'Maspeth',
    'Ozone Park',
];

$teamMembers = [
    [
        'name' => 'Munna Khan',
        'role' => 'Manager',
        'image' => 'https://www.skdrivingschoolny.com/images/employee/Munna-Khan.jpg',
        'description' => 'Oversees daily operations and staff, and helps ensure the school follows Department of Motor Vehicles requirements.',
        'email' => 'mailto:munna.khan@skdrivingschoolny.com',
        'facebook' => 'https://www.facebook.com/khan.munna97',
        'linkedin' => 'https://www.linkedin.com/in/munna-khan-13a834258/',
    ],
    [
        'name' => 'Monoget Saha',
        'role' => 'Developer',
        'image' => 'https://www.skdrivingschoolny.com/images/employee/monoget.jpg',
        'description' => 'Builds and maintains web applications, software tools, and automation for the school’s digital services.',
        'email' => 'mailto:monoget1@gmail.com',
        'facebook' => 'https://www.facebook.com/monoget1',
        'linkedin' => 'https://www.linkedin.com/in/monoget/',
    ],
];
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main>
    <section class="about-hero py-5">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary border rounded-pill px-3 py-2 mb-3">
                        <i class="bi bi-patch-check-fill me-1" aria-hidden="true"></i> NY DMV Approved • School #11644
                    </span>
                    <h1 class="display-4 fw-bold lh-sm mb-4">Helping New York drivers feel ready for the road.</h1>
                    <p class="lead text-muted mb-4">SK Driving School Inc offers behind-the-wheel lessons, road test support, and online pre-licensing classes for learners across New York and nearby communities.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="packages.php" class="btn btn-primary btn-lg rounded-pill px-4">
                            Explore Packages <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg rounded-pill px-4">Talk to Our Team</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="about-promise-card">
                        <span class="about-promise-icon"><i class="bi bi-car-front" aria-hidden="true"></i></span>
                        <h2 class="h4 fw-bold mt-3">Learn at your pace</h2>
                        <p class="text-muted mb-0">Choose a package or individual service, then schedule lessons around your goals and availability.</p>
                        <div class="about-promise-divider"></div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="about-promise-icon about-promise-icon-small"><i class="bi bi-calendar-week" aria-hidden="true"></i></span>
                            <div>
                                <span class="fw-bold d-block">Open 7 days a week</span>
                                <span class="small text-muted">By scheduled appointment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-lg-4">
            <div class="about-section-heading text-center mb-5">
                <span class="text-primary fw-bold small text-uppercase">What we offer</span>
                <h2 class="h1 fw-bold mt-2">Support for every step of your drive</h2>
                <p class="text-muted mb-0">Practical services and flexible options to help you prepare with confidence.</p>
            </div>
            <div class="row g-4">
                <div class="col-sm-6 col-lg-3">
                    <article class="about-service-card h-100">
                        <span class="about-service-icon"><i class="bi bi-signpost-2" aria-hidden="true"></i></span>
                        <h3 class="h5 fw-bold mt-3">Behind-the-wheel lessons</h3>
                        <p class="text-muted small mb-0">Available lesson schedules and highway driving instruction with experienced instructors.</p>
                    </article>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <article class="about-service-card h-100">
                        <span class="about-service-icon"><i class="bi bi-car-front" aria-hidden="true"></i></span>
                        <h3 class="h5 fw-bold mt-3">Road test support</h3>
                        <p class="text-muted small mb-0">Road test car rentals and rush appointment assistance, subject to availability.</p>
                    </article>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <article class="about-service-card h-100">
                        <span class="about-service-icon"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                        <h3 class="h5 fw-bold mt-3">Online 5-hour class</h3>
                        <p class="text-muted small mb-0">Online New York Pre-Licensing Course options available throughout the week.</p>
                    </article>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <article class="about-service-card h-100">
                        <span class="about-service-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                        <h3 class="h5 fw-bold mt-3">Flexible, personal service</h3>
                        <p class="text-muted small mb-0">Multilingual support, female instructors, package deals, and payment plan options.</p>
                    </article>
                </div>
            </div>
            <div class="about-pickup-note mt-4">
                <i class="bi bi-geo-alt-fill text-primary" aria-hidden="true"></i>
                <p class="mb-0"><strong>Pickup and drop-off:</strong> Free pickup and drop-off are available in eligible areas close to our office. Contact us to confirm service for your location.</p>
            </div>
        </div>
    </section>


    <section class="py-5 bg-white">
        <div class="container py-lg-4">
            <div class="about-section-heading text-center mb-5">
                <span class="text-primary fw-bold small text-uppercase">Meet the team</span>
                <h2 class="h1 fw-bold mt-2">People behind the service</h2>
                <p class="text-muted mb-0">Our administration and technology team help keep the school running and connected.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php foreach ($teamMembers as $member): ?>
                    <div class="col-md-6 col-xl-5">
                        <article class="about-team-card h-100">
                            <img
                                src="<?= htmlspecialchars($member['image'], ENT_QUOTES, 'UTF-8') ?>"
                                class="about-team-image"
                                alt="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>"
                                loading="lazy">
                            <div class="about-team-content">
                                <span class="badge bg-primary-subtle text-primary rounded-pill mb-2"><?= htmlspecialchars($member['role'], ENT_QUOTES, 'UTF-8') ?></span>
                                <h3 class="h4 fw-bold mb-2"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-muted small mb-4"><?= htmlspecialchars($member['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="about-team-links">
                                    <a href="<?= htmlspecialchars($member['email'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Email <?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-envelope" aria-hidden="true"></i></a>
                                    <a href="<?= htmlspecialchars($member['facebook'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?> on Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                                    <a href="<?= htmlspecialchars($member['linkedin'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?> on LinkedIn"><i class="bi bi-linkedin" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="about-team-note text-center mt-4">
                <i class="bi bi-globe2 me-2 text-primary" aria-hidden="true"></i>Remote administration and development team
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
