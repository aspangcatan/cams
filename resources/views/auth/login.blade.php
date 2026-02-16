<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CAMS Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["Space Grotesk", "ui-sans-serif", "sans-serif"],
                    },
                    colors: {
                        ink: "#0B132B",
                        dawn: "#1C2541",
                        mint: "#3A506B",
                        tide: "#5BC0BE",
                        mist: "#E9F7F6"
                    },
                    boxShadow: {
                        premium: "0 24px 48px rgba(8, 15, 33, 0.22)"
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_right,_#b8ece8_0%,_#eaf8f7_34%,_#ffffff_70%)] font-sans text-ink">
    <div class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 md:px-8">
        <div class="grid w-full gap-8 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="hidden rounded-3xl border border-white/70 bg-gradient-to-br from-ink via-dawn to-mint p-10 text-white shadow-premium lg:block">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-tide">CAMS Platform</p>
                <h1 class="mt-4 text-4xl font-bold leading-tight">Centralized User and Access Administration</h1>
                <p class="mt-4 text-sm text-slate-100/90">Securely manage users, roles, systems, and approvals in one premium workspace.</p>
                <div class="mt-10 grid gap-4 text-sm">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">Unified CRUD modules for HR and access control</div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">Searchable assignments and role-based provisioning</div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">Modern responsive operations dashboard</div>
                </div>
            </section>

            <section class="rounded-3xl border border-white/70 bg-white/85 p-6 shadow-premium backdrop-blur-md md:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-mint">Welcome Back</p>
                <h2 class="mt-2 text-2xl font-bold md:text-3xl">Sign in to CAMS</h2>
                <p class="mt-2 text-sm text-slate-500">Use your account credentials to continue.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold text-slate-600">Username</span>
                        <input type="text" name="username" value="{{ old('username') }}" required autofocus class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none ring-tide transition focus:ring-2">
                    </label>

                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold text-slate-600">Password</span>
                        <input type="password" name="password" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none ring-tide transition focus:ring-2">
                    </label>

                    <button type="submit" class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-dawn">
                        Sign In
                    </button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
