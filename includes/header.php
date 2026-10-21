<?php

session_start();
require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Driving School - Drive Smart</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts (Example: Inter for that modern look) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="topbar topbar-desktop py-2">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-2">
    <div class="topbar-social d-flex align-items-center" aria-label="Social media">
      <a href="#" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
      <a href="#" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
      <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
      <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin" aria-hidden="true"></i></a>
      <a href="#" aria-label="YouTube"><i class="bi bi-youtube" aria-hidden="true"></i></a>
    </div>
    <div class="topbar-info d-flex flex-column flex-md-row align-items-center justify-content-end gap-2 gap-lg-4 small">
      <a href="tel:+16464000666"><i class="bi bi-telephone-fill me-2" aria-hidden="true"></i>(646)-400-0666</a>
      <a href="https://maps.google.com/?q=69-07+Roosevelt+Ave+2nd+floor,+Woodside,+New+York+11377" target="_blank" rel="noopener noreferrer">
        <i class="bi bi-geo-alt-fill me-2" aria-hidden="true"></i>69-07 Roosevelt Ave 2nd floor, Woodside, New York 11377
      </a>
      <div class="topbar-hours">
        <span><i class="bi bi-clock-fill me-2" aria-hidden="true"></i>Mon - Fri: 7am to 10pm</span>
        <span><i class="bi bi-clock-fill me-2" aria-hidden="true"></i>Sat: 9am to 6pm &amp; Sun: 10am to 5pm</span>
      </div>
    </div>
  </div>
</div>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
      <img src="assets/images/logo.png" alt="SK Driving School logo" class="me-2" style="width: 40px; height: 40px; object-fit: contain;">
      SK Driving School
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="topbar topbar-mobile py-2">
        <div class="topbar-social d-flex align-items-center" aria-label="Social media">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
          <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin" aria-hidden="true"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube" aria-hidden="true"></i></a>
        </div>
        <div class="topbar-info d-flex flex-column small">
          <a href="tel:+16464000666"><i class="bi bi-telephone-fill me-2" aria-hidden="true"></i>(646)-400-0666</a>
          <a href="https://maps.google.com/?q=69-07+Roosevelt+Ave+2nd+floor,+Woodside,+New+York+11377" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-geo-alt-fill me-2" aria-hidden="true"></i>69-07 Roosevelt Ave 2nd floor, Woodside, New York 11377
          </a>
          <div class="topbar-hours">
            <span><i class="bi bi-clock-fill me-2" aria-hidden="true"></i>Mon - Fri: 7am to 10pm</span>
            <span><i class="bi bi-clock-fill me-2" aria-hidden="true"></i>Sat: 9am to 6pm &amp; Sun: 10am to 5pm</span>
          </div>
        </div>
      </div>
      <ul class="navbar-nav mx-auto fw-medium">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="packages.php">Packages</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="faq.php">FAQs</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
      </ul>
      
      <div class="navbar-actions d-flex align-items-center justify-content-between gap-3">
        <a href="checkout.php" class="text-dark position-relative d-inline-flex align-items-center" aria-label="Checkout">
            <i class="bi bi-bag-check fs-5" aria-hidden="true"></i>
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo count($_SESSION['cart']); ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="packages.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">Book a Drive</a>
      </div>
    </div>
  </div>
</nav>