import { useEffect, useRef, useState } from 'react';
import AppLayout from '../components/AppLayout';
import PasswordField from '../components/PasswordField';

const initialFields = {
    username: '',
    password: '',
    password_confirmation: '',
};

function firstError(errors, field) {
    const messages = errors[field];

    return Array.isArray(messages) ? messages[0] : (messages ?? '');
}

export default function Register() {
    const [fields, setFields] = useState(initialFields);
    const [errors, setErrors] = useState({});
    const [formError, setFormError] = useState('');
    const [processing, setProcessing] = useState(false);
    const [registered, setRegistered] = useState(false);
    const usernameInput = useRef(null);
    const successState = useRef(null);
    const focusUsernameAfterReset = useRef(false);

    const passwordStrength = [
        fields.password.length >= 8,
        /[A-Z]/.test(fields.password) && /[a-z]/.test(fields.password),
        /\d|[^A-Za-z0-9]/.test(fields.password),
    ].filter(Boolean).length;
    const strengthLabels = ['8+ characters', 'Basic', 'Good', 'Strong'];

    useEffect(() => {
        if (registered) {
            successState.current?.focus();
            return;
        }

        if (focusUsernameAfterReset.current) {
            focusUsernameAfterReset.current = false;
            usernameInput.current?.focus();
        }
    }, [registered]);

    function updateField(event) {
        const { name, value } = event.target;

        setFields((current) => ({ ...current, [name]: value }));
        setErrors((current) => {
            const nextErrors = { ...current };
            delete nextErrors[name];
            return nextErrors;
        });
    }

    async function submit(event) {
        event.preventDefault();
        setErrors({});
        setFormError('');

        if (!event.currentTarget.checkValidity()) {
            event.currentTarget.reportValidity();
            return;
        }

        setProcessing(true);

        try {
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(fields),
            });
            const payload = await response.json();

            if (response.status === 422) {
                setErrors(payload.errors ?? {});
                return;
            }

            if (!response.ok) {
                throw new Error('Registration request failed');
            }

            setFields({ ...initialFields });
            setRegistered(true);
        } catch {
            setFormError('The identity network could not be reached. Please try again.');
        } finally {
            setProcessing(false);
        }
    }

    function registerAnother() {
        setErrors({});
        setFormError('');
        setFields({ ...initialFields });
        focusUsernameAfterReset.current = true;
        setRegistered(false);
    }

    const usernameError = firstError(errors, 'username');
    const passwordError = firstError(errors, 'password');
    const confirmationError = firstError(errors, 'password_confirmation');

    return (
        <AppLayout
            pageTitle="Create your account | Starhaven"
            description="Create an account for Starhaven."
            heading="Starhaven Pioneer Initiative"
            subheading="// New Operator //"
            variant="public"
        >
            <main className="page-content">
                        <section className="content-panel" aria-label="Create account">
                            {!registered ? (
                                <div>
                                    <div className="content-heading">
                                        <h2>Create account</h2>
                                    </div>

                                    <form className="form-layout" action="/api/register" method="post" noValidate onSubmit={submit}>
                                        <div className="form-field">
                                            <label htmlFor="username">Username</label>
                                            <div className={`input-control${usernameError ? ' is-invalid' : ''}`}>
                                                <span className="input-leading" aria-hidden="true">&gt;</span>
                                                <input
                                                    ref={usernameInput}
                                                    id="username"
                                                    name="username"
                                                    type="text"
                                                    value={fields.username}
                                                    minLength={3}
                                                    maxLength={32}
                                                    pattern="[A-Za-z0-9_]+"
                                                    autoComplete="off"
                                                    autoCapitalize="none"
                                                    spellCheck={false}
                                                    required
                                                    aria-describedby="username-help username-error"
                                                    aria-invalid={Boolean(usernameError)}
                                                    onChange={updateField}
                                                />
                                            </div>
                                            <div className="field-details">
                                                <span id="username-help">3-32 letters, numbers, or underscores</span>
                                                <span>{fields.username.length} / 32</span>
                                            </div>
                                            <p className="field-error" id="username-error" aria-live="polite">{usernameError}</p>
                                        </div>

                                        <PasswordField
                                            id="password"
                                            label="Password"
                                            prefix=">"
                                            value={fields.password}
                                            autoComplete="off"
                                            onChange={updateField}
                                            error={passwordError}
                                            minLength={8}
                                            descriptionId="password-strength"
                                        >
                                            <div className="field-meter" id="password-strength">
                                                {[0, 1, 2].map((index) => (
                                                    <span className={index < passwordStrength ? 'is-active' : ''} key={index} />
                                                ))}
                                                <small>{strengthLabels[passwordStrength]}</small>
                                            </div>
                                        </PasswordField>

                                        <PasswordField
                                            id="password_confirmation"
                                            label="Confirm password"
                                            prefix=">"
                                            value={fields.password_confirmation}
                                            onChange={updateField}
                                            error={confirmationError}
                                            visibilityLabel="password confirmation"
                                        />

                                        {formError && <div className="form-alert" role="alert">{formError}</div>}

                                        <button className="action-button" type="submit" disabled={processing}>
                                            <span>{processing ? '// Creating account...' : '// Create account'}</span>
                                        </button>
                                    </form>
                                </div>
                            ) : (
                                <div className="status-panel" ref={successState} tabIndex={-1}>
                                    <p>// Registration complete</p>
                                    <h2>Account created</h2>
                                    <span>Use your new credentials to log in to Starhaven.</span>
                                    <button className="action-button" type="button" onClick={registerAnother}>
                                        // Register another account
                                    </button>
                                </div>
                            )}
                        </section>
            </main>
        </AppLayout>
    );
}