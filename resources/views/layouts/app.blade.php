<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="#ffffff"
    >

    <title>
        @yield('title', 'Kasir KRJ')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('head')
</head>

<body
    class="
        min-h-screen
        bg-zinc-50
        text-zinc-900
        antialiased
        dark:bg-zinc-950
        dark:text-zinc-100
    "
>

    <div class="min-h-screen">

        @include('layouts.partials.navbar')

        <div class="flex">

            @include('layouts.partials.sidebar')

            <main
                class="
                    min-w-0
                    flex-1
                    px-4
                    py-5
                    pb-24
                    sm:px-6
                    lg:px-8
                    lg:pb-8
                "
            >

                @include('layouts.partials.flash')

                @yield('content')

            </main>

        </div>

        @include('layouts.partials.bottom-nav')

    </div>

    @stack('scripts')

</body>
</html>