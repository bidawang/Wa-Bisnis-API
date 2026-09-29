<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Kasir KRJ</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body
    class="
        min-h-screen
        bg-zinc-50
        text-zinc-900
        dark:bg-zinc-950
        dark:text-zinc-100
    "
>

    <main
        class="
            flex
            min-h-screen
            items-center
            justify-center
            px-4
        "
    >

        <div class="w-full max-w-sm">

            <div class="mb-8 text-center">

                <h1 class="text-2xl font-bold">
                    Kasir KRJ
                </h1>

                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Masuk ke panel admin
                </p>

            </div>


            @if(session('success'))
                <div
                    class="
                        mb-4
                        rounded-lg
                        bg-green-50
                        px-4
                        py-3
                        text-sm
                        text-green-700
                        dark:bg-green-950
                        dark:text-green-300
                    "
                >
                    {{ session('success') }}
                </div>
            @endif


            @if($errors->any())
                <div
                    class="
                        mb-4
                        rounded-lg
                        bg-red-50
                        px-4
                        py-3
                        text-sm
                        text-red-700
                        dark:bg-red-950
                        dark:text-red-300
                    "
                >
                    {{ $errors->first() }}
                </div>
            @endif


            <form
                method="POST"
                action="{{ route('login.process') }}"
                class="
                    rounded-2xl
                    border
                    border-zinc-200
                    bg-white
                    p-6
                    shadow-sm
                    dark:border-zinc-800
                    dark:bg-zinc-900
                "
            >

                @csrf


                <div class="space-y-5">

                    <div>
                        <label
                            for="email"
                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "
                        >
                            Email
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                            class="
                                w-full
                                rounded-lg
                                border
                                border-zinc-300
                                bg-white
                                px-3
                                py-2.5
                                text-sm
                                outline-none
                                focus:border-zinc-500
                                dark:border-zinc-700
                                dark:bg-zinc-950
                            "
                        >
                    </div>


                    <div>
                        <label
                            for="password"
                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="
                                w-full
                                rounded-lg
                                border
                                border-zinc-300
                                bg-white
                                px-3
                                py-2.5
                                text-sm
                                outline-none
                                focus:border-zinc-500
                                dark:border-zinc-700
                                dark:bg-zinc-950
                            "
                        >
                    </div>


                    <button
                        type="submit"
                        class="
                            w-full
                            rounded-lg
                            bg-zinc-900
                            px-4
                            py-2.5
                            text-sm
                            font-medium
                            text-white
                            transition
                            hover:bg-zinc-800
                            dark:bg-white
                            dark:text-zinc-900
                            dark:hover:bg-zinc-200
                        "
                    >
                        Masuk
                    </button>

                </div>

            </form>

        </div>

    </main>

</body>

</html>