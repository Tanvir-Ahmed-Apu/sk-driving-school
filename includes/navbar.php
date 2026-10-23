<nav class="navbar navbar-expand-lg navbar-light bg-white
            border-bottom py-3 sticky-top">

    <div class="container">

        <a class="navbar-brand d-flex align-items-center fw-bold"
           href="index.php">

            <img
                src="assets/images/logo.png"
                alt="SK Driving School logo"
                class="me-2"
                width="40"
                height="40"
                style="object-fit: contain;"
            >

            SK Driving School
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            $topbarMobile = true;
            include __DIR__ . '/topbar.php';
            unset($topbarMobile);
            ?>

            <ul class="navbar-nav mx-auto fw-medium">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="packages.php">Packages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gallery.php">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="faq.php">FAQs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
            </ul>

            <div class="navbar-actions d-flex align-items-center justify-content-between justify-content-lg-end w-100 w-lg-auto mt-3 mt-lg-0 gap-3">

                <a href="cart.php"
                   class="text-dark position-relative"
                   aria-label="Shopping cart">

                    <i class="bi bi-cart3 fs-5" aria-hidden="true"></i>

                    <?php
                    $cartCount = 0;
                    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $quantity) {
                            if (is_int($quantity) && $quantity > 0) {
                                $cartCount += $quantity;
                            }
                        }
                    }
                    ?>

                    <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cartCount ?>
                            <span class="visually-hidden">items in cart</span>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="packages.php"
                   class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                    Book a Drive
                </a>

            </div>
        </div>
    </div>
</nav>