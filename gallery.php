<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/header.php';

$galleryImages = [
    'gallery_1.jpeg',
    'gallery_2.jpeg',
    'gallery_3.jpeg',
    'gallery_4.jpeg',
    'gallery_5.jpeg',
    'gallery_8.jpeg',
    'gallery_12.jpg',
    'gallery_16.jpg',
];
$galleryBaseUrl = 'https://www.skdrivingschoolny.com/images/gallery/';
?>
<header class="site-header">
    <?php
    require_once 'includes/topbar.php';
    require_once 'includes/navbar.php';
    ?>
</header>

<main>
    <section class="gallery-hero py-5">
        <div class="container py-lg-4 text-center">
            <span class="badge bg-white text-primary border rounded-pill px-3 py-2 mb-3">
                <i class="bi bi-images me-1" aria-hidden="true"></i> SK Driving School • Photo Gallery
            </span>
            <h1 class="display-4 fw-bold mb-3">A closer look at life on the road</h1>
            <p class="lead text-muted mx-auto mb-4">Explore moments from SK Driving School.</p>
        </div>
    </section>

    <section class="gallery-section py-5" aria-label="SK Driving School photo gallery">
        <div class="container">
            <div class="gallery-grid">
                <?php foreach ($galleryImages as $index => $image): ?>
                    <?php $imageUrl = $galleryBaseUrl . rawurlencode($image); ?>
                    <figure class="gallery-item mb-0">
                        <img
                            src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="SK Driving School gallery photo <?= $index + 1 ?>"
                            loading="<?= $index < 3 ? 'eager' : 'lazy' ?>"
                            <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
