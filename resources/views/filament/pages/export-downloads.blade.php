<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-950">
                Export Downloads
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Download your completed exports in CSV or XLSX format.
            </p>
        </div>

        @if ($exports->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm">
                No completed exports are available yet.
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">File</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Rows</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Completed</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Downloads</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($exports as $export)
                                <tr>
                                    <td class="px-4 py-4 font-medium text-gray-900">
                                        {{ $export->file_name ?? ('export-' . $export->id) }}
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">
                                        {{ number_format($export->successful_rows) }} / {{ number_format($export->total_rows) }}
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">
                                        {{ \Illuminate\Support\Carbon::parse($export->completed_at)->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a
                                                href="{{ url('/filament/exports/' . $export->id . '/download?format=csv') }}"
                                                class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-xs font-semibold text-white hover:bg-primary-500"
                                            >
                                                Download CSV
                                            </a>

                                            <a
                                                href="{{ url('/filament/exports/' . $export->id . '/download?format=xlsx') }}"
                                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                            >
                                                Download XLSX
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
