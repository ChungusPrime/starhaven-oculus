<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#030713">
    <meta name="description" content="Access the Starhaven Oculus Admin Network.">
    <title>Starhaven | Restricted</title>
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
                    <small>OCULUS ADMIN NETWORK</small>
                </span>
            </a>
            <div class="network-state" aria-label="Oculus network online">
                <span class="network-pulse"></span>NETWORK ONLINE
            </div>
        </header>

        <main class="register-main">

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
            <!-- </section> -->

            <section class="registration-panel" aria-label="Create account">
                <div class="panel-index">SH // REG-01</div>

                <div data-registration-form-container>
                    <div class="panel-heading">
                        <p>SECURE ADMINISTRATION</p>
                    </div>

                    <form class="registration-form" action="{{ route('admin.authenticate') }}" method="post">
                        @csrf
                        <div class="field-group">
                            <label for="username">// ADMINISTRATOR IDENTIFIER</label>
                            <div class="input-frame">
                                <span class="input-prefix" aria-hidden="true">ID</span>
                                <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="admin_identifier" required aria-describedby="username-error">
                            </div>
                            <p class="field-error" id="username-error" aria-live="polite">{{ $errors->first('username') }}</p>
                        </div>

                        <div class="field-group">
                            <label for="password">// ACCESS KEY</label>
                            <div class="input-frame">
                                <span class="input-prefix" aria-hidden="true">KEY</span>
                                <input id="password" name="password" type="password" autocomplete="current-password" placeholder="admin_access_key" required aria-describedby="password-error">
                                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password">SHOW</button>
                            </div>
                            <p class="field-error" id="password-error" aria-live="polite">{{ $errors->first('password') }}</p>
                        </div>

                        <div class="form-alert" data-form-alert role="alert" hidden></div>

                        <button class="submit-button" type="submit">
                            <span>// CONFIRM</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6" /></svg>
                        </button>
                    </form>

                    <p class="security-note">
                        <span aria-hidden="true">◇</span>
                        Credentials are encrypted and transmitted through the Oculus secure network.
                    </p>
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