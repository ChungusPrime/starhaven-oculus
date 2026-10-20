import { useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
import PasswordField from '../../components/PasswordField';

export default function Login({ username = '' }) {
    const form = useForm({
        username,
        password: '',
    });

    function updateField(event) {
        const { name, value } = event.target;

        form.setData(name, value);
        form.clearErrors(name);
    }

    function submit(event) {
        event.preventDefault();
        form.post('/admin', {
            preserveScroll: true,
            onError: () => form.reset('password'),
        });
    }

    const usernameError = form.errors.username ?? '';
    const passwordError = form.errors.password ?? '';

    return (
        <AppLayout pageTitle="Starhaven | Restricted" description="Access the Starhaven Oculus Admin Network." heading="SH Admin Console v0.4" subheading="// Elevated Access Required //" variant="admin">
            <main className="page-content">
                <section className="content-panel" aria-label="Administrator sign in">
                    <div>
                        <form className="form-layout" action="/admin" method="post" onSubmit={submit}>
                            <div className="form-field">
                                <label htmlFor="username">Username</label>
                                <div className={`input-control${usernameError ? ' is-invalid' : ''}`}>
                                    <span className="input-leading" aria-hidden="true">&gt;</span>
                                    <input
                                        id="username"
                                        name="username"
                                        type="text"
                                        value={form.data.username}
                                        autoComplete="off"
                                        autoCapitalize="none"
                                        spellCheck={false}
                                        required
                                        aria-describedby="username-error"
                                        aria-invalid={Boolean(usernameError)}
                                        onChange={updateField}
                                    />
                                </div>
                                <p className="field-error" id="username-error" aria-live="polite">{usernameError}</p>
                            </div>
                            <PasswordField id="password" label="Password" prefix=">" value={form.data.password} onChange={updateField} error={passwordError}  autoComplete="current-password" />
                            <button className="action-button" type="submit" disabled={form.processing}>
                                <span>{form.processing ? '// Signing in...' : '// Sign in'}</span>
                            </button>
                        </form>
                    </div>
                </section>
            </main>
        </AppLayout>
    );
}