<?php

declare(strict_types=1);

use App\Models\Admin;

$title = $title ?? 'APX Admin';
$pageKey = $pageKey ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';
$crumb = $crumb ?? ('APX / ' . $pageTitle);

$adminRole = '';
if (isset($_SESSION['admin_id'])) {
    try {
        $adm = Admin::findById((int) $_SESSION['admin_id']);
        if ($adm !== null) {
            $adminRole = strtolower(trim((string) ($adm['role'] ?? '')));
        }
    } catch (\Throwable $e) {
        error_log('[admin.layout] ' . $e->getMessage());
    }
}

$adminAssetRoot = dirname(__DIR__, 3) . '/public/assets';
$adminCssVer = is_file($adminAssetRoot . '/css/admin.css') ? (string) filemtime($adminAssetRoot . '/css/admin.css') : '1';
$adminJsVer = is_file($adminAssetRoot . '/js/admin.js') ? (string) filemtime($adminAssetRoot . '/js/admin.js') : '1';
$renderedContent = '';
ob_start();
require $contentView;
$renderedContent = (string) ob_get_clean();
$fallbackContent = '';
if (preg_match('/<template\s+id=["\']page-content["\']\s*>([\s\S]*?)<\/template>/i', $renderedContent, $m) === 1) {
    $fallbackContent = (string) ($m[1] ?? '');
}
$fallbackMenu = [
    ['key' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'href' => base_url('/admin')],
    ['key' => 'pages', 'icon' => 'bi-layout-text-window-reverse', 'label' => 'Manage Pages', 'href' => base_url('/admin/pages')],
    ['key' => 'services', 'icon' => 'bi-grid-1x2', 'label' => 'Services', 'href' => base_url('/admin/services')],
    ['key' => 'applications', 'icon' => 'bi-inboxes', 'label' => 'Applications', 'href' => base_url('/admin/applications')],
    ['key' => 'enquiries', 'icon' => 'bi-chat-dots', 'label' => 'Enquiries', 'href' => base_url('/admin/enquiries')],
    ['key' => 'settings', 'icon' => 'bi-gear', 'label' => 'Settings', 'href' => base_url('/admin/settings')],
    ['key' => 'logout', 'icon' => 'bi-box-arrow-right', 'label' => 'Logout', 'href' => base_url('/admin/logout')],
];
if ($adminRole === 'staff') {
    $fallbackMenu = array_values(array_filter($fallbackMenu, static fn(array $m): bool => $m['key'] !== 'settings'));
} elseif ($adminRole === 'driver') {
    $fallbackMenu = array_values(array_filter($fallbackMenu, static fn(array $m): bool => in_array($m['key'], ['dashboard', 'applications', 'logout'], true)));
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <base href="<?= e(rtrim(base_url('/'), '/') . '/') ?>" />
  <title><?= e($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(base_url('/assets/css/admin.css')) ?>" />
  <?= $extraHead ?? '' ?>
</head>
<body data-page="<?= e((string) $pageKey) ?>" data-title="<?= e((string) $pageTitle) ?>" data-crumb="<?= e((string) $crumb) ?>">
  <div id="sfApp"><?php if (trim($fallbackContent) !== ''): ?>
    <div class="sf-app">
      <aside class="sf-sidebar" id="sfSidebar" aria-label="Sidebar navigation">
        <div class="sf-brand">
          <div>
            <div class="sf-brand-title">APX</div>
            <div class="sf-brand-sub">Admin Console</div>
          </div>
        </div>
        <nav class="sf-nav nav flex-column" role="navigation">
          <?php foreach ($fallbackMenu as $item): ?>
            <a class="nav-link<?= $pageKey === $item['key'] ? ' active' : '' ?>" href="<?= e((string) $item['href']) ?>">
              <i class="bi <?= e((string) $item['icon']) ?>"></i>
              <span><?= e((string) $item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </aside>
      <div class="sf-content" id="sfContent">
        <header class="sf-topbar">
          <div class="sf-topbar-inner">
            <div class="d-flex align-items-center gap-2">
              <div>
                <h1 class="sf-page-title"><?= e((string) $pageTitle) ?></h1>
                <div class="sf-breadcrumb"><?= e((string) $crumb) ?></div>
              </div>
            </div>
          </div>
        </header>
        <main class="sf-main" id="sfMain" role="main"><?= $fallbackContent ?></main>
      </div>
    </div>
  <?php endif; ?></div>

  <?= $renderedContent ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
  <?= $extraScripts ?? '' ?>
  <script>window.__APX_BASE__ = <?= json_encode(rtrim(base_url('/'), '/'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
  <script>window.__ADMIN_ROLE__ = <?= json_encode($adminRole, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
  <script>
    // Fallback render: avoid blank page when admin.js cannot mount.
    (function () {
      function mountFallback() {
        var app = document.getElementById('sfApp');
        var tpl = document.getElementById('page-content');
        if (!app || !tpl || app.children.length > 0) return;
        app.innerHTML = '<main class="container py-4"></main>';
        var host = app.querySelector('main');
        if (host && tpl.content) {
          host.appendChild(tpl.content.cloneNode(true));
        }
      }
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountFallback);
      } else {
        mountFallback();
      }
    })();
  </script>
  <script src="<?= e(base_url('/assets/js/admin.js')) ?>?v=<?= e($adminJsVer) ?>"></script>
</body>
</html>
