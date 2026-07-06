<?php

declare(strict_types=1);

$settings = $settings ?? [];
$socialLinks = $socialLinks ?? \App\Services\SiteConfig::socialLinks();
$footerLinksByGroup = $footerLinksByGroup ?? [];
$footerGallery = $footerGallery ?? [];
$logoPath = (string) ($settings['site_logo_path'] ?? '/images/logo.png');
?>
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-top-contact" aria-label="Footer contact">
      <div class="footer-contact-pill">
        <div class="footer-contact-item"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i><span><?= e($settings['contact_address'] ?? 'Colombo, Sri Lanka') ?></span></div>
        <div class="footer-contact-item"><i class="bi bi-envelope-fill" aria-hidden="true"></i><span><?= e($settings['contact_email'] ?? 'info@apx.com') ?></span></div>
        <div class="footer-contact-item"><i class="bi bi-telephone-fill" aria-hidden="true"></i><span><?= e($settings['contact_phone_label'] ?? '+94 77 000 0000') ?></span></div>
      </div>
    </div>

    <div class="container px-0">
      <div class="row g-4">
        <div class="col-12 col-lg-4 footer-col">
        <img class="footer-logo" src="<?= e(base_url(ltrim($logoPath, '/'))) ?>" alt="<?= e($settings['site_name'] ?? 'APX') ?> logo" width="160" height="55" loading="lazy" decoding="async" />
        <p class="footer-tagline"><?= e($settings['footer_tagline'] ?? 'Your joyful journey is in our care') ?></p>
        <div class="footer-social" aria-label="Social links">
          <?php foreach ($socialLinks as $soc): ?>
            <?php
              $slabel = (string) ($soc['label'] ?? '');
              $surl = (string) ($soc['url'] ?? '#');
              $sicon = (string) ($soc['icon'] ?? '');
              $biClass = apx_social_bi_class($slabel, $sicon);
            ?>
            <a href="<?= e(resolve_public_href($surl)) ?>" aria-label="<?= e($slabel) ?>" rel="noopener" title="<?= e($slabel) ?>"><i class="bi <?= e($biClass) ?>" aria-hidden="true"></i></a>
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
            <?php
              $gp = (string) ($g['image_path'] ?? '');
              if ($gp === '') {
                  continue;
              }
              $gAlt = trim((string) ($g['alt_text'] ?? ''));
              $gLabel = $gAlt !== '' ? $gAlt : 'Gallery image';
            ?>
          <div class="footer-thumb-wrap">
            <a class="footer-thumb js-lightbox" href="<?= e(base_url(ltrim($gp, '/'))) ?>" aria-label="<?= e($gLabel) ?>">
              <img src="<?= e(base_url(ltrim($gp, '/'))) ?>" alt="<?= e($gAlt) ?>" loading="lazy" decoding="async" />
            </a>
            <?php if ($gAlt !== ''): ?>
              <span class="footer-thumb-caption"><?= e($gAlt) ?></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php if ($footerGallery === []): ?>
            <p class="small text-white-50 mb-0">Gallery images are managed in Admin → Footer gallery.</p>
          <?php endif; ?>
        </div>

        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?= e((string) date('Y')) ?> <?= e($settings['site_name'] ?? 'APX') ?>. All Rights Reserved</div>
      <div>
        <a href="<?= e(base_url('/')) ?>">Home</a>
        <span class="footer-divider" aria-hidden="true">|</span>
        <a href="<?= e(base_url('/contact')) ?>">Contact</a>
        <span class="footer-divider" aria-hidden="true">|</span>
        <a href="<?= e(base_url('/admin/login')) ?>">Admin login</a>
      </div>
    </div>
  </div>
</footer>
