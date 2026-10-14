@props(['user'])

@php
    $type  = $user?->classification() ?? 'student';
    $label = $user?->classificationLabel() ?? 'Student';

    // One colour per classification so staff can spot the applicant type at a glance.
    $colors = match ($type) {
        'alumni' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'parent' => 'bg-purple-100 text-purple-700 border-purple-200',
        'guest'  => 'bg-orange-100 text-orange-700 border-orange-200',
        default  => 'bg-blue-100 text-blue-700 border-blue-200',
    };
@endphp

<span data-classification="{{ $type }}"
      {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-md border text-[10px] font-extrabold uppercase tracking-wider $colors"]) }}>
    {{ $label }}
</span>
