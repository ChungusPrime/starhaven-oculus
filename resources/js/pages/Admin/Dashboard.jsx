import { useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';

export default function Dashboard({ username }) {
    const logoutForm = useForm({});

    function logout(event) {
        event.preventDefault();
        logoutForm.post('/admin/logout');
    }

    return (
        <AppLayout pageTitle="Administration | Starhaven" heading="Starhaven Administration" subheading="// Command network" variant="admin">
            <main className="page-content">
                <section className="content-panel" aria-label="Administration dashboard">
                    <div className="status-panel">
                        <p>// Session authenticated</p>
                        <h2>Command network</h2>
                        <span>Authenticated as {username}. Add protected administration tools to this area.</span>
                        <form className="session-form" action="/admin/logout" method="post" onSubmit={logout}>
                            <button className="action-button" type="submit" disabled={logoutForm.processing}>
                                {logoutForm.processing ? '// Logging out...' : '// Log out'}
                            </button>
                        </form>
                    </div>
                </section>
            </main>
        </AppLayout>
    );
}