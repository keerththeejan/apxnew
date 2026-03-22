<?php

declare(strict_types=1);

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(base_url('/css/style.css')) ?>" />
</head>
<body>
  <div class="container py-5" style="max-width:520px">
    <p class="mb-3"><a href="<?= e(base_url('/admin/login')) ?>" class="link-secondary text-decoration-none">← Back to login</a></p>
    <?php if (!empty($flashSuccess)): ?>
      <div class="alert alert-success"><?= e((string) $flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
      <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
    <?php endif; ?>

    <div class="card" style="border-radius:18px">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-3">Reset admin password</h1>
        <p class="text-secondary small">Enter your admin email. If an account exists, we will send a reset link (also logged to <code>storage/logs/mail.log</code> when mail is not configured).</p>
        <form method="post" action="<?= e(base_url('/admin/forgot-password')) ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" required autocomplete="email" />
          </div>
          <button class="btn btn-primary w-100" type="submit">Send reset link</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
