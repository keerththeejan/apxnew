<?php

declare(strict_types=1);

use App\Models\Admin;

$title = $title ?? 'APX Admin';
$pageKey = $pageKey ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';
$crumb = $crumb ?? ('APX / ' . $pageTitle);

$adminRole = '';
if (isset($_SESSION['admin_id'])) {
    $adm = Admin::findById((int) $_SESSION['admin_id']);
    if ($adm !== null) {
        $adminRole = strtolower(trim((string) ($adm['role'] ?? '')));
    }
}

$adminAssetRoot = dirname(__DIR__, 3) . '/public/assets';
$adminCssVer = is_file($adminAssetRoot . '/css/admin.css') ? (string) filemtime($adminAssetRoot . '/css/admin.css') : '1';
$adminJsVer = is_file($adminAssetRoot . '/js/admin.js') ? (string) filemtime($adminAssetRoot . '/js/admin.js') : '1';

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(base_url('/assets/css/admin.css')) ?>" />
  <?= $extraHead ?? '' ?>
</head>
<body data-page="<?= e((string) $pageKey) ?>" data-title="<?= e((string) $pageTitle) ?>" data-crumb="<?= e((string) $crumb) ?>">
  <div id="sfApp"></div>

  <?php require $contentView; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
  <?= $extraScripts ?? '' ?>
  <script>window.__APX_BASE__ = <?= json_encode(rtrim(base_url('/'), '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
  <script>window.__ADMIN_ROLE__ = <?= json_encode($adminRole, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
  <script src="<?= e(base_url('/assets/js/admin.js')) ?>?v=<?= e($adminJsVer) ?>"></script>
</body>
</html>
