<x-app-layout>

    {{--
        Availability for whichever office or department is signed in.

        This page once managed per-date open/closed days and bookable slots.
        Slots are gone — a booking names a day and the office answers with
        the window it wants the student in when it approves — so all that
        remains is the office's hours.
    --}}
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">{{ $office }} Availability</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Set the hours {{ $office }} keeps. An approval may only name a time inside these hours.
                </p>
            </div>

            <x-office-hours :office="$office" />

        </div>
    </div>

</x-app-layout>
