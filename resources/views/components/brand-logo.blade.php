{{--
    The ISUFSTPASS seal, as a circle.

    The logo file is a round mark, but it was being dropped into square and
    rounded-square tiles, so the seal sat inside a box instead of reading as
    one round badge. The wrapper is the circle now - rounded and clipped -
    and the logo fills it, so a header, a card and a pass all carry the
    same mark whatever size they are printed at.

    size: tiny, xs, sm, md, lg, xl, 2xl, 3xl.
--}}

@props([
    'size' => 'md',
    'alt' => 'ISUFSTPASS',
])

@php
    $sizes = [
        'tiny' => 'w-7 h-7',
        'xs' => 'w-8 h-8',
        'sm' => 'w-9 h-9',
        'md' => 'w-10 h-10',
        'lg' => 'w-11 h-11',
        'xl' => 'w-12 h-12 sm:w-14 sm:h-14',
        '2xl' => 'w-14 h-14',
        '3xl' => 'w-16 h-16',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center shrink-0 overflow-hidden rounded-full bg-white shadow-sm '.($sizes[$size] ?? $sizes['md'])]) }}>

    <img src="{{ asset('img/isufstpass_circle_transparent.png') }}"
         alt="{{ $alt }}"
         class="w-full h-full object-contain">

</span>
