@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-6">
        <h1 class="text-xl font-semibold sm:text-2xl">
            Dashboard
        </h1>

        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Ringkasan operasional hari ini.
        </p>
    </div>


    <div
        class="
            grid
            grid-cols-2
            gap-3
            sm:grid-cols-2
            lg:grid-cols-4
        "
    >

        <div
            class="
                rounded-xl
                border
                border-zinc-200
                bg-white
                p-4
                dark:border-zinc-800
                dark:bg-zinc-900
            "
        >
            <p class="text-xs text-zinc-500">
                Booking
            </p>

            <p class="mt-2 text-2xl font-semibold">
                {{ $stats['booking']['total'] ?? 0 }}
            </p>
        </div>


        <div
            class="
                rounded-xl
                border
                border-zinc-200
                bg-white
                p-4
                dark:border-zinc-800
                dark:bg-zinc-900
            "
        >
            <p class="text-xs text-zinc-500">
                Sewa Aktif
            </p>

            <p class="mt-2 text-2xl font-semibold">
                {{ $stats['sewa']['aktif'] ?? 0 }}
            </p>
        </div>


        <div
            class="
                rounded-xl
                border
                border-zinc-200
                bg-white
                p-4
                dark:border-zinc-800
                dark:bg-zinc-900
            "
        >
            <p class="text-xs text-zinc-500">
                Tagihan
            </p>

            <p class="mt-2 text-2xl font-semibold">
                {{ $stats['tagihan']['belum_lunas'] ?? 0 }}
            </p>
        </div>


        <div
            class="
                rounded-xl
                border
                border-zinc-200
                bg-white
                p-4
                dark:border-zinc-800
                dark:bg-zinc-900
            "
        >
            <p class="text-xs text-zinc-500">
                Pendapatan
            </p>

            <p class="mt-2 text-xl font-semibold">
                Rp {{ number_format($stats['pembayaran']['total'] ?? 0, 0, ',', '.') }}
            </p>
        </div>

    </div>

</div>

@endsection