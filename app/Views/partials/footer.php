<?php

declare(strict_types=1);

$settings = $settings ?? [];
$socialLinks = $socialLinks ?? \App\Services\SiteConfig::socialLinks();
$footerLinksByGroup = $footerLinksByGroup ?? [];
$footerGallery = $footerGallery ?? [];
$logoPath = (string) ($settings['site_logo_path'] ?? '/images/logo.png');
$contactAddress = (string) ($settings['contact_address'] ?? 'Colombo, Sri Lanka');
$contactEmail = (string) ($settings['contact_email'] ?? 'info@apx.com');
$contactPhoneLabel = (string) ($settings['contact_phone_label'] ?? '+94 77 000 0000');
$contactPhoneHref = (string) ($settings['contact_phone'] ?? $contactPhoneLabel);

if (!function_exists('apx_footer_link_icon')) {
    function apx_footer_link_icon(string $label): string
    {
        $label = strtolower($label);
        $map = [
            'about' => 'bi-house',
            'contact' => 'bi-telephone',
            'destination' => 'bi-geo-alt',
            'blog' => 'bi-newspaper',
            'news' => 'bi-newspaper',
            'flight' => 'bi-airplane',
            'visa' => 'bi-file-earmark-text',
            'insurance' => 'bi-shield-check',
            'hotel' => 'bi-building',
            'vehicle' => 'bi-car-front',
        ];
        foreach ($map as $key => $icon) {
            if (str_contains($label, $key)) {
                return $icon;
            }
        }

        return 'bi-link-45deg';
    }
}
?>
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-top-contact" aria-label="Footer contact">
      <div class="footer-contact-pill">
        <div class="footer-contact-item">
          <span class="footer-contact-icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></span>
          <span class="footer-contact-copy">
            <strong><?= e($contactAddress) ?></strong>
            <small>Our Location</small>
          </span>
        </div>
        <div class="footer-contact-item">
          <span class="footer-contact-icon" aria-hidden="true"><i class="bi bi-envelope-fill"></i></span>
          <span class="footer-contact-copy">
            <a href="mailto:<?= e($contactEmail) ?>"><strong><?= e($contactEmail) ?></strong></a>
            <small>Send us an email</small>
          </span>
        </div>
        <div class="footer-contact-item">
          <span class="footer-contact-icon" aria-hidden="true"><i class="bi bi-telephone-fill"></i></span>
          <span class="footer-contact-copy">
            <a href="tel:<?= e($contactPhoneHref) ?>"><strong><?= e($contactPhoneLabel) ?></strong></a>
            <small>Call us anytime</small>
          </span>
        </div>
      </div>
    </div>

    <div class="footer-grid">
      <div class="footer-col footer-brand">
        <img class="footer-logo" src="<?= e(base_url(ltrim($logoPath, '/'))) ?>" alt="<?= e($settings['site_name'] ?? 'APX') ?> logo" width="160" height="55" loading="lazy" decoding="async" />
        <p class="footer-tagline"><?= e($settings['footer_tagline'] ?? 'Your joyful journey is in our care') ?></p>
        <div class="footer-social" aria-label="Social links">
          <?php foreach ($socialLinks as $soc): ?>
            <?php
              $slabel = (string) ($soc['label'] ?? '');
              $surl = (string) ($soc['url'] ?? '#');
              $sicon = (string) ($soc['icon'] ?? '');
              $biClass = apx_social_bi_class($slabel, $sicon);
              $socialKey = strtolower(trim($sicon !== '' ? $sicon : $slabel));
            ?>
            <a class="footer-social-link footer-social-link--<?= e(preg_replace('/[^a-z0-9]+/', '', $socialKey) ?? 'link') ?>" href="<?= e(resolve_public_href($surl)) ?>" aria-label="<?= e($slabel) ?>" rel="noopener" title="<?= e($slabel) ?>"><i class="bi <?= e($biClass) ?>" aria-hidden="true"></i></a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php foreach ($footerLinksByGroup as $groupName => $links): ?>
        <div class="footer-col">
          <h4><?= e((string) $groupName) ?></h4>
          <ul>
            <?php foreach ($links as $ln): ?>
              <?php $linkLabel = (string) ($ln['label'] ?? ''); ?>
              <li>
                <a class="footer-link" href="<?= e(resolve_public_href((string) ($ln['url'] ?? '#'))) ?>">
                  <i class="bi <?= e(apx_footer_link_icon($linkLabel)) ?>" aria-hidden="true"></i>
                  <span><?= e($linkLabel) ?></span>
                  <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>

      <div class="footer-col footer-gallery-col">
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
            <p class="small mb-0">Gallery images are managed in Admin → Footer gallery.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div>© <?= e((string) date('Y')) ?> <?= e($settings['site_name'] ?? 'APX') ?>. All Rights Reserved</div>
      <div class="footer-bottom-links">
        <a href="<?= e(base_url('/')) ?>">Home</a>
        <span class="footer-divider" aria-hidden="true">|</span>
        <a href="<?= e(base_url('/contact')) ?>">Contact</a>
        <span class="footer-divider" aria-hidden="true">|</span>
        <a href="<?= e(base_url('/admin/login')) ?>">Admin login</a>
      </div>
    </div>
  </div>
</footer>
