<?php

declare(strict_types=1);

$token = (string) ($token ?? '');

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Set new password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(base_url('/css/style.css')) ?>" />
</head>
<body>
  <div class="container py-5" style="max-width:520px">
    <p class="mb-3"><a href="<?= e(base_url('/admin/login')) ?>" class="link-secondary text-decoration-none">← Back to login</a></p>
    <?php if (!empty($flashError)): ?>
      <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
    <?php endif; ?>

    <div class="card" style="border-radius:18px">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-3">Choose a new password</h1>
        <form method="post" action="<?= e(base_url('/admin/reset-password')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>" />
          <div class="mb-3">
            <label class="form-label">New password</label>
            <input class="form-control" name="password" type="password" required minlength="8" autocomplete="new-password" />
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm</label>
            <input class="form-control" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" />
          </div>
          <button class="btn btn-primary w-100" type="submit">Update password</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
