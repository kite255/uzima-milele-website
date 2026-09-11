<x-filament-panels::page>

    <style>
        .ih-page {
            --ih-primary: #0083CB;
            --ih-primary-dark: #076994;
            --ih-navy: #0E3D4F;
            --ih-accent: #F4B122;

            display: flex;
            flex-direction: column;
            gap: 28px;
            width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | Hero
        |--------------------------------------------------------------------------
        */
        .ih-hero {
            position: relative;
            overflow: hidden;
            padding: 34px 38px;
            border-radius: 24px;
            background:
                linear-gradient(
                    135deg,
                    #0E3D4F 0%,
                    #076994 48%,
                    #0083CB 100%
                );
            color: #ffffff;
            box-shadow:
                0 10px 30px rgba(15, 23, 42, 0.12);
        }

        .ih-hero::before,
        .ih-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.11);
        }

        .ih-hero::before {
            width: 190px;
            height: 190px;
            right: -45px;
            bottom: -80px;
        }

        .ih-hero::after {
            width: 95px;
            height: 95px;
            right: 115px;
            top: 25px;
        }

        .ih-hero-content {
            position: relative;
            z-index: 2;
        }

        .ih-eyebrow {
            margin: 0 0 8px;
            color: rgba(255,255,255,.9);
            font-size: 14px;
            font-weight: 800;
        }

        .ih-title {
            margin: 0;
            color: #ffffff;
            font-size: 34px;
            line-height: 1.15;
            font-weight: 900;
        }

        .ih-description {
            margin: 12px 0 0;
            color: rgba(255,255,255,.9);
            font-size: 15px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */
        .ih-stats {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 16px;
        }

        .ih-stat {
            min-width: 0;
            padding: 20px 22px;
            border: 1px solid #E2E8F0;
            border-radius: 18px;
            background: #ffffff;
        }

        .ih-stat.primary {
            border-top: 4px solid var(--ih-primary);
        }

        .ih-stat.navy {
            border-top: 4px solid var(--ih-navy);
        }

        .ih-stat.green {
            border-top: 4px solid #22C55E;
        }

        .ih-stat.accent {
            border-top: 4px solid var(--ih-accent);
        }

        .ih-stat-label {
            color: #64748B;
            font-size: 13px;
            font-weight: 600;
        }

        .ih-stat-value {
            margin-top: 10px;
            color: var(--ih-navy);
            font-size: 30px;
            line-height: 1;
            font-weight: 900;
        }

        .ih-stat.green .ih-stat-value {
            color: #16A34A;
        }

        .ih-stat.primary .ih-stat-value {
            color: var(--ih-primary);
        }

        /*
        |--------------------------------------------------------------------------
        | Sections
        |--------------------------------------------------------------------------
        */
        .ih-section {
            overflow: hidden;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            background: #ffffff;
        }

        .ih-section-header {
            padding: 22px 24px;
            border-bottom: 1px solid #E2E8F0;
        }

        .ih-section-title {
            margin: 0;
            color: var(--ih-navy);
            font-size: 22px;
            font-weight: 900;
        }

        .ih-section-description {
            margin: 5px 0 0;
            color: #64748B;
            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | Quick Actions
        |--------------------------------------------------------------------------
        */
        .ih-actions {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 16px;
            padding: 24px;
        }

        .ih-action {
            display: flex;
            align-items: center;
            min-height: 72px;
            padding: 18px 20px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .ih-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15,23,42,.08);
        }

        .ih-action.yellow {
            background: #FFF3D6;
            color: #0E3D4F;
        }

        .ih-action.blue {
            background: #E4F3FB;
            color: #0083CB;
        }

        .ih-action.red {
            background: #FFF0F0;
            color: #B91C1C;
        }

        .ih-action.navy {
            background: #EDF3F5;
            color: #0E3D4F;
        }

        .ih-action.gray {
            background: #F3F4F6;
            color: #0E3D4F;
        }

        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */
        .ih-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .ih-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .ih-table th {
            padding: 14px 18px;
            background: #F8FAFC;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .ih-table td {
            padding: 15px 18px;
            border-top: 1px solid #F1F5F9;
            color: #334155;
            font-size: 13px;
            vertical-align: top;
        }

        .ih-name {
            color: var(--ih-navy);
            font-weight: 900;
        }

        .ih-muted {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 11px;
        }

        .ih-link {
            color: var(--ih-primary);
            font-weight: 700;
            text-decoration: none;
        }

        .ih-link:hover {
            text-decoration: underline;
        }

        .ih-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border-radius: 9px;
            background: var(--ih-primary);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        /*
        |--------------------------------------------------------------------------
        | Badges
        |--------------------------------------------------------------------------
        */
        .ih-badge {
            display: inline-flex;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .ih-badge.blue {
            background: #E0F2FE;
            color: #0369A1;
        }

        .ih-badge.green {
            background: #DCFCE7;
            color: #15803D;
        }

        .ih-badge.yellow {
            background: #FEF3C7;
            color: #A16207;
        }

        .ih-badge.red {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .ih-badge.gray {
            background: #F1F5F9;
            color: #475569;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */
        .ih-empty {
            padding: 40px 24px;
            text-align: center;
        }

        .ih-empty-title {
            color: #475569;
            font-size: 14px;
            font-weight: 800;
        }

        .ih-empty-text {
            margin-top: 4px;
            color: #94A3B8;
            font-size: 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Question
        |--------------------------------------------------------------------------
        */
        .ih-question {
            padding: 20px 24px;
            border-bottom: 1px solid #F1F5F9;
        }

        .ih-question:last-child {
            border-bottom: 0;
        }

        .ih-question-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .ih-question-text {
            margin-top: 10px;
            color: #475569;
            font-size: 13px;
            line-height: 1.6;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */
        @media (max-width: 1200px) {
            .ih-stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .ih-actions {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .ih-hero {
                padding: 26px 22px;
            }

            .ih-title {
                font-size: 27px;
            }

            .ih-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ih-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ih-question-row {
                flex-direction: column;
            }
        }

        @media (max-width: 520px) {
            .ih-stats,
            .ih-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="ih-page">

        {{-- HERO --}}
        <section class="ih-hero">
            <div class="ih-hero-content">

                <p class="ih-eyebrow">
                    Instructor Dashboard
                </p>

                <h2 class="ih-title">
                    Karibu, {{ auth()->user()->name }}
                </h2>

                <p class="ih-description">
                    Manage your lessons, students, follow-ups,
                    questions and learning activities.
                </p>

            </div>
        </section>

        {{-- STATS --}}
        <section class="ih-stats">

            <div class="ih-stat primary">
                <div class="ih-stat-label">My Lessons</div>
                <div class="ih-stat-value">{{ $totalLessons }}</div>
            </div>

            <div class="ih-stat navy">
                <div class="ih-stat-label">Students</div>
                <div class="ih-stat-value">{{ $totalStudents }}</div>
            </div>

            <div class="ih-stat accent">
                <div class="ih-stat-label">Pending Questions</div>
                <div class="ih-stat-value">{{ $pendingQuestions }}</div>
            </div>

            <div class="ih-stat green">
                <div class="ih-stat-label">Answered</div>
                <div class="ih-stat-value">{{ $answeredQuestions }}</div>
            </div>

            <div class="ih-stat navy">
                <div class="ih-stat-label">Certificates</div>
                <div class="ih-stat-value">{{ $certificatesIssued }}</div>
            </div>

        </section>

        {{-- QUICK ACTIONS --}}
        <section class="ih-section">

            <div class="ih-section-header">
                <h3 class="ih-section-title">
                    Quick Actions
                </h3>
            </div>

            <div class="ih-actions">

                <a
                    href="{{ route('instructor.questions.index') }}"
                    class="ih-action yellow"
                >
                    Answer Q&A
                </a>

                <a
                    href="#assigned-students"
                    class="ih-action blue"
                >
                    Assigned Students
                </a>

                <a
                    href="#due-follow-ups"
                    class="ih-action red"
                >
                    Due Follow-ups
                </a>

                @if($canViewTeamSupervision)
                    <a
                        href="#team-supervision"
                        class="ih-action navy"
                    >
                        Team Supervision
                    </a>
                @endif

                <a
                    href="{{ route('lessons.index') }}"
                    class="ih-action gray"
                >
                    View Lessons
                </a>

            </div>
        </section>

        {{-- DUE FOLLOW UPS --}}
        <section
            id="due-follow-ups"
            class="ih-section"
        >

            <div class="ih-section-header">
                <h3 class="ih-section-title">
                    Due Follow-ups
                </h3>

                <p class="ih-section-description">
                    Students whose scheduled follow-up time
                    has arrived or passed.
                </p>
            </div>

            @if($dueFollowUps->isEmpty())

                <div class="ih-empty">
                    <div class="ih-empty-title">
                        No follow-ups are due right now.
                    </div>

                    <div class="ih-empty-text">
                        Scheduled follow-ups will appear here
                        when their date and time are reached.
                    </div>
                </div>

            @else

                <div class="ih-table-wrap">
                    <table class="ih-table">

                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Lesson</th>
                                <th>Assigned Instructor</th>
                                <th>Status</th>
                                <th>Due At</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($dueFollowUps as $dueFollowUp)

                                @php
                                    $status = $dueFollowUp->follow_up_status
                                        ?? \App\Models\LessonEnrollment::FOLLOW_UP_NOT_CONTACTED;

                                    $tone = match ($status) {
                                        \App\Models\LessonEnrollment::FOLLOW_UP_COMPLETED => 'green',
                                        \App\Models\LessonEnrollment::FOLLOW_UP_DOING_WELL => 'green',
                                        \App\Models\LessonEnrollment::FOLLOW_UP_NEEDS_FOLLOW_UP => 'yellow',
                                        \App\Models\LessonEnrollment::FOLLOW_UP_CONTACTED => 'blue',
                                        default => 'gray',
                                    };
                                @endphp

                                <tr>

                                    <td>
                                        <div class="ih-name">
                                            {{ $dueFollowUp->user?->name ?? 'Student' }}
                                        </div>

                                        @if($dueFollowUp->user?->phone)
                                            <div class="ih-muted">
                                                {{ $dueFollowUp->user->phone }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $dueFollowUp->lesson?->title ?? 'Lesson' }}
                                    </td>

                                    <td>
                                        {{ $dueFollowUp->followUpInstructor?->name ?? 'Unassigned' }}
                                    </td>

                                    <td>
                                        <span class="ih-badge {{ $tone }}">
                                            {{ ucwords(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="ih-badge red">
                                            Overdue
                                        </span>

                                        <div class="ih-muted">
                                            {{ $dueFollowUp->next_follow_up_at?->format('d M Y, H:i') }}
                                        </div>
                                    </td>

                                    <td>
                                        <a
                                            href="{{ route('instructor.students.show', $dueFollowUp) }}"
                                            class="ih-btn"
                                        >
                                            View Student
                                        </a>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>
                    </table>
                </div>

            @endif

        </section>

        {{-- TEAM SUPERVISION --}}
        @if($canViewTeamSupervision)

            <section
                id="team-supervision"
                class="ih-section"
            >

                <div class="ih-section-header">
                    <h3 class="ih-section-title">
                        Team Supervision
                    </h3>

                    <p class="ih-section-description">
                        Monitor follow-up instructors and students
                        in the lessons you lead.
                    </p>
                </div>

                @if($teamSupervision->isEmpty())

                    <div class="ih-empty">
                        <div class="ih-empty-title">
                            No follow-up instructors are configured yet.
                        </div>
                    </div>

                @else

                    <div class="ih-table-wrap">

                        <table class="ih-table">

                            <thead>
                                <tr>
                                    <th>Follow-up Instructor</th>
                                    <th>Lesson</th>
                                    <th>Students</th>
                                    <th>Due Follow-ups</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($teamSupervision as $teamMember)

                                    <tr>

                                        <td>
                                            <div class="ih-name">
                                                {{ $teamMember['instructor']->name }}
                                            </div>

                                            <div class="ih-muted">
                                                {{ $teamMember['instructor']->email }}
                                            </div>

                                            @if($teamMember['instructor']->phone)
                                                <div class="ih-muted">
                                                    {{ $teamMember['instructor']->phone }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $teamMember['lesson']->title }}
                                        </td>

                                        <td>
                                            <a
                                                href="{{ route('instructor.team.students', [
                                                    'lesson' => $teamMember['lesson'],
                                                    'instructor' => $teamMember['instructor'],
                                                ]) }}"
                                                class="ih-link"
                                            >
                                                {{ $teamMember['student_count'] }}

                                                {{ $teamMember['student_count'] === 1
                                                    ? 'Student'
                                                    : 'Students'
                                                }}
                                            </a>
                                        </td>

                                        <td>

                                            @if($teamMember['due_follow_up_count'] > 0)

                                                <span class="ih-badge red">
                                                    {{ $teamMember['due_follow_up_count'] }}
                                                    Due
                                                </span>

                                            @else

                                                <span class="ih-badge green">
                                                    Up to date
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

                {{-- UNASSIGNED STUDENTS --}}
                <div class="ih-section-header">
                    <h3 class="ih-section-title">
                        Unassigned Students
                    </h3>

                    <p class="ih-section-description">
                        Students in lessons you lead who do not
                        yet have a follow-up instructor.
                    </p>
                </div>

                @if($unassignedStudents->isEmpty())

                    <div class="ih-empty">
                        <div class="ih-empty-title">
                            All students are assigned.
                        </div>
                    </div>

                @else

                    <div class="ih-table-wrap">

                        <table class="ih-table">

                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Lesson</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Enrolled</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($unassignedStudents as $enrollment)

                                    <tr>

                                        <td>
                                            <div class="ih-name">
                                                {{ $enrollment->user?->name ?? 'Student' }}
                                            </div>
                                        </td>

                                        <td>
                                            {{ $enrollment->lesson?->title ?? 'Lesson' }}
                                        </td>

                                        <td>
                                            {{ $enrollment->user?->email ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $enrollment->user?->phone ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $enrollment->enrolled_at?->format('d M Y') ?? '—' }}
                                        </td>

                                        <td>
                                            <a
                                                href="{{ route('instructor.students.show', $enrollment) }}"
                                                class="ih-btn"
                                            >
                                                View Student
                                            </a>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </section>

        @endif

        {{-- ASSIGNED STUDENTS --}}
        <section
            id="assigned-students"
            class="ih-section"
        >

            <div class="ih-section-header">

                <h3 class="ih-section-title">
                    Assigned Students
                </h3>

                <p class="ih-section-description">
                    Students currently visible to you based
                    on your instructor role.
                </p>

            </div>

            @if($assignedStudents->isEmpty())

                <div class="ih-empty">
                    <div class="ih-empty-title">
                        No assigned students yet.
                    </div>
                </div>

            @else

                <div class="ih-table-wrap">

                    <table class="ih-table">

                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Lesson</th>
                                <th>Assignment</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Enrolled</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($assignedStudents as $studentEnrollment)

                                @php
                                    $student = $studentEnrollment->user;
                                    $studentLesson = $studentEnrollment->lesson;

                                    if (
                                        $studentLesson?->lead_instructor_id
                                        === auth()->id()
                                    ) {
                                        $assignmentLabel = 'Lead Instructor';
                                    } elseif (
                                        $studentEnrollment->follow_up_instructor_id
                                        === auth()->id()
                                    ) {
                                        $assignmentLabel = 'Follow-up';
                                    } else {
                                        $assignmentLabel = 'Legacy';
                                    }
                                @endphp

                                <tr>

                                    <td>
                                        <div class="ih-name">
                                            {{ $student?->name ?? 'Student' }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ $studentLesson?->title ?? 'Lesson' }}
                                    </td>

                                    <td>
                                        <span class="ih-badge blue">
                                            {{ $assignmentLabel }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($student?->email)
                                            <a
                                                href="mailto:{{ $student->email }}"
                                                class="ih-link"
                                            >
                                                {{ $student->email }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        @if($student?->phone)
                                            <a
                                                href="tel:{{ $student->phone }}"
                                                class="ih-link"
                                            >
                                                {{ $student->phone }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        {{ $studentEnrollment->enrolled_at?->format('d M Y') ?? '—' }}
                                    </td>

                                    <td>
                                        <a
                                            href="{{ route('instructor.students.show', $studentEnrollment) }}"
                                            class="ih-btn"
                                        >
                                            View Student
                                        </a>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>
                    </table>

                </div>

            @endif

        </section>

        {{-- LESSONS --}}
        <section class="ih-section">

            <div class="ih-section-header">

                <h3 class="ih-section-title">
                    My Lessons
                </h3>

                <p class="ih-section-description">
                    Lessons available through your instructor responsibilities.
                </p>

            </div>

            <div class="ih-table-wrap">

                <table class="ih-table">

                    <thead>
                        <tr>
                            <th>Lesson</th>
                            <th>Modules</th>
                            <th>Topics</th>
                            <th>Students</th>
                            <th>Questions</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($lessons as $lesson)

                            <tr>

                                <td>
                                    <div class="ih-name">
                                        {{ $lesson->title }}
                                    </div>

                                    <div class="ih-muted">
                                        {{ $lesson->category ?? 'No category' }}
                                    </div>
                                </td>

                                <td>
                                    {{ $lesson->modules_count }}
                                </td>

                                <td>
                                    {{ $lesson->topics_count }}
                                </td>

                                <td>
                                    {{ $lesson->enrollments_count }}
                                </td>

                                <td>
                                    {{ $lesson->questions_count }}
                                </td>

                                <td>

                                    @if($lesson->is_published)

                                        <span class="ih-badge green">
                                            Published
                                        </span>

                                    @else

                                        <span class="ih-badge gray">
                                            Draft
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    <a
                                        href="{{ route('lessons.show', $lesson->slug) }}"
                                        class="ih-link"
                                    >
                                        View
                                    </a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7">
                                    No lessons available.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

        {{-- RECENT QUESTIONS --}}
        <section class="ih-section">

            <div class="ih-section-header">

                <h3 class="ih-section-title">
                    Recent Questions
                </h3>

                <p class="ih-section-description">
                    Recent questions from students in your lessons.
                </p>

            </div>

            @forelse($recentQuestions as $question)

                <div class="ih-question">

                    <div class="ih-question-row">

                        <div>

                            <div class="ih-name">
                                {{ $question->user?->name ?? 'Student' }}
                            </div>

                            <div class="ih-muted">
                                {{ $question->lesson?->title ?? 'Lesson' }}
                                ·
                                {{ $question->created_at?->format('d M Y, H:i') }}
                            </div>

                            <div class="ih-question-text">
                                {{ $question->question }}
                            </div>

                        </div>

                        <div>

                            @if($question->answer)

                                <span class="ih-badge green">
                                    Answered
                                </span>

                            @else

                                <span class="ih-badge yellow">
                                    Pending
                                </span>

                            @endif

                            <div style="margin-top: 8px;">
                                <a
                                    href="{{ route('instructor.questions.show', $question) }}"
                                    class="ih-link"
                                >
                                    {{ $question->answer
                                        ? 'Edit Answer'
                                        : 'Answer Question'
                                    }}
                                </a>
                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="ih-empty">
                    <div class="ih-empty-title">
                        No questions yet.
                    </div>
                </div>

            @endforelse

        </section>

    </div>

</x-filament-panels::page>