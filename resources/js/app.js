const registrationForm = document.querySelector('[data-registration-form]');

if (registrationForm) {
	const usernameInput = registrationForm.querySelector('#username');
	const passwordInput = registrationForm.querySelector('#password');
	const counter = registrationForm.querySelector('[data-counter]');
	const submitButton = registrationForm.querySelector('[data-submit-button]');
	const submitLabel = registrationForm.querySelector('[data-submit-label]');
	const formAlert = registrationForm.querySelector('[data-form-alert]');
	const formContainer = document.querySelector('[data-registration-form-container]');
	const successState = document.querySelector('[data-registration-success]');

	const clearErrors = () => {
		registrationForm.querySelectorAll('[data-error-for]').forEach((error) => {
			error.textContent = '';
		});
		registrationForm.querySelectorAll('.input-frame').forEach((frame) => {
			frame.classList.remove('has-error');
		});
		formAlert.hidden = true;
	};

	const showErrors = (errors) => {
		Object.entries(errors).forEach(([field, messages]) => {
			const error = registrationForm.querySelector(`[data-error-for="${field}"]`);
			if (!error) return;

			error.textContent = messages[0];
			error.closest('.field-group')?.querySelector('.input-frame')?.classList.add('has-error');
		});
	};

	usernameInput.addEventListener('input', () => {
		counter.textContent = `${usernameInput.value.length} / 32`;
	});

	passwordInput.addEventListener('input', () => {
		const value = passwordInput.value;
		const score = [value.length >= 8, /[A-Z]/.test(value) && /[a-z]/.test(value), /\d|[^A-Za-z0-9]/.test(value)].filter(Boolean).length;
		const labels = ['8+ characters', 'Basic', 'Good', 'Strong'];

		registrationForm.querySelectorAll('[data-strength-bar]').forEach((bar, index) => {
			bar.classList.toggle('active', index < score);
		});
		registrationForm.querySelector('[data-strength-label]').textContent = labels[score];
	});

	registrationForm.addEventListener('submit', async (event) => {
		event.preventDefault();
		clearErrors();

		if (!registrationForm.checkValidity()) {
			registrationForm.reportValidity();
			return;
		}

		submitButton.disabled = true;
		submitLabel.textContent = 'CREATING ID...';

		try {
			const formData = new FormData(registrationForm);
			const response = await fetch(registrationForm.action, {
				method: 'POST',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(Object.fromEntries(formData)),
			});
			const payload = await response.json();

			if (response.status === 422) {
				showErrors(payload.errors ?? {});
				return;
			}

			if (!response.ok) {
				throw new Error('Registration request failed');
			}

			registrationForm.reset();
			formContainer.hidden = true;
			successState.hidden = false;
			successState.focus();
		} catch {
			formAlert.textContent = 'The identity network could not be reached. Please try again.';
			formAlert.hidden = false;
		} finally {
			submitButton.disabled = false;
			submitLabel.textContent = 'CREATE ACCOUNT';
		}
	});

	document.querySelector('[data-register-another]').addEventListener('click', () => {
		successState.hidden = true;
		formContainer.hidden = false;
		counter.textContent = '0 / 32';
		registrationForm.querySelectorAll('[data-strength-bar]').forEach((bar) => bar.classList.remove('active'));
		registrationForm.querySelector('[data-strength-label]').textContent = '8+ characters';
		usernameInput.focus();
	});
}

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
	toggle.addEventListener('click', () => {
		const input = document.getElementById(toggle.dataset.passwordToggle);
		const showPassword = input.type === 'password';
		input.type = showPassword ? 'text' : 'password';
		toggle.textContent = showPassword ? 'HIDE' : 'SHOW';
		toggle.setAttribute('aria-label', `${showPassword ? 'Hide' : 'Show'} ${input.id === 'password' ? 'password' : 'password confirmation'}`);
	});
});
