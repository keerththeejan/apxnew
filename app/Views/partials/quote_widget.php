<?php

declare(strict_types=1);

$settings = $settings ?? [];
$waDigits = preg_replace('/\D/', '', (string) ($settings['whatsapp_number'] ?? ''));
if ($waDigits === '') {
    $waDigits = '94770000000';
}

$quoteSelectOptions = \App\Models\QuoteRoute::activeOrdered();
if ($quoteSelectOptions === []) {
    $quoteSelectOptions = [
        ['slug' => 'usa-dhl', 'label' => 'USA – DHL', 'country' => 'USA', 'service' => 'DHL', 'price_per_kg' => '500'],
        ['slug' => 'uk-fedex', 'label' => 'United Kingdom – FedEx', 'country' => 'United Kingdom', 'service' => 'FedEx', 'price_per_kg' => '420'],
        ['slug' => 'ae-aramex', 'label' => 'UAE – Aramex', 'country' => 'UAE', 'service' => 'Aramex', 'price_per_kg' => '380'],
        ['slug' => 'lk-local', 'label' => 'Sri Lanka – Local', 'country' => 'Sri Lanka', 'service' => 'Local', 'price_per_kg' => '120'],
    ];
}
?>
<section class="apx-quote-widget-section py-5" id="get-quote" aria-labelledby="apx-quote-title">
  <div class="container">
    <div
      class="apx-quote-widget mx-auto"
      id="apx-quote-widget"
      data-whatsapp="<?= e($waDigits) ?>"
    >
      <div class="apx-quote-widget__card">
        <header class="apx-quote-widget__header">
          <h2 id="apx-quote-title" class="apx-quote-widget__title">Get a price quote</h2>
        </header>

        <form class="apx-quote-widget__form" novalidate>
          <div class="apx-quote-widget__row">
            <div class="apx-quote-widget__field">
              <label class="apx-quote-widget__label" for="apx-quote-route">Country &amp; service</label>
              <select id="apx-quote-route" class="apx-quote-widget__input apx-quote-widget__select" required>
                <option value="" disabled selected>Select country – service</option>
                <?php foreach ($quoteSelectOptions as $opt): ?>
                  <?php
                    $slug = (string) ($opt['slug'] ?? '');
                    if ($slug === '') {
                        continue;
                    }
                  ?>
                  <option
                    value="<?= e($slug) ?>"
                    data-country="<?= e((string) ($opt['country'] ?? '')) ?>"
                    data-service="<?= e((string) ($opt['service'] ?? '')) ?>"
                    data-price="<?= e((string) ($opt['price_per_kg'] ?? '0')) ?>"
                  ><?= e((string) ($opt['label'] ?? $slug)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="apx-quote-widget__field apx-quote-widget__field--qty">
              <label class="apx-quote-widget__label" for="apx-quote-qty">Quantity (kg)</label>
              <input
                id="apx-quote-qty"
                class="apx-quote-widget__input"
                type="number"
                inputmode="decimal"
                min="0.1"
                step="0.1"
                value="1"
                required
              />
            </div>
          </div>

          <div class="apx-quote-widget__field">
            <label class="apx-quote-widget__label" for="apx-quote-dealer">Dealer code <span class="apx-quote-widget__optional">(optional)</span></label>
            <input
              id="apx-quote-dealer"
              class="apx-quote-widget__input"
              type="text"
              autocomplete="off"
              placeholder="Enter dealer code to get dealer price"
            />
          </div>

          <div class="apx-quote-widget__actions">
            <button type="button" class="apx-quote-widget__btn-primary" id="apx-quote-calc">
              Get Total
            </button>
          </div>
        </form>

        <div
          class="apx-quote-widget__result"
          id="apx-quote-result"
          role="region"
          aria-labelledby="apx-quote-result-heading"
          aria-hidden="true"
          hidden
        >
          <h3 id="apx-quote-result-heading" class="visually-hidden">Quote summary</h3>
          <dl class="apx-quote-widget__summary">
            <div class="apx-quote-widget__summary-row">
              <dt>Country</dt>
              <dd id="apx-quote-out-country">—</dd>
            </div>
            <div class="apx-quote-widget__summary-row">
              <dt>Service</dt>
              <dd id="apx-quote-out-service">—</dd>
            </div>
            <div class="apx-quote-widget__summary-row">
              <dt>Total weight</dt>
              <dd id="apx-quote-out-weight">—</dd>
            </div>
            <div class="apx-quote-widget__summary-row">
              <dt>Price per kg</dt>
              <dd id="apx-quote-out-ppkg">—</dd>
            </div>
          </dl>
          <div class="apx-quote-widget__divider" role="presentation"></div>
          <div class="apx-quote-widget__total-row">
            <span class="apx-quote-widget__total-label">Total price</span>
            <span class="apx-quote-widget__total-value" id="apx-quote-out-total">—</span>
          </div>
        </div>

        <div class="apx-quote-widget__extras" id="apx-quote-extras" aria-hidden="true" hidden>
          <div class="apx-quote-widget__downloads" role="group" aria-label="Download quote">
            <button type="button" class="apx-quote-widget__btn-dl" id="apx-quote-dl-pdf" data-format="pdf">
              <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
              <span>Download PDF</span>
            </button>
            <button type="button" class="apx-quote-widget__btn-dl" id="apx-quote-dl-png" data-format="png">
              <i class="bi bi-file-image" aria-hidden="true"></i>
              <span>Download PNG</span>
            </button>
            <button type="button" class="apx-quote-widget__btn-dl" id="apx-quote-dl-txt" data-format="txt">
              <i class="bi bi-file-text" aria-hidden="true"></i>
              <span>Download Text</span>
            </button>
          </div>
          <a
            class="apx-quote-widget__wa"
            id="apx-quote-wa"
            href="#"
            target="_blank"
            rel="noopener noreferrer"
          >
            <i class="bi bi-whatsapp" aria-hidden="true"></i>
            <span>Send via WhatsApp</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
