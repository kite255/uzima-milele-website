<?php
namespace App\Filament\Resources\LaunchAttendeeResource\Pages;
use App\Filament\Resources\LaunchAttendeeResource;
use App\Models\LaunchAttendee;
use App\Exports\LaunchAttendeesExport;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
class ListLaunchAttendees extends ListRecords {
    protected static string $resource = LaunchAttendeeResource::class;
    public function getSubheading(): ?string {
        return 'Registered: '.LaunchAttendee::count().' | Downloaded (self-reported): '.LaunchAttendee::where('downloaded',true)->count();
    }
    protected function getHeaderActions(): array {
        return [Action::make('export')->label('Export Excel')->action(function(){abort_unless(LaunchAttendeeResource::canViewAny(),403);return Excel::download(new LaunchAttendeesExport,'launch-attendees.xlsx');})];
    }
}
