<?php

declare(strict_types=1);

$settings = $settings ?? [];
$socialLinks = $socialLinks ?? \App\Services\SiteConfig::socialLinks();
$navMenu = $navMenu ?? ['links' => [], 'ctas' => []];
$navCurrentKey = $navCurrentKey ?? \App\Models\NavItem::currentRequestNavKey();

if (!function_exists('apx_nav_branch_active')) {
    function apx_nav_branch_active(array $item, string $currentKey): bool
    {
        if (\App\Models\NavItem::itemNavKey($item) === $currentKey) {
            return true;
        }
        foreach ($item['children'] ?? [] as $ch) {
            if (apx_nav_branch_active($ch, $currentKey)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('apx_nav_icon_html')) {
    function apx_nav_icon_html(?string $icon, ?string $label = null): string
    {
        if ($icon === null || $icon === '') {
            return '';
        }
        $icon = trim($icon);
        if ($label !== null && strcasecmp(trim($label), $icon) === 0) {
            return '';
        }
        if (str_starts_with($icon, 'bi ')) {
            return '<i class="' . e($icon) . ' me-1" aria-hidden="true"></i>';
        }
        if (str_starts_with($icon, 'bi-')) {
            return '<i class="bi ' . e($icon) . ' me-1" aria-hidden="true"></i>';
        }

        return '<span class="me-1" aria-hidden="true">' . e($icon) . '</span>';
    }
}

$logoPath = (string) ($settings['site_logo_path'] ?? '/images/logo.png');
$navLinks = $navMenu['links'] ?? [];
$navCtas = $navMenu['ctas'] ?? [];
if ($navCtas === []) {
    $navCtas = [
        [
            'label' => (string) ($settings['nav_apply_label'] ?? 'Apply Now'),
            'url' => (string) ($settings['nav_apply_url'] ?? '/#apply'),
            'open_new_tab' => 0,
            'icon' => null,
            'children' => [],
        ],
        [
            'label' => (string) ($settings['nav_contact_label'] ?? 'Contact'),
            'url' => (string) ($settings['nav_contact_url'] ?? '/contact'),
            'open_new_tab' => 0,
            'icon' => null,
            'children' => [],
        ],
    ];
}
?>
<a href="#main-content" class="visually-hidden-focusable skip-link">Skip to content</a>
<header class="topbar">
  <div class="top-contact-bar">
    <div class="top-contact-inner">
      <div class="top-contact-left">
        <a class="top-contact-link" href="mailto:<?= e($settings['contact_email'] ?? 'info@apx.com') ?>" aria-label="Email">✉️ <?= e($settings['contact_email'] ?? 'info@apx.com') ?></a>
        <a class="top-contact-link" href="tel:<?= e($settings['contact_phone'] ?? '+94770000000') ?>" aria-label="Phone">📞 <?= e($settings['contact_phone_label'] ?? '+94 77 000 0000') ?></a>
      </div>
      <div class="top-contact-right" aria-label="Social links">
        <div class="top-social-links">
          <?php foreach ($socialLinks as $soc): ?>
            <?php
              $slabel = (string) ($soc['label'] ?? '');
              $surl = (string) ($soc['url'] ?? '#');
              $hint = mb_substr($slabel, 0, 1) ?: '·';
            ?>
            <a class="top-social" href="<?= e(resolve_public_href($surl)) ?>" aria-label="<?= e($slabel) ?>" title="<?= e($slabel) ?>" rel="noopener"><?= e($hint) ?></a>
          <?php endforeach; ?>
          <a class="top-social" href="<?= e(base_url('/admin/login')) ?>" aria-label="Admin login" title="Admin login">A</a>
          <button type="button" class="top-social theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Theme">🌙</button>
        </div>
      </div>
    </div>
  </div>

  <div class="navbar-shell">
      <a class="brand" href="<?= e(base_url('/')) ?>" aria-label="Home">
        <img src="<?= e(base_url(ltrim($logoPath, '/'))) ?>" alt="<?= e($settings['site_name'] ?? 'APX') ?> logo" width="180" height="60" loading="eager" decoding="async" />
      </a>

      <button class="hamb" type="button" aria-expanded="false" aria-controls="navbar-menu" aria-label="Open menu">Menu</button>

      <div id="navbar-menu" class="navbar-menu navbar-collapse">
        <ul class="primary-links navbar-nav ms-lg-auto mb-0 align-items-lg-center flex-column flex-lg-row gap-lg-1" aria-label="Primary">
          <?php foreach ($navLinks as $item): ?>
            <?php
              $children = $item['children'] ?? [];
              $hasChildren = is_array($children) && $children !== [];
              $href = resolve_public_href((string) ($item['url'] ?? '/'));
              $target = (int) ($item['open_new_tab'] ?? 0) === 1 ? ' target="_blank" rel="noopener"' : '';
              $branchOn = apx_nav_branch_active($item, $navCurrentKey);
              $selfActive = \App\Models\NavItem::itemNavKey($item) === $navCurrentKey;
            ?>
            <?php if ($hasChildren): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle<?= $branchOn ? ' is-active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" id="nav-dd-<?= (int) ($item['id'] ?? 0) ?>">
                  <?= apx_nav_icon_html(isset($item['icon']) ? (string) $item['icon'] : null, (string) ($item['label'] ?? '')) ?><?= e((string) ($item['label'] ?? '')) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="nav-dd-<?= (int) ($item['id'] ?? 0) ?>">
                  <?php foreach ($children as $child): ?>
                    <?php
                      $chref = resolve_public_href((string) ($child['url'] ?? '/'));
                      $ctarget = (int) ($child['open_new_tab'] ?? 0) === 1 ? ' target="_blank" rel="noopener"' : '';
                      $cactive = \App\Models\NavItem::itemNavKey($child) === $navCurrentKey;
                    ?>
                    <li>
                      <a class="dropdown-item<?= $cactive ? ' active' : '' ?>" data-nav href="<?= e($chref) ?>"<?= $ctarget ?><?php if ($cactive): ?> aria-current="page"<?php endif; ?>><?= apx_nav_icon_html(isset($child['icon']) ? (string) $child['icon'] : null, (string) ($child['label'] ?? '')) ?><?= e((string) ($child['label'] ?? '')) ?></a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link<?= $selfActive ? ' is-active' : '' ?>" data-nav href="<?= e($href) ?>"<?= $target ?><?php if ($selfActive): ?> aria-current="page"<?php endif; ?>><?= apx_nav_icon_html(isset($item['icon']) ? (string) $item['icon'] : null, (string) ($item['label'] ?? '')) ?><?= e((string) ($item['label'] ?? '')) ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>

        <div class="nav-cta flex-shrink-0" role="group" aria-label="Primary actions">
          <?php foreach ($navCtas as $i => $cta): ?>
            <?php
              $cu = resolve_public_href((string) ($cta['url'] ?? '/'));
              $ct = (int) ($cta['open_new_tab'] ?? 0) === 1 ? ' target="_blank" rel="noopener"' : '';
              $btnClass = ($i % 2 === 0) ? 'btn btn-cta' : 'btn btn-cta-outline';
            ?>
            <a class="<?= e($btnClass) ?>" href="<?= e($cu) ?>"<?= $ct ?>><?= apx_nav_icon_html(isset($cta['icon']) ? (string) $cta['icon'] : null, (string) ($cta['label'] ?? '')) ?><?= e((string) ($cta['label'] ?? '')) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
</header>
