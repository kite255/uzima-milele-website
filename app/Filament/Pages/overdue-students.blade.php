<div>
    <div class="space-y-6">

        <div class="rounded-xl bg-yellow-50 border border-yellow-200 p-4">
            <h2 class="text-lg font-bold text-yellow-800">
                Overdue Students
            </h2>

            <p class="text-sm text-yellow-700 mt-1">
                Students shown here enrolled more than 10 days ago and have not completed all published topics.
                SMS is manual only.
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Lesson</th>
                        <th class="px-4 py-3">Progress</th>
                        <th class="px-4 py-3">Days</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-bold text-gray-900">
                                    {{ $row['name'] }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    User ID: {{ $row['user_id'] }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div>{{ $row['email'] ?? 'No email' }}</div>

                                <div class="text-xs text-gray-500">
                                    {{ $row['phone'] ?? 'No phone' }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-800">
                                    {{ $row['lesson_title'] }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    Lesson ID: {{ $row['lesson_id'] }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <span>{{ $row['completed_topics'] }}/{{ $row['total_topics'] }} topics</span>
                                    <span>{{ $row['progress'] }}%</span>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <span class="font-bold">
                                    {{ $row['days'] }} days
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">

                                    <button type="button"
                                            wire:click="sendManualReminder({{ $row['enrollment_id'] }}, 'both')"
                                            style="background:#0083CB;color:white;padding:8px 12px;border-radius:8px;font-weight:700;font-size:12px;">
                                        Email + Notify
                                    </button>

                                    <button type="button"
                                            wire:click="sendManualReminder({{ $row['enrollment_id'] }}, 'sms')"
                                            style="background:#F4B122;color:#0E3D4F;padding:8px 12px;border-radius:8px;font-weight:700;font-size:12px;">
                                        SMS
                                    </button>

                                    <button type="button"
                                            wire:click="sendManualReminder({{ $row['enrollment_id'] }}, 'all')"
                                            style="background:#0E3D4F;color:white;padding:8px 12px;border-radius:8px;font-weight:700;font-size:12px;">
                                        All
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <h3 class="font-bold text-gray-900">
                                    No overdue students found.
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    Students will appear here after more than 10 days if they have not completed their lessons.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>