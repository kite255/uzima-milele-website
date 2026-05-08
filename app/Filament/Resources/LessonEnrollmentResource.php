<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonEnrollmentResource\Pages;
use App\Models\Certificate;
use App\Models\LessonEnrollment;
use App\Models\LessonProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonEnrollmentResource extends Resource
{
    protected static ?string $model = LessonEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?string $navigationLabel = 'Student Enrollments';

    protected static ?string $modelLabel = 'Student Enrollment';

    protected static ?string $pluralModelLabel = 'Student Enrollments';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Enrollment Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Student')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('lesson_id')
                            ->label('Lesson')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('enrolled_at')
                            ->label('Enrolled At')
                            ->seconds(false)
                            ->default(now()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (LessonEnrollment $record): string {
                        $lesson = $record->lesson;

                        if (! $lesson) {
                            return '0%';
                        }

                        $lesson->loadMissing('modules.topics');

                        $topicIds = $lesson->modules
                            ->flatMap(fn ($module) => $module->topics)
                            ->pluck('id');

                        $totalTopics = $topicIds->count();

                        if ($totalTopics === 0) {
                            return '0%';
                        }

                        $completedTopics = LessonProgress::where('user_id', $record->user_id)
                            ->where('lesson_id', $record->lesson_id)
                            ->whereIn('lesson_topic_id', $topicIds)
                            ->distinct('lesson_topic_id')
                            ->count('lesson_topic_id');

                        return round(($completedTopics / $totalTopics) * 100) . '%';
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        $lesson = $record->lesson;

                        if (! $lesson) {
                            return 'gray';
                        }

                        $lesson->loadMissing('modules.topics');

                        $topicIds = $lesson->modules
                            ->flatMap(fn ($module) => $module->topics)
                            ->pluck('id');

                        $totalTopics = $topicIds->count();

                        if ($totalTopics === 0) {
                            return 'gray';
                        }

                        $completedTopics = LessonProgress::where('user_id', $record->user_id)
                            ->where('lesson_id', $record->lesson_id)
                            ->whereIn('lesson_topic_id', $topicIds)
                            ->distinct('lesson_topic_id')
                            ->count('lesson_topic_id');

                        $progress = round(($completedTopics / $totalTopics) * 100);

                        return match (true) {
                            $progress >= 100 => 'success',
                            $progress >= 50 => 'warning',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('certificate_status')
                    ->label('Certificate')
                    ->state(function (LessonEnrollment $record): string {
                        $hasCertificate = Certificate::where('user_id', $record->user_id)
                            ->where('lesson_id', $record->lesson_id)
                            ->exists();

                        return $hasCertificate ? 'Issued' : 'Not Issued';
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        $hasCertificate = Certificate::where('user_id', $record->user_id)
                            ->where('lesson_id', $record->lesson_id)
                            ->exists();

                        return $hasCertificate ? 'success' : 'gray';
                    }),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Enrolled At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lesson_id')
                    ->label('Lesson')
                    ->relationship('lesson', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Student')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('viewStudent')
                    ->label('View Student')
                    ->icon('heroicon-o-user')
                    ->url(fn (LessonEnrollment $record) => url('/admin/users/' . $record->user_id . '/edit'))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('viewLesson')
                    ->label('View Lesson')
                    ->icon('heroicon-o-academic-cap')
                    ->url(fn (LessonEnrollment $record) => route('lessons.show', $record->lesson->slug))
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('enrolled_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user', 'lesson.modules.topics']);

        $user = auth()->user();

        if ($user && $user->role === 'instructor') {
            return $query->whereHas('lesson', function (Builder $lessonQuery) use ($user) {
                $lessonQuery->where('instructor_id', $user->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessonEnrollments::route('/'),
            'create' => Pages\CreateLessonEnrollment::route('/create'),
            'edit' => Pages\EditLessonEnrollment::route('/{record}/edit'),
        ];
    }
}