<?php

declare(strict_types=1);

$title = $title ?? 'APX';
$settings = $settings ?? [];
$defaultLocale = $defaultLocale ?? 'en';
$defaultTheme = $defaultTheme ?? 'light';
$themeClientConfig = $themeClientConfig ?? [];
$themeCssVars = $themeCssVars ?? '';
$metaDescription = $metaDescription ?? '';
$bodyClass = trim((string) ($bodyClass ?? ''));
$tm = isset($themeClientConfig['themeMode']) ? (string) $themeClientConfig['themeMode'] : 'light';
$bodyClass = trim($bodyClass . ' theme-' . ($defaultTheme === 'dark' ? 'dark' : 'light'));

$flashErrors = $_SESSION['flash_errors'] ?? [];
$flashOld = $_SESSION['flash_old'] ?? [];
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_errors'], $_SESSION['flash_old'], $_SESSION['flash_success'], $_SESSION['flash_error']);

$siteName = (string) ($settings['site_name'] ?? 'APX');
$canonicalUrl = base_url(parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$ogImage = base_url(ltrim((string) ($settings['site_logo_path'] ?? '/images/logo.png'), '/'));
$metaDesc = trim($metaDescription) !== '' ? trim($metaDescription) : (string) ($settings['footer_tagline'] ?? 'Your joyful journey is in our care');
?><!doctype html>
<html lang="<?= e((string) $defaultLocale) ?>" class="apx-root" data-bs-theme="<?= e((string) $defaultTheme) ?>" data-theme="<?= e((string) $defaultTheme) ?>" data-theme-mode="<?= e($tm) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <base href="<?= e(rtrim(base_url('/'), '/') . '/') ?>" />
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>" />
  <link rel="canonical" href="<?= e($canonicalUrl) ?>" />
  <meta name="robots" content="index, follow" />
  <meta name="theme-color" content="#0f1c3f" />

  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?= e($siteName) ?>" />
  <meta property="og:title" content="<?= e($title) ?>" />
  <meta property="og:description" content="<?= e($metaDesc) ?>" />
  <meta property="og:url" content="<?= e($canonicalUrl) ?>" />
  <meta property="og:image" content="<?= e($ogImage) ?>" />
  <meta property="og:locale" content="<?= e(str_replace('_', '-', (string) $defaultLocale)) ?>" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= e($title) ?>" />
  <meta name="twitter:description" content="<?= e($metaDesc) ?>" />
  <meta name="twitter:image" content="<?= e($ogImage) ?>" />

  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin />
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="preload" href="<?= e(asset_url('/css/style.css')) ?>" as="style" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&family=Manrope:wght@600;700;800&display=swap" crossorigin="anonymous" />
  <style><?= $themeCssVars !== '' ? $themeCssVars : '' ?></style>
  <link rel="stylesheet" href="<?= e(asset_url('/css/style.css')) ?>" />
  <link rel="stylesheet" href="<?= e(asset_url('/css/apx-premium.css')) ?>" />
  <?php if (str_contains($bodyClass, 'page-home')): ?>
  <link rel="stylesheet" href="<?= e(asset_url('/css/apx-home.css')) ?>" />
  <?php endif; ?>
  <script type="application/json" id="apx-public-config"><?= e(json_encode($themeClientConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
  <script type="application/ld+json"><?= e(json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      'name' => $siteName,
      'url' => base_url('/'),
      'logo' => $ogImage,
      'email' => (string) ($settings['contact_email'] ?? ''),
      'telephone' => (string) ($settings['contact_phone'] ?? ''),
      'address' => [
          '@type' => 'PostalAddress',
          'addressLocality' => (string) ($settings['contact_address'] ?? ''),
      ],
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
</head>
<body class="tms-body<?= $bodyClass !== '' ? ' ' . e($bodyClass) : '' ?>">
  <?php require __DIR__ . '/../partials/header.php'; ?>

  <main id="main-content" tabindex="-1">
    <?php if ($flashSuccess): ?>
      <div class="container pt-3" role="status"><div class="alert alert-success mb-0"><?= e((string) $flashSuccess) ?></div></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="container pt-3" role="alert"><div class="alert alert-danger mb-0"><?= e((string) $flashError) ?></div></div>
    <?php endif; ?>

    <?php require $contentView; ?>
  </main>

  <?php require __DIR__ . '/../partials/footer.php'; ?>

  <button type="button" class="apx-back-to-top" id="apxBackToTop" aria-label="Back to top" title="Back to top">
    <i class="bi bi-arrow-up" aria-hidden="true"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" defer></script>
  <script src="<?= e(asset_url('/js/theme-clock.js')) ?>" defer></script>
  <script src="<?= e(asset_url('/js/script.js')) ?>" defer></script>
  <script src="<?= e(asset_url('/js/apx-premium.js')) ?>" defer></script>
  <script defer>
    document.addEventListener('DOMContentLoaded', function(){
      var forms = document.querySelectorAll('.needs-validation');
      Array.prototype.slice.call(forms).forEach(function(form){
        form.addEventListener('submit', function(event){
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    });
  </script>
</body>
</html>
