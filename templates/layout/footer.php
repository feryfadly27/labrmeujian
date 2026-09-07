</main>
<?php if (isAuthenticated()): ?></div><?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<?php if (isAuthenticated()): ?>
<script nonce="<?= escapeHtml(cspNonce()) ?>">
	const sidebarFilter = document.querySelector('#sidebarFilter');
	if (sidebarFilter) {
		sidebarFilter.addEventListener('input', () => {
			const query = sidebarFilter.value.trim().toLowerCase();
			document.querySelectorAll('.sidebar-nav a').forEach((item) => {
				item.hidden = query !== '' && !item.dataset.menuLabel.includes(query);
			});
		});
	}

	document.querySelectorAll('select[data-target]').forEach((picker) => {
		picker.addEventListener('change', () => {
			const name = picker.value.trim();
			if (name === '') {
				return;
			}
			const textarea = document.getElementById(picker.dataset.target);
			if (!textarea) {
				return;
			}
			const existingLines = textarea.value.split('\n').map((line) => line.trim()).filter((line) => line !== '');
			if (!existingLines.includes(name)) {
				existingLines.push(name);
			}
			textarea.value = existingLines.join('\n');
			picker.value = '';
		});
	});

	document.querySelectorAll('form[data-confirm]').forEach((form) => {
		form.addEventListener('submit', (event) => {
			if (!window.confirm(form.dataset.confirm)) {
				event.preventDefault();
			}
		});
	});

	document.querySelectorAll('[data-auto-submit]').forEach((field) => {
		field.addEventListener('change', () => {
			field.form?.requestSubmit();
		});
	});
</script>
<?php endif; ?>
</body>
</html>
