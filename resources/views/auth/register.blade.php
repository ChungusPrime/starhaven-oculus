<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#030713">
    <meta name="description" content="Create an account for Starhaven.">
    <title>Create your account | Starhaven</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="register-page">
    <div class="register-shell">
        <div class="screen-chrome" aria-hidden="true">
            <span>OCULUS // IDENTITY TERMINAL</span>
            <span class="screen-controls"><i></i><i></i><i></i></span>
        </div>
        <header class="site-header">
            <a class="brand" href="/" aria-label="Starhaven home">
                <svg class="brand-mark" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M24 3 43 14v20L24 45 5 34V14L24 3Z" />
                    <path d="m24 10 4.1 9.2L38 20l-7.5 6.6 2.3 9.6L24 31.1l-8.8 5.1 2.3-9.6L10 20l9.9-.8L24 10Z" />
                    <circle cx="24" cy="24" r="3" />
                </svg>
                <span>
                    <strong>STARHAVEN</strong>
                    <small>OCULUS IDENTITY NETWORK</small>
                </span>
            </a>
            <div class="network-state" aria-label="Oculus network online">
                <span class="network-pulse"></span>
                NETWORK ONLINE
            </div>
        </header>

        <main class="register-main">
            <section class="register-intro" aria-labelledby="registration-title">
                <p class="eyebrow"><span>01</span> NEW OPERATOR REGISTRATION</p>
                <h1 id="registration-title">Your journey starts beyond the rim.</h1>
                <p class="intro-copy">Create the account you'll use to enter Starhaven. Your callsign is your permanent identity across the game network.</p>

                <dl class="identity-notes">
                    <div>
                        <dt>ACCOUNT TYPE</dt>
                        <dd>Starhaven Operator ID</dd>
                    </div>
                    <div>
                        <dt>NETWORK</dt>
                        <dd>Oculus Secure Link</dd>
                    </div>
                </dl>
            </section>

            <section class="registration-panel" aria-label="Create account">
                <div class="panel-index">SH // REG-01</div>

                <div data-registration-form-container>
                    <div class="panel-heading">
                        <p>ENLISTMENT CONSOLE</p>
                        <h2>Create operator ID</h2>
                        <span>All fields are required.</span>
                    </div>

                    <form class="registration-form" data-registration-form action="/api/auth/register" method="post" novalidate>
                        <div class="field-group">
                            <label for="username">CALLSIGN</label>
                            <div class="input-frame">
                                <span class="input-prefix" aria-hidden="true">ID</span>
                                <input id="username" name="username" type="text" minlength="3" maxlength="32" pattern="[A-Za-z0-9_]+" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="choose_your_callsign" required aria-describedby="username-help username-error">
                            </div>
                            <div class="field-meta">
                                <span id="username-help">3–32 letters, numbers, or underscores</span>
                                <span data-counter>0 / 32</span>
                            </div>
                            <p class="field-error" id="username-error" data-error-for="username" aria-live="polite"></p>
                        </div>

                        <div class="field-group">
                            <label for="password">ACCESS KEY</label>
                            <div class="input-frame">
                                <span class="input-prefix" aria-hidden="true">KEY</span>
                                <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Enter a secure password" required aria-describedby="password-strength password-error">
                                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password">SHOW</button>
                            </div>
                            <div class="strength-meter" id="password-strength">
                                <span data-strength-bar></span>
                                <span data-strength-bar></span>
                                <span data-strength-bar></span>
                                <small data-strength-label>8+ characters</small>
                            </div>
                            <p class="field-error" id="password-error" data-error-for="password" aria-live="polite"></p>
                        </div>

                        <div class="field-group">
                            <label for="password_confirmation">CONFIRM ACCESS KEY</label>
                            <div class="input-frame">
                                <span class="input-prefix" aria-hidden="true">KEY</span>
                                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" placeholder="Repeat your password" required aria-describedby="password_confirmation-error">
                                <button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password confirmation">SHOW</button>
                            </div>
                            <p class="field-error" id="password_confirmation-error" data-error-for="password_confirmation" aria-live="polite"></p>
                        </div>

                        <div class="form-alert" data-form-alert role="alert" hidden></div>

                        <button class="submit-button" type="submit" data-submit-button>
                            <span data-submit-label>CREATE ACCOUNT</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                        </button>
                    </form>

                    <p class="security-note">
                        <span aria-hidden="true">◇</span>
                        Credentials are encrypted and transmitted through the Oculus secure network.
                    </p>
                </div>

                <div class="success-state" data-registration-success hidden tabindex="-1">
                    <div class="success-mark" aria-hidden="true">✓</div>
                    <p>IDENTITY CONFIRMED</p>
                    <h2>Operator ID created.</h2>
                    <span>Your Starhaven account is ready. Use your new credentials when you launch the game.</span>
                    <button class="secondary-button" type="button" data-register-another>REGISTER ANOTHER ID</button>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <span>OCULUS AUTHENTICATION SYSTEM</span>
            <span>STARHAVEN // BUILD YOUR LEGACY</span>
        </footer>
    </div>
</body>
</html>