<?php

namespace App\Exports;

use App\Models\EmailSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmailSubscribersExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
{
    public function __construct(
        protected string $status = 'all',
    ) {
    }

    public function query(): Builder
    {
        return EmailSubscriber::query()
            ->when(
                $this->status !== 'all',
                fn (Builder $query) =>
                    $query->where('status', $this->status)
            )
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Name',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Status',
            'Source',
            'Language',
            'Subscribed At',
            'Unsubscribed At',
            'Created At',
        ];
    }

    public function map($subscriber): array
    {
        return [
            $subscriber->name,
            $subscriber->first_name,
            $subscriber->last_name,
            $subscriber->email,
            $subscriber->phone,
            $subscriber->status,
            $subscriber->source,
            $subscriber->language,
            optional($subscriber->subscribed_at)
                ?->format('Y-m-d H:i:s'),
            optional($subscriber->unsubscribed_at)
                ?->format('Y-m-d H:i:s'),
            optional($subscriber->created_at)
                ?->format('Y-m-d H:i:s'),
        ];
    }
}