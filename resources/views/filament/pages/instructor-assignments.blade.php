<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white">Instructor Assignments</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        View each lesson instructor and the students assigned to them.
                    </p>
                </div>

                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ $rows->count() }} assignment{{ $rows->count() === 1 ? '' : 's' }}
                </div>
            </div>
        </div>

        @forelse($rows as $row)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 p-5 dark:border-white/10">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                                    {{ $row['instructor_name'] }}
                                </h3>

                                <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-400/10 dark:text-primary-400">
                                    {{ $row['assignment_role'] }}
                                </span>
                            </div>

                            <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ $row['lesson_title'] }}
                            </p>

                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($row['instructor_email'])
                                    <span>{{ $row['instructor_email'] }}</span>
                                @endif

                                @if($row['instructor_phone'])
                                    <span>{{ $row['instructor_phone'] }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-center dark:bg-white/5">
                            <div class="text-2xl font-bold text-gray-950 dark:text-white">
                                {{ $row['student_count'] }}
                            </div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Students
                            </div>
                        </div>
                    </div>
                </div>

                @if($row['students']->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/5">
                                <tr>
                                    <th class="px-5 py-3">Student</th>
                                    <th class="px-5 py-3">Email</th>
                                    <th class="px-5 py-3">Phone</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach($row['students'] as $student)
                                    <tr>
                                        <td class="px-5 py-3 font-semibold text-gray-950 dark:text-white">{{ $student['name'] }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $student['email'] ?: '—' }}</td>
                                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $student['phone'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-5 text-sm text-gray-500 dark:text-gray-400">
                        No students are currently assigned to this instructor for this lesson.
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No instructor assignments have been configured yet.
                </p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
