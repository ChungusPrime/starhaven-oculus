<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#030713">
	<meta name="robots" content="noindex, nofollow">
	<title>Administration | Starhaven</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="register-page">
	<div class="register-shell">
		<div class="screen-chrome" aria-hidden="true">
			<span>OCULUS // ADMINISTRATION TERMINAL</span>
			<span class="screen-controls"><i></i><i></i><i></i></span>
		</div>
		<header class="site-header">
			<a class="brand" href="{{ route('admin.dashboard') }}" aria-label="Starhaven administration home">
				<svg class="brand-mark" viewBox="0 0 48 48" aria-hidden="true">
					<path d="M24 3 43 14v20L24 45 5 34V14L24 3Z" />
					<path d="m24 10 4.1 9.2L38 20l-7.5 6.6 2.3 9.6L24 31.1l-8.8 5.1 2.3-9.6L10 20l9.9-.8L24 10Z" />
					<circle cx="24" cy="24" r="3" />
				</svg>
				<span><strong>STARHAVEN</strong><small>OCULUS ADMIN NETWORK</small></span>
			</a>
			<form action="{{ route('admin.logout') }}" method="post">
				@csrf
				<button class="secondary-button admin-logout" type="submit">LOG OUT</button>
			</form>
		</header>
		<main class="admin-main">
			<p class="eyebrow"><span>02</span> SECURE ADMINISTRATION</p>
			<h1>Command network</h1>
			<p class="intro-copy">Authenticated as {{ session('admin.username') }}. Add protected administration tools to this area.</p>
		</main>
		<footer class="site-footer">
			<span>RESTRICTED ACCESS</span>
			<span>SESSION AUTHENTICATED</span>
		</footer>
	</div>
</body>
</html>
