<?php

declare(strict_types=1);

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= e(base_url('/css/style.css')) ?>" />
</head>
<body>
  <div class="container py-5" style="max-width:520px">
    <p class="mb-3"><a href="<?= e(base_url('/')) ?>" class="link-secondary text-decoration-none">← Back to website</a></p>
    <?php if (!empty($flashError)): ?>
      <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
    <?php endif; ?>

    <div class="card" style="border-radius:18px">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-3">Admin Login</h1>
        <form method="post" action="<?= e(base_url('/admin/login')) ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" name="password" type="password" required />
          </div>
          <button class="btn btn-primary w-100" type="submit">Sign in</button>
          <p class="mt-3 mb-0 small text-center"><a href="<?= e(base_url('/admin/forgot-password')) ?>" class="link-secondary">Forgot password?</a></p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
