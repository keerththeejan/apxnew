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
?><!doctype html>
<html lang="<?= e((string) $defaultLocale) ?>" class="apx-root" data-bs-theme="<?= e((string) $defaultTheme) ?>" data-theme="<?= e((string) $defaultTheme) ?>" data-theme-mode="<?= e($tm) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($title) ?></title>
  <?php if (trim($metaDescription) !== ''): ?>
  <meta name="description" content="<?= e($metaDescription) ?>" />
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" crossorigin="anonymous" />
  <style><?= $themeCssVars !== '' ? $themeCssVars : '' ?></style>
  <link rel="stylesheet" href="<?= e(base_url('/css/style.css')) ?>?v=<?= time() ?>" />
  <script type="application/json" id="apx-public-config"><?= e(json_encode($themeClientConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
</head>
<body class="tms-body<?= $bodyClass !== '' ? ' ' . e($bodyClass) : '' ?>">
  <?php require __DIR__ . '/../partials/header.php'; ?>

  <main id="main-content" tabindex="-1">
    <?php if ($flashSuccess): ?>
      <div class="container pt-3"><div class="alert alert-success mb-0"><?= e((string) $flashSuccess) ?></div></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="container pt-3"><div class="alert alert-danger mb-0"><?= e((string) $flashError) ?></div></div>
    <?php endif; ?>

    <?php require $contentView; ?>
  </main>

  <?php require __DIR__ . '/../partials/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="<?= e(base_url('/js/theme-clock.js')) ?>?v=<?= time() ?>"></script>
  <script src="<?= e(base_url('/js/script.js')) ?>?v=<?= time() ?>"></script>
  <script>
    (function(){
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
    })();
  </script>
</body>
</html>
