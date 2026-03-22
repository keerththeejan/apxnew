<?php

declare(strict_types=1);

$settings = $settings ?? [];
$socialLinks = $socialLinks ?? \App\Services\SiteConfig::socialLinks();
$footerLinksByGroup = $footerLinksByGroup ?? [];
$footerGallery = $footerGallery ?? [];
?>
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-top-contact" aria-label="Footer contact">
      <div class="footer-contact-pill">
        <div class="footer-contact-item">📍 <span><?= e($settings['contact_address'] ?? 'Colombo, Sri Lanka') ?></span></div>
        <div class="footer-contact-item">✉️ <span><?= e($settings['contact_email'] ?? 'info@apx.com') ?></span></div>
        <div class="footer-contact-item">📞 <span><?= e($settings['contact_phone_label'] ?? '+94 77 000 0000') ?></span></div>
      </div>
    </div>

    <div class="container px-0">
      <div class="row g-4">
        <div class="col-12 col-lg-4 footer-col">
        <?php $logoPath = (string) ($settings['site_logo_path'] ?? '/images/logo.png'); ?>
        <img src="<?= e(base_url(ltrim($logoPath, '/'))) ?>" alt="<?= e($settings['site_name'] ?? 'APX') ?> logo" width="160" height="55" loading="lazy" decoding="async" style="border-radius:10px;background:rgba(255,255,255,.92);padding:12px;border:1px solid rgba(255,255,255,.35);box-shadow:0 2px 10px rgba(0,0,0,.2)" />
        <p style="margin:10px 0 0;color:rgba(255,255,255,.8);font-weight:700"><?= e($settings['footer_tagline'] ?? 'Your joyful journey is in our care') ?></p>
        <div class="footer-social" aria-label="Social links">
          <?php foreach ($socialLinks as $soc): ?>
            <?php
              $slabel = (string) ($soc['label'] ?? '');
              $surl = (string) ($soc['url'] ?? '#');
              $hint = mb_substr($slabel, 0, 1) ?: '·';
            ?>
            <a href="<?= e(resolve_public_href($surl)) ?>" aria-label="<?= e($slabel) ?>" rel="noopener"><?= e($hint) ?></a>
          <?php endforeach; ?>
        </div>

        </div>

        <?php foreach ($footerLinksByGroup as $groupName => $links): ?>
        <div class="col-6 col-lg-2 footer-col">
        <h4><?= e((string) $groupName) ?></h4>
        <ul>
          <?php foreach ($links as $ln): ?>
            <li><a href="<?= e(resolve_public_href((string) ($ln['url'] ?? '#'))) ?>"><?= e((string) ($ln['label'] ?? '')) ?></a></li>
          <?php endforeach; ?>
        </ul>
        </div>
        <?php endforeach; ?>

        <div class="col-12 col-lg-4 footer-col">
        <h4>Gallery</h4>
        <div class="footer-gallery" aria-label="Gallery">
          <?php foreach ($footerGallery as $g): ?>
            <?php $gp = (string) ($g['image_path'] ?? ''); if ($gp === '') { continue; } ?>
          <a class="footer-thumb js-lightbox" href="<?= e(base_url(ltrim($gp, '/'))) ?>" aria-label="<?= e((string) ($g['alt_text'] ?? 'Gallery')) ?>">
            <img src="<?= e(base_url(ltrim($gp, '/'))) ?>" alt="<?= e((string) ($g['alt_text'] ?? '')) ?>" loading="lazy" decoding="async" />
          </a>
          <?php endforeach; ?>
        </div>

        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?= e((string) date('Y')) ?> <?= e($settings['site_name'] ?? 'APX') ?>. All Rights Reserved</div>
      <div>
        <a href="<?= e(base_url('/')) ?>">Home</a>
        <span style="opacity:.65;margin:0 8px">|</span>
        <a href="<?= e(base_url('/contact')) ?>">Contact</a>
        <span style="opacity:.65;margin:0 8px">|</span>
        <a href="<?= e(base_url('/admin/login')) ?>">Admin login</a>
      </div>
    </div>
  </div>
</footer>
