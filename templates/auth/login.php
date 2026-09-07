<div class="login-page">
	<section class="card login-card">
		<div class="card-body p-4 p-md-5">
			<div class="login-brand mb-2">JADWAL LAB</div>
			<h1 class="h3 mb-1"><?= escapeHtml($pageTitle) ?></h1>
			<p class="text-secondary mb-4">Kelola jadwal dan workstation ujian.</p>
			<?php if ($error !== null): ?>
				<div class="alert alert-danger" role="alert"><?= escapeHtml($error) ?></div>
			<?php endif; ?>
			<form method="post" action="login.php" autocomplete="off">
				<input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
				<div class="mb-3">
					<label class="form-label" for="username">Username</label>
					<input class="form-control" id="username" name="username" type="text" value="<?= escapeHtml($username) ?>" required autofocus>
				</div>
				<div class="mb-4">
					<label class="form-label" for="password">Password</label>
					<input class="form-control" id="password" name="password" type="password" required>
				</div>
				<button class="btn btn-success w-100" type="submit">Masuk</button>
			</form>
		</div>
	</section>
</div>
