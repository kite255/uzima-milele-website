<div class="space-y-5">

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">
            Mpokeaji
        </p>

        <p class="mt-1 font-bold text-gray-900">
            {{ $recipient->name ?: '—' }}
        </p>

        <p class="mt-1 text-sm text-gray-600">
            {{ $recipient->email }}
        </p>
    </div>

    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="text-sm font-bold text-red-800">
            Sababu ya Kushindwa
        </p>

        <div
            class="mt-3 whitespace-pre-wrap break-words text-sm leading-relaxed text-red-700"
        >
            {{ $recipient->error_message ?: 'Hakuna maelezo ya hitilafu.' }}
        </div>
    </div>

    @if ($recipient->failed_at)
        <p class="text-xs text-gray-500">
            Imeshindwa tarehe:
            {{ $recipient->failed_at->format('d M Y H:i') }}
        </p>
    @endif

</div>
