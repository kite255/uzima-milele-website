<x-filament-panels::page>
    <div class="space-y-8">

        {{-- HERO / PAGE SUMMARY --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-cyan-900 to-sky-600 p-6 md:p-8 text-white shadow-lg">
            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-white/80">
                        Lesson Management
                    </p>

                    <h1 class="mt-2 text-3xl md:text-4xl font-black">
                        Instructor Assignments
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm md:text-base text-white/85">
                        View each lesson instructor and the students currently assigned to them.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center rounded-2xl bg-white/15 px-5 py-4 backdrop-blur-sm">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-white/70">
                            Total Assignments
                        </p>

                        <p class="mt-1 text-3xl font-black">
                            {{ $rows->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="absolute -right-12 -bottom-14 h-56 w-56 rounded-full bg-white/10"></div>
            <div class="absolute right-24 top-6 h-24 w-24 rounded-full bg-white/10"></div>
        </div>

        {{-- ASSIGNMENT CARDS --}}
        <div class="space-y-6">
            @forelse($rows as $row)
                <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">

                    {{-- INSTRUCTOR HEADER --}}
                    <div class="border-b border-gray-100 p-5 md:p-6 dark:border-white/10">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-xl md:text-2xl font-black text-gray-950 dark:text-white">
                                        {{ $row['instructor_name'] }}
                                    </h2>

                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/20">
                                        {{ $row['assignment_role'] }}
                                    </span>
                                </div>

                                <p class="mt-2 text-sm md:text-base font-bold text-gray-800 dark:text-gray-200">
                                    {{ $row['lesson_title'] }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500 dark:text-gray-400">
                                    @if($row['instructor_email'])
                                        <a href="mailto:{{ $row['instructor_email'] }}"
                                           class="font-medium text-sky-700 hover:underline dark:text-sky-300">
                                            {{ $row['instructor_email'] }}
                                        </a>
                                    @endif

                                    @if($row['instructor_phone'])
                                        <a href="tel:{{ $row['instructor_phone'] }}"
                                           class="font-medium text-sky-700 hover:underline dark:text-sky-300">
                                            {{ $row['instructor_phone'] }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0">
                                <div class="min-w-32 rounded-2xl bg-sky-50 px-5 py-4 text-center ring-1 ring-inset ring-sky-100 dark:bg-sky-400/10 dark:ring-sky-400/20">
                                    <div class="text-3xl font-black text-sky-700 dark:text-sky-300">
                                        {{ $row['student_count'] }}
                                    </div>

                                    <div class="mt-1 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ $row['student_count'] === 1 ? 'Student' : 'Students' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STUDENTS TABLE --}}
                    @if($row['students']->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                    <tr>
                                        <th class="px-5 md:px-6 py-4 font-bold">
                                            Student
                                        </th>
                                        <th class="px-5 md:px-6 py-4 font-bold">
                                            Email
                                        </th>
                                        <th class="px-5 md:px-6 py-4 font-bold">
                                            Phone
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                    @foreach($row['students'] as $student)
                                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/5">
                                            <td class="px-5 md:px-6 py-4">
                                                <p class="font-black text-gray-950 dark:text-white">
                                                    {{ $student['name'] }}
                                                </p>
                                            </td>

                                            <td class="px-5 md:px-6 py-4">
                                                @if($student['email'])
                                                    <a href="mailto:{{ $student['email'] }}"
                                                       class="font-medium text-sky-700 hover:underline dark:text-sky-300">
                                                        {{ $student['email'] }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>

                                            <td class="px-5 md:px-6 py-4">
                                                @if($student['phone'])
                                                    <a href="tel:{{ $student['phone'] }}"
                                                       class="font-medium text-sky-700 hover:underline dark:text-sky-300">
                                                        {{ $student['phone'] }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6">
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-6 text-center dark:border-white/10 dark:bg-white/5">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    No students are currently assigned to this instructor for this lesson.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-3xl border border-gray-100 bg-white p-10 text-center shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300">
                        <x-heroicon-o-user-group class="h-7 w-7" />
                    </div>

                    <h2 class="mt-4 text-xl font-black text-gray-950 dark:text-white">
                        No instructor assignments yet
                    </h2>

                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Configure lead and follow-up instructors on a lesson to see them here.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-filament-panels::page>
