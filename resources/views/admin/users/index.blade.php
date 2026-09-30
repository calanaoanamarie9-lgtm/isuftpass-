<x-app-layout>

    <div class="min-h-screen bg-[#f5f8fc] py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- =========================================================
                PAGE HEADER
            ========================================================== --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 mb-7">

                <div class="flex items-center gap-4">

                    {{-- PAGE ICON --}}
                    <div class="w-14 h-14 rounded-2xl
                                bg-blue-700
                                flex items-center justify-center
                                shadow-sm">

                        <svg class="w-7 h-7 text-white"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H3v-2a4 4 0 014-4h3a4 4 0 014 4v2zm-5-8a4 4 0 100-8 4 4 0 000 8zm9-4a3 3 0 100-6 3 3 0 000 6z"/>

                        </svg>

                    </div>

                    <div>

                        <h1 class="text-2xl sm:text-3xl
                                   font-extrabold
                                   text-[#102d5b]">

                            User Management

                        </h1>

                        <p class="mt-1 text-sm text-gray-500">

                            Manage registered users and account access.

                        </p>

                    </div>

                </div>


                {{-- ACCOUNT ADMINISTRATION --}}
                <div class="inline-flex items-center gap-2
                            bg-white
                            border border-gray-200
                            rounded-full
                            px-4 py-2
                            text-sm
                            font-medium
                            text-gray-600
                            shadow-sm">

                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>

                    Account Administration

                </div>

            </div>


            {{-- =========================================================
                SUCCESS MESSAGE
            ========================================================== --}}
            @if (session('status'))

                <div class="mb-5
                            flex items-center gap-3
                            bg-green-50
                            border border-green-200
                            rounded-xl
                            px-4 py-3
                            text-sm text-green-700">

                    <div class="w-7 h-7 rounded-full
                                bg-green-100
                                flex items-center justify-center
                                font-bold">

                        ✓

                    </div>

                    {{ session('status') }}

                </div>

            @endif


            {{-- =========================================================
                ERROR MESSAGE
            ========================================================== --}}
            @if (session('error'))

                <div class="mb-5
                            flex items-center gap-3
                            bg-red-50
                            border border-red-200
                            rounded-xl
                            px-4 py-3
                            text-sm text-red-700">

                    <div class="w-7 h-7 rounded-full
                                bg-red-100
                                flex items-center justify-center
                                font-bold">

                        !

                    </div>

                    {{ session('error') }}

                </div>

            @endif


            {{-- =========================================================
                VALIDATION ERRORS
            ========================================================== --}}
            @if ($errors->any())

                <div class="mb-5
                            bg-red-50
                            border border-red-200
                            rounded-xl
                            px-5 py-4
                            text-sm text-red-700">

                    <p class="font-semibold mb-2">
                        Please correct the following:
                    </p>

                    <ul class="list-disc list-inside space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- =========================================================
                STATISTICS
            ========================================================== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">


                {{-- TOTAL ACCOUNTS --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative
                            overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs
                                      font-bold
                                      uppercase
                                      tracking-wide
                                      text-gray-400">

                                Total Accounts

                            </p>

                            <p class="mt-2
                                      text-3xl
                                      font-extrabold
                                      text-[#102d5b]">

                                {{ $users->total() }}

                            </p>

                        </div>


                        <div class="w-12 h-12
                                    rounded-full
                                    bg-blue-50
                                    text-blue-700
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H3v-2a4 4 0 014-4h3a4 4 0 014 4v2zm-5-8a4 4 0 100-8 4 4 0 000 8zm9-4a3 3 0 100-6 3 3 0 000 6z"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-600"></div>

                </div>


                {{-- ACTIVE USERS --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative
                            overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs
                                      font-bold
                                      uppercase
                                      tracking-wide
                                      text-gray-400">

                                Active Users

                            </p>

                            <p class="mt-2
                                      text-3xl
                                      font-extrabold
                                      text-[#102d5b]">

                                {{ $users->where('is_active', true)->count() }}

                            </p>

                        </div>


                        <div class="w-12 h-12
                                    rounded-full
                                    bg-green-50
                                    text-green-600
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5 13l4 4L19 7"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-green-500"></div>

                </div>


                {{-- STUDENTS --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative
                            overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs
                                      font-bold
                                      uppercase
                                      tracking-wide
                                      text-gray-400">

                                Students

                            </p>

                            <p class="mt-2
                                      text-3xl
                                      font-extrabold
                                      text-[#102d5b]">

                                {{ $users->where('role', \App\Models\User::ROLE_STUDENT)->count() }}

                            </p>

                        </div>


                        <div class="w-12 h-12
                                    rounded-full
                                    bg-yellow-50
                                    text-yellow-600
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M12 14l9-5-9-5-9 5 9 5z"/>

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M5 12v4c0 1.5 3.1 3 7 3s7-1.5 7-3v-4"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-yellow-400"></div>

                </div>


                {{-- OFFICE ACCOUNTS --}}
                <div class="bg-white
                            rounded-2xl
                            border border-gray-200
                            shadow-sm
                            p-5
                            relative
                            overflow-hidden">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-xs
                                      font-bold
                                      uppercase
                                      tracking-wide
                                      text-gray-400">

                                Office Accounts

                            </p>

                            <p class="mt-2
                                      text-3xl
                                      font-extrabold
                                      text-[#102d5b]">

                                {{
                                    $users->whereIn('role', [
                                        \App\Models\User::ROLE_REGISTRAR,
                                        \App\Models\User::ROLE_CASHIER,
                                        \App\Models\User::ROLE_DEPARTMENT,
                                        \App\Models\User::ROLE_ADMIN
                                    ])->count()
                                }}

                            </p>

                        </div>


                        <div class="w-12 h-12
                                    rounded-full
                                    bg-purple-50
                                    text-purple-600
                                    flex items-center justify-center">

                            <svg class="w-6 h-6"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-5h6v5M9 10h.01M15 10h.01M9 13h.01M15 13h.01"/>

                            </svg>

                        </div>

                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-purple-500"></div>

                </div>

            </div>


            {{-- =========================================================
                SEARCH / FILTER
            ========================================================== --}}
            <form method="GET"
                  action="{{ route('admin.users.index') }}"
                  class="bg-white
                         rounded-2xl
                         border border-gray-200
                         shadow-sm
                         p-5
                         mb-6">

                <div class="grid grid-cols-1 lg:grid-cols-[1fr_200px_200px_auto]
                            gap-4 items-end">


                    {{-- SEARCH --}}
                    <div>

                        <label class="block text-xs
                                      font-bold
                                      text-gray-500
                                      mb-2">

                            Search

                        </label>

                        <div class="relative">

                            <div class="absolute inset-y-0 left-0
                                        pl-4
                                        flex items-center
                                        pointer-events-none">

                                <svg class="w-5 h-5 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="m21 21-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>

                                </svg>

                            </div>

                            <input type="text"
                                   name="q"
                                   value="{{ $search }}"
                                   placeholder="Search by name or email..."
                                   class="w-full h-12
                                          rounded-xl
                                          border-gray-200
                                          bg-gray-50
                                          pl-11
                                          pr-4
                                          text-sm
                                          focus:bg-white
                                          focus:border-blue-600
                                          focus:ring-blue-600">

                        </div>

                    </div>


                    {{-- ROLE --}}
                    <div>

                        <label class="block text-xs
                                      font-bold
                                      text-gray-500
                                      mb-2">

                            Role

                        </label>

                        <select name="role"
                                class="w-full h-12
                                       rounded-xl
                                       border-gray-200
                                       bg-gray-50
                                       text-sm
                                       focus:bg-white
                                       focus:border-blue-600
                                       focus:ring-blue-600">

                            <option value="">
                                All roles
                            </option>

                            <option value="{{ \App\Models\User::ROLE_STUDENT }}"
                                    @selected($roleFilter === \App\Models\User::ROLE_STUDENT)>
                                Student
                            </option>

                            <option value="{{ \App\Models\User::ROLE_REGISTRAR }}"
                                    @selected($roleFilter === \App\Models\User::ROLE_REGISTRAR)>
                                Registrar
                            </option>

                            <option value="{{ \App\Models\User::ROLE_CASHIER }}"
                                    @selected($roleFilter === \App\Models\User::ROLE_CASHIER)>
                                Cashier
                            </option>

                            <option value="{{ \App\Models\User::ROLE_DEPARTMENT }}"
                                    @selected($roleFilter === \App\Models\User::ROLE_DEPARTMENT)>
                                Department / Office
                            </option>

                            <option value="{{ \App\Models\User::ROLE_ADMIN }}"
                                    @selected($roleFilter === \App\Models\User::ROLE_ADMIN)>
                                Administrator
                            </option>

                        </select>

                    </div>


                    <div>

                        <label class="block text-xs
                                      font-bold
                                      text-gray-500
                                      mb-2">

                            Approval

                        </label>

                        <select name="approval"
                                class="w-full h-12
                                       rounded-xl
                                       border-gray-200
                                       bg-gray-50
                                       text-sm
                                       focus:bg-white
                                       focus:border-blue-600
                                       focus:ring-blue-600">

                            <option value="">
                                All statuses
                            </option>

                            <option value="{{ \App\Models\User::APPROVAL_PENDING }}"
                                    @selected($approvalFilter === \App\Models\User::APPROVAL_PENDING)>
                                Pending ({{ $pendingCount }})
                            </option>

                            <option value="{{ \App\Models\User::APPROVAL_APPROVED }}"
                                    @selected($approvalFilter === \App\Models\User::APPROVAL_APPROVED)>
                                Approved
                            </option>

                            <option value="{{ \App\Models\User::APPROVAL_REJECTED }}"
                                    @selected($approvalFilter === \App\Models\User::APPROVAL_REJECTED)>
                                Rejected
                            </option>

                        </select>

                    </div>


                    {{-- BUTTONS --}}
                    <div class="flex gap-2">

                        <button type="submit"
                                class="h-12
                                       px-6
                                       rounded-xl
                                       bg-blue-700
                                       hover:bg-blue-800
                                       text-white
                                       text-sm
                                       font-semibold
                                       inline-flex
                                       items-center
                                       justify-center
                                       gap-2
                                       transition">

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m21 21-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>

                            </svg>

                            Search

                        </button>


                        @if ($search || $roleFilter || $approvalFilter)

                            <a href="{{ route('admin.users.index') }}"
                               class="h-12
                                      px-5
                                      rounded-xl
                                      border border-gray-200
                                      bg-white
                                      text-gray-600
                                      text-sm
                                      font-semibold
                                      inline-flex
                                      items-center
                                      justify-center
                                      hover:bg-gray-50
                                      transition">

                                Reset

                            </a>

                        @endif

                    </div>

                </div>

            </form>


            {{-- =========================================================
                SYSTEM ACCOUNTS
            ========================================================== --}}
            <div class="bg-white
                        rounded-2xl
                        border border-gray-200
                        shadow-sm
                        overflow-hidden">


                {{-- TABLE HEADER --}}
                <div class="px-6 py-5
                            border-b border-gray-100
                            flex items-center
                            justify-between">

                    <div class="flex items-center gap-3">

                        <div class="w-10 h-10
                                    rounded-xl
                                    bg-blue-50
                                    text-blue-700
                                    flex items-center justify-center">

                            <svg class="w-5 h-5"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H3v-2a4 4 0 014-4h3a4 4 0 014 4v2zm-5-8a4 4 0 100-8 4 4 0 000 8zm9-4a3 3 0 100-6 3 3 0 000 6z"/>

                            </svg>

                        </div>

                        <div>

                            <h2 class="text-lg font-bold text-[#102d5b]">
                                System Accounts
                            </h2>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Registered ISUFSTPASS users
                            </p>

                        </div>

                    </div>


                    <span class="hidden sm:inline-flex
                                 items-center
                                 px-3 py-1.5
                                 rounded-full
                                 bg-blue-50
                                 text-blue-700
                                 text-xs
                                 font-bold">

                        {{ $users->total() }} Accounts

                    </span>

                </div>


                {{-- PENDING OFFICE APPLICATIONS --}}
                @if ($pendingCount > 0)

                    <div class="px-6 py-4
                                bg-indigo-50/60
                                border-b border-indigo-100
                                flex flex-col sm:flex-row
                                sm:items-center
                                sm:justify-between
                                gap-3">

                        <div class="flex items-center gap-3">

                            <span class="w-2.5 h-2.5
                                         rounded-full
                                         bg-indigo-500
                                         shrink-0">
                            </span>

                            <p class="text-sm font-semibold text-indigo-900">
                                {{ $pendingCount }}
                                {{ Str::plural('office application', $pendingCount) }}
                                awaiting your approval.
                            </p>

                        </div>

                        <a href="{{ route('admin.users.index', ['approval' => \App\Models\User::APPROVAL_PENDING]) }}"
                           class="inline-flex items-center gap-2 self-start sm:self-auto
                                  px-4 py-2
                                  rounded-lg
                                  bg-indigo-600
                                  text-white
                                  text-xs
                                  font-semibold
                                  hover:bg-indigo-700
                                  transition">

                            Review Now

                        </a>

                    </div>

                @endif


                {{-- =====================================================
                    TABLE
                ====================================================== --}}
                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-[#f8fafc]">

                            <tr>

                                <th class="px-6 py-4
                                           text-left
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    User

                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    Email

                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    Role

                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    Office

                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    Status

                                </th>

                                <th class="px-6 py-4
                                           text-left
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    Approval

                                </th>

                                <th class="px-6 py-4
                                           text-right
                                           text-[11px]
                                           font-bold
                                           uppercase
                                           tracking-wider
                                           text-gray-500">

                                    Actions

                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100">

                            @forelse ($users as $user)

                                @php

                                    $initials = collect(
                                        preg_split('/\s+/', trim($user->name))
                                    )
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                    ->implode('');

                                    $role = strtolower($user->role);

                                    $roleClasses = match ($role) {

                                        \App\Models\User::ROLE_STUDENT =>
                                            'bg-green-50 text-green-700',

                                        \App\Models\User::ROLE_REGISTRAR =>
                                            'bg-blue-50 text-blue-700',

                                        \App\Models\User::ROLE_CASHIER =>
                                            'bg-yellow-50 text-yellow-700',

                                        \App\Models\User::ROLE_DEPARTMENT =>
                                            'bg-purple-50 text-purple-700',

                                        \App\Models\User::ROLE_ADMIN =>
                                            'bg-gray-100 text-gray-700',

                                        default =>
                                            'bg-gray-100 text-gray-600',

                                    };

                                @endphp


                                <tr class="hover:bg-blue-50/30 transition">


                                    {{-- USER --}}
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="w-10 h-10
                                                        rounded-full
                                                        bg-blue-700
                                                        text-white
                                                        flex items-center
                                                        justify-center
                                                        text-xs
                                                        font-bold
                                                        shrink-0">

                                                {{ $initials ?: '?' }}

                                            </div>


                                            <div>

                                                <p class="text-sm
                                                          font-semibold
                                                          text-[#102d5b]">

                                                    {{ $user->name }}

                                                </p>

                                                @if(isset($user->student_id))

                                                    <p class="text-xs
                                                              text-gray-400
                                                              mt-0.5">

                                                        ID: {{ $user->student_id }}

                                                    </p>

                                                @endif

                                            </div>

                                        </div>

                                    </td>


                                    {{-- EMAIL --}}
                                    <td class="px-6 py-4">

                                        <span class="text-sm text-gray-600 whitespace-nowrap">

                                            {{ $user->email }}

                                        </span>

                                    </td>


                                    {{-- ROLE --}}
                                    <td class="px-6 py-4">

                                        <span class="inline-flex
                                                     items-center
                                                     px-3 py-1.5
                                                     rounded-full
                                                     text-xs
                                                     font-semibold
                                                     {{ $roleClasses }}">

                                            {{ ucfirst($user->role) }}

                                        </span>

                                    </td>


                                    {{-- OFFICE --}}
                                    <td class="px-6 py-4">

                                        <span class="text-sm text-gray-500">

                                            {{ $user->office ?? '—' }}

                                        </span>

                                    </td>


                                    {{-- STATUS --}}
                                    <td class="px-6 py-4">

                                        @if ($user->is_active)

                                            <span class="inline-flex
                                                         items-center
                                                         gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-green-50
                                                         text-green-700
                                                         text-xs
                                                         font-semibold">

                                                <span class="w-2 h-2
                                                             rounded-full
                                                             bg-green-500">
                                                </span>

                                                Active

                                            </span>

                                        @else

                                            <span class="inline-flex
                                                         items-center
                                                         gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-gray-100
                                                         text-gray-600
                                                         text-xs
                                                         font-semibold">

                                                <span class="w-2 h-2
                                                             rounded-full
                                                             bg-gray-400">
                                                </span>

                                                Inactive

                                            </span>

                                        @endif

                                    </td>


                                    {{-- APPROVAL --}}
                                    <td class="px-6 py-4">

                                        @if ($user->isPendingApproval())

                                            <span class="inline-flex
                                                         items-center
                                                         gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-indigo-50
                                                         text-indigo-700
                                                         text-xs
                                                         font-semibold">

                                                <span class="w-2 h-2
                                                             rounded-full
                                                             bg-indigo-500">
                                                </span>

                                                Pending

                                            </span>

                                            @if ($user->position)

                                                <p class="text-xs
                                                          text-gray-400
                                                          mt-1.5">

                                                    {{ $user->position }}

                                                </p>

                                            @endif

                                        @elseif ($user->isRejected())

                                            <span class="inline-flex
                                                         items-center
                                                         gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-red-50
                                                         text-red-700
                                                         text-xs
                                                         font-semibold">

                                                <span class="w-2 h-2
                                                             rounded-full
                                                             bg-red-500">
                                                </span>

                                                Rejected

                                            </span>

                                            @if ($user->rejection_reason)

                                                <p class="text-xs
                                                          text-gray-400
                                                          mt-1.5
                                                          max-w-[14rem]">

                                                    {{ $user->rejection_reason }}

                                                </p>

                                            @endif

                                        @else

                                            <span class="inline-flex
                                                         items-center
                                                         gap-2
                                                         px-3 py-1.5
                                                         rounded-full
                                                         bg-green-50
                                                         text-green-700
                                                         text-xs
                                                         font-semibold">

                                                <svg class="w-3.5 h-3.5"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2.5"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>

                                                Approved

                                            </span>

                                        @endif

                                    </td>


                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-4">

                                        <div class="flex items-center
                                                    justify-end gap-2">

                                            @if ($user->isPendingApproval())

                                                {{-- APPROVE --}}
                                                <form method="POST"
                                                      action="{{ route('admin.users.approve', $user) }}">

                                                    @csrf
                                                    @method('PUT')

                                                    <button type="submit"
                                                            class="px-4 py-2
                                                                   rounded-lg
                                                                   bg-green-600
                                                                   text-white
                                                                   text-xs
                                                                   font-semibold
                                                                   hover:bg-green-700
                                                                   transition">

                                                        Approve

                                                    </button>

                                                </form>


                                                {{-- REJECT --}}
                                                <form method="POST"
                                                      action="{{ route('admin.users.reject', $user) }}"
                                                      data-confirm="This will decline the application and block the account from signing in."
                                                      data-confirm-title="Reject this application?"
                                                      data-confirm-ok="Yes, reject it">

                                                    @csrf
                                                    @method('PUT')

                                                    <button type="submit"
                                                            class="px-4 py-2
                                                                   rounded-lg
                                                                   bg-red-600
                                                                   text-white
                                                                   text-xs
                                                                   font-semibold
                                                                   hover:bg-red-700
                                                                   transition">

                                                        Reject

                                                    </button>

                                                </form>

                                            @else

                                                {{-- TOGGLE --}}
                                                <form method="POST"
                                                      action="{{ route('admin.users.toggle', $user) }}">

                                                    @csrf
                                                    @method('PUT')

                                                    <button type="submit"
                                                            class="px-4 py-2
                                                                   rounded-lg
                                                                   text-xs
                                                                   font-semibold
                                                                   transition
                                                                   {{ $user->is_active
                                                                        ? 'bg-blue-700 text-white hover:bg-blue-800'
                                                                        : 'bg-green-500 text-white hover:bg-green-600' }}">

                                                        {{ $user->is_active
                                                            ? 'Deactivate'
                                                            : 'Activate' }}

                                                    </button>

                                                </form>

                                            @endif


                                            {{-- DELETE --}}
                                            <form method="POST"
                                                  action="{{ route('admin.users.destroy', $user) }}"
                                                  data-confirm="This account will be permanently deleted."
                                                  data-confirm-title="Delete this account?"
                                                  data-confirm-ok="Yes, delete it">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="px-4 py-2
                                                               rounded-lg
                                                               bg-white
                                                               border border-gray-200
                                                               text-gray-600
                                                               text-xs
                                                               font-semibold
                                                               hover:bg-gray-50
                                                               transition">

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td colspan="7"
                                        class="px-6 py-16 text-center">

                                        <div class="w-14 h-14
                                                    mx-auto
                                                    rounded-2xl
                                                    bg-gray-100
                                                    text-gray-400
                                                    flex items-center
                                                    justify-center">

                                            <svg class="w-7 h-7"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H3v-2a4 4 0 014-4h3a4 4 0 014 4v2zm-5-8a4 4 0 100-8 4 4 0 000 8zm9-4a3 3 0 100-6 3 3 0 000 6z"/>

                                            </svg>

                                        </div>

                                        <p class="mt-4
                                                  font-semibold
                                                  text-gray-700">

                                            No accounts found

                                        </p>

                                        <p class="mt-1
                                                  text-sm
                                                  text-gray-400">

                                            Try changing your search or filter.

                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- =====================================================
                    PAGINATION
                ====================================================== --}}
                @if ($users->hasPages())

                    <div class="px-6 py-4
                                border-t border-gray-100">

                        {{ $users->links() }}

                    </div>

                @endif

            </div>


            {{-- =========================================================
                ACCOUNT ACCESS INFORMATION
            ========================================================== --}}
            <div class="mt-5
                        bg-blue-50
                        border border-blue-100
                        rounded-2xl
                        px-5 py-4">

                <div class="flex items-start gap-3">

                    <div class="w-9 h-9
                                rounded-full
                                bg-blue-600
                                text-white
                                flex items-center justify-center
                                font-bold
                                shrink-0">

                        i

                    </div>


                    <div>

                        <p class="text-sm
                                  font-bold
                                  text-blue-900">

                            Account Access

                        </p>

                        <p class="mt-1
                                  text-xs
                                  leading-5
                                  text-blue-700">

                            Only active accounts can access the ISUFSTPASS
                            system. Deactivated accounts remain in the
                            system but cannot sign in.

                        </p>

                    </div>

                </div>

            </div>


        </div>

    </div>

</x-app-layout>