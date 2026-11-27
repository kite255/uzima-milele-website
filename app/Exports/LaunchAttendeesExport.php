<?php
namespace App\Exports;
use App\Models\LaunchAttendee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
class LaunchAttendeesExport extends StringValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder {
    public function query(){return LaunchAttendee::query()->orderBy('id');}
    public function headings(): array {return ['Name','Phone','Email','Gender','Church','College','Downloaded (self-reported)','SMS','Email status','Registered'];}
    public function map($a): array {return [$a->name,$a->phone,$a->email,$a->gender,$a->church,$a->college,$a->downloaded?'Yes':'No',$a->sms_status,$a->email_status,$a->created_at->toDateTimeString()];}
}
