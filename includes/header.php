<?php
/**
 * Public site header (navbar + optional hero).
 * Usage: $pageTitle, $activeNav (e.g. 'home','events','about','login')
 */
require_once __DIR__ . '/auth.php';
$isStudent = is_student_logged_in();
$isAdmin   = is_admin_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'SmartPass') ?> · Smart College Event Passport</title>
<link rel="stylesheet" href="<?= e(base_url()) ?>css/style.css">
</head>
<body>
<header class="site-header" id="siteHeader">
  <div class="container nav-wrap">
    <a href="<?= e(base_url()) ?>index.php" class="brand">
      <span class="brand-mark">SP</span>
      <span class="brand-name">SMART<span class="brand-accent">PASS</span></span>
    </a>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>
    <nav class="site-nav" id="siteNav">
      <a href="<?= e(base_url()) ?>index.php" class="<?= ($activeNav ?? '')==='home'?'active':'' ?>">Home</a>
      <a href="<?= e(base_url()) ?>events.php" class="<?= ($activeNav ?? '')==='events'?'active':'' ?>">Events</a>
      <a href="<?= e(base_url()) ?>about.php" class="<?= ($activeNav ?? '')==='about'?'active':'' ?>">About</a>
      <?php if ($isStudent): ?>
        <a href="<?= e(base_url()) ?>student/dashboard.php" class="nav-cta">Dashboard</a>
        <a href="<?= e(base_url()) ?>logout.php">Logout</a>
      <?php elseif ($isAdmin): ?>
        <a href="<?= e(base_url()) ?>admin/dashboard.php" class="nav-cta">Admin</a>
        <a href="<?= e(base_url()) ?>logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= e(base_url()) ?>login.php" class="<?= ($activeNav ?? '')==='login'?'active':'' ?>">Login</a>
        <a href="<?= e(base_url()) ?>register.php" class="nav-cta">Get Started</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main>
