<x-filament-panels::page>
    <div class="space-y-6">

        {{-- SUMMARY --}}
        <div class="grid gap-4 lg:grid-cols-3">

            <div class="lg:col-span-2">
                <x-filament::section>
                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400"
                        >
                            <x-heroicon-o-user-group class="h-6 w-6" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                class="text-lg font-bold text-gray-950 dark:text-white"
                            >
                                Instructor Assignments
                            </h2>

                            <p
                                class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400"
                            >
                                View each lesson instructor and the students
                                currently assigned to them.
                            </p>
                        </div>

                    </div>
                </x-filament::section>
            </div>

            <x-filament::section>
                <div class="flex items-center justify-between gap-4">

                    <div>
                        <p
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                        >
                            Total Assignments
                        </p>

                        <p
                            class="mt-1 text-3xl font-bold tracking-tight text-gray-950 dark:text-white"
                        >
                            {{ $rows->count() }}
                        </p>
                    </div>

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400"
                    >
                        <x-heroicon-o-academic-cap class="h-6 w-6" />
                    </div>

                </div>
            </x-filament::section>

        </div>

        {{-- ASSIGNMENTS --}}
        <div class="space-y-5">

            @forelse($rows as $row)

                <x-filament::section>

                    {{-- INSTRUCTOR DETAILS --}}
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                    >

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <h2
                                    class="text-lg font-bold text-gray-950 dark:text-white"
                                >
                                    {{ $row['instructor_name'] }}
                                </h2>

                                <x-filament::badge color="info">
                                    {{ $row['assignment_role'] }}
                                </x-filament::badge>

                            </div>

                            <p
                                class="mt-2 text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                {{ $row['lesson_title'] }}
                            </p>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm"
                            >

                                @if($row['instructor_email'])
                                    <a
                                        href="mailto:{{ $row['instructor_email'] }}"
                                        class="inline-flex items-center gap-1.5 text-primary-600 hover:underline dark:text-primary-400"
                                    >
                                        <x-heroicon-o-envelope class="h-4 w-4" />

                                        <span>
                                            {{ $row['instructor_email'] }}
                                        </span>
                                    </a>
                                @endif

                                @if($row['instructor_phone'])
                                    <a
                                        href="tel:{{ $row['instructor_phone'] }}"
                                        class="inline-flex items-center gap-1.5 text-primary-600 hover:underline dark:text-primary-400"
                                    >
                                        <x-heroicon-o-phone class="h-4 w-4" />

                                        <span>
                                            {{ $row['instructor_phone'] }}
                                        </span>
                                    </a>
                                @endif

                            </div>

                        </div>

                        {{-- STUDENT COUNT --}}
                        <div
                            class="w-full shrink-0 rounded-xl border border-gray-200 bg-gray-50 px-5 py-3 lg:w-auto lg:min-w-[130px] dark:border-white/10 dark:bg-white/5"
                        >
                            <div
                                class="flex items-center justify-between gap-5 lg:block lg:text-center"
                            >

                                <span
                                    class="text-sm font-medium text-gray-500 lg:hidden dark:text-gray-400"
                                >
                                    Assigned Students
                                </span>

                                <div>
                                    <div
                                        class="text-2xl font-bold text-primary-600 dark:text-primary-400"
                                    >
                                        {{ $row['student_count'] }}
                                    </div>

                                    <div
                                        class="mt-0.5 hidden text-xs font-medium text-gray-500 lg:block dark:text-gray-400"
                                    >
                                        {{ $row['student_count'] === 1 ? 'Student' : 'Students' }}
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- STUDENT LIST --}}
                    @if($row['students']->isNotEmpty())

                        <div
                            class="mt-5 overflow-hidden rounded-xl border border-gray-200 dark:border-white/10"
                        >

                            <div class="overflow-x-auto">

                                <table class="w-full table-auto text-left">

                                    <thead
                                        class="bg-gray-50 dark:bg-white/5"
                                    >
                                        <tr>

                                            <th
                                                class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200"
                                            >
                                                Student
                                            </th>

                                            <th
                                                class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200"
                                            >
                                                Email
                                            </th>

                                            <th
                                                class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200"
                                            >
                                                Phone
                                            </th>

                                        </tr>
                                    </thead>

                                    <tbody
                                        class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900"
                                    >

                                        @foreach($row['students'] as $student)

                                            <tr
                                                class="transition hover:bg-gray-50 dark:hover:bg-white/5"
                                            >

                                                <td
                                                    class="whitespace-nowrap px-5 py-4"
                                                >
                                                    <div
                                                        class="flex items-center gap-3"
                                                    >

                                                        <div
                                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300"
                                                        >
                                                            {{ strtoupper(substr($student['name'], 0, 1)) }}
                                                        </div>

                                                        <span
                                                            class="font-semibold text-gray-950 dark:text-white"
                                                        >
                                                            {{ $student['name'] }}
                                                        </span>

                                                    </div>
                                                </td>

                                                <td
                                                    class="whitespace-nowrap px-5 py-4 text-sm"
                                                >
                                                    @if($student['email'])

                                                        <a
                                                            href="mailto:{{ $student['email'] }}"
                                                            class="text-primary-600 hover:underline dark:text-primary-400"
                                                        >
                                                            {{ $student['email'] }}
                                                        </a>

                                                    @else

                                                        <span
                                                            class="text-gray-400 dark:text-gray-500"
                                                        >
                                                            —
                                                        </span>

                                                    @endif
                                                </td>

                                                <td
                                                    class="whitespace-nowrap px-5 py-4 text-sm"
                                                >
                                                    @if($student['phone'])

                                                        <a
                                                            href="tel:{{ $student['phone'] }}"
                                                            class="text-primary-600 hover:underline dark:text-primary-400"
                                                        >
                                                            {{ $student['phone'] }}
                                                        </a>

                                                    @else

                                                        <span
                                                            class="text-gray-400 dark:text-gray-500"
                                                        >
                                                            —
                                                        </span>

                                                    @endif
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    @else

                        <div
                            class="mt-5 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-center dark:border-white/10 dark:bg-white/5"
                        >

                            <div
                                class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400"
                            >
                                <x-heroicon-o-users class="h-5 w-5" />
                            </div>

                            <p
                                class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400"
                            >
                                No students are currently assigned to this
                                instructor for this lesson.
                            </p>

                        </div>

                    @endif

                </x-filament::section>

            @empty

                <x-filament::section>

                    <div class="py-8 text-center">

                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400"
                        >
                            <x-heroicon-o-user-group class="h-7 w-7" />
                        </div>

                        <h2
                            class="mt-4 text-lg font-bold text-gray-950 dark:text-white"
                        >
                            No instructor assignments yet
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-500 dark:text-gray-400"
                        >
                            Configure lead and follow-up instructors on a lesson
                            to see their assignments here.
                        </p>

                    </div>

                </x-filament::section>

            @endforelse

        </div>

    </div>
</x-filament-panels::page>