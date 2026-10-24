<?php
$adminPageTitle = $adminPageTitle ?? 'Orders';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?> · SK Driving School Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="index.php"><span>SK</span> Driving School</a>
        <p class="admin-sidebar-label">Workspace</p>
        <a class="admin-nav-link <?= $adminPageTitle === 'Dashboard' ? 'active' : '' ?>" href="index.php"><i class="bi bi-grid-1x2"></i>Dashboard</a>
        <a class="admin-nav-link <?= $adminPageTitle === 'Pending orders' ? 'active' : '' ?>" href="pending.php"><i class="bi bi-hourglass-split"></i>Pending</a>
        <a class="admin-nav-link <?= $adminPageTitle === 'Package sales' ? 'active' : '' ?>" href="package-sales.php"><i class="bi bi-clipboard-data"></i>Package sales</a>
        <a class="admin-nav-link <?= $adminPageTitle === 'Product data' ? 'active' : '' ?>" href="product-data.php"><i class="bi bi-box-seam"></i>Product data</a>
        <a class="admin-nav-link <?= $adminPageTitle === 'Change password' ? 'active' : '' ?>" href="change-password.php"><i class="bi bi-key"></i>Change password</a>
        <div class="admin-sidebar-bottom">
            <span><?= htmlspecialchars($_SESSION['admin_email'] ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?></span>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(adminCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <button class="admin-logout" type="submit"><i class="bi bi-box-arrow-right"></i>Sign out</button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <p class="admin-eyebrow mb-1">SK Driving School · Admin</p>
                <h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <a class="btn btn-outline-primary" href="../index.php" target="_blank" rel="noopener noreferrer">View website <i class="bi bi-box-arrow-up-right ms-1"></i></a>
        </header>
        <div class="admin-content">
