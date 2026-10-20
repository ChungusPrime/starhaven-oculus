import { Head } from '@inertiajs/react';

export default function AppLayout({
    pageTitle,
    description,
    heading,
    subheading,
    variant = 'public',
    children,
}) {
    const variantClass = variant === 'admin' ? 'app-page--admin' : 'app-page--public';

    return (
        <>
            <Head title={pageTitle}>
                <meta name="theme-color" content="#000000" />
                <meta name="robots" content="noindex, nofollow" />
                {description && <meta name="description" content={description} />}
            </Head>

            <div className={`app-page ${variantClass}`}>
                <div className="page-shell">
                    <header className="page-header">
                        <h1>{heading}</h1>
                        <p>{subheading}</p>
                    </header>

                    {children}
                </div>
            </div>
        </>
    );
}