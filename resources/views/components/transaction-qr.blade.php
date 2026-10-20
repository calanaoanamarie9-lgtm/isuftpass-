@props([
    'title' => 'Transaction QR',
    'code' => '',
    'qr' => '',
])

<div class="mx-auto w-full max-w-xs bg-blue-950 bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 rounded-2xl p-6 flex flex-col items-center text-center relative overflow-hidden">
    <div class="absolute -top-16 -right-16 w-40 h-40 bg-blue-700/40 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative flex items-center gap-2">
        <x-brand-logo size="tiny" />
        <p class="font-extrabold text-white text-sm leading-tight">ISUFSTPASS</p>
    </div>

    <p class="relative mt-2 text-[10px] text-blue-300 uppercase tracking-wide">{{ $title }}</p>

    <p class="relative mt-3 text-lg font-black tracking-wider text-yellow-400 break-all">{{ $code }}</p>

    <div class="relative mt-3 bg-white rounded-xl p-4">
        <img src="{{ $qr }}" alt="{{ $title }} — {{ $code }}" class="w-36 h-36">
    </div>
</div>
