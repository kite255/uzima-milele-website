<x-filament-panels::page>

    <style>
        .ia-page {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .ia-summary-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(240px, 1fr);
            gap: 16px;
        }

        .ia-summary-card,
        .ia-assignment-card,
        .ia-empty-card {
            border: 1px solid #E5E7EB;
            border-radius: 14px;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .ia-summary-card {
            padding: 22px 24px;
        }

        .ia-summary-content {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .ia-icon-box {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #EFF8FF;
            color: #0083CB;
        }

        .ia-icon-box svg {
            width: 24px;
            height: 24px;
        }

        .ia-summary-text {
            min-width: 0;
        }

        .ia-summary-title {
            margin: 0;
            color: #111827;
            font-size: 18px;
            line-height: 1.35;
            font-weight: 800;
        }

        .ia-summary-description {
            margin: 5px 0 0;
            color: #64748B;
            font-size: 14px;
            line-height: 1.6;
        }

        .ia-stat {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .ia-stat-label {
            margin: 0;
            color: #64748B;
            font-size: 14px;
            font-weight: 600;
        }

        .ia-stat-value {
            margin: 4px 0 0;
            color: #111827;
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
        }

        .ia-assignments {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .ia-assignment-card {
            padding: 22px 24px;
        }

        .ia-assignment-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 140px;
            gap: 24px;
            align-items: start;
        }

        .ia-instructor {
            min-width: 0;
        }

        .ia-name-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .ia-instructor-name {
            margin: 0;
            color: #111827;
            font-size: 18px;
            line-height: 1.35;
            font-weight: 800;
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .ia-role {
            display: inline-flex;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            background: #EFF8FF;
            color: #076994;
            font-size: 11px;
            line-height: 1.2;
            font-weight: 800;
            white-space: nowrap;
        }

        .ia-lesson-title {
            margin: 8px 0 0;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
        }

        .ia-contact-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            margin-top: 14px;
        }

        .ia-contact-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #0083CB;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            overflow-wrap: anywhere;
        }

        .ia-contact-link:hover {
            text-decoration: underline;
        }

        .ia-contact-link svg {
            width: 17px;
            height: 17px;
            flex: 0 0 17px;
        }

        .ia-student-count {
            min-width: 140px;
            padding: 14px 16px;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            background: #F8FAFC;
            text-align: center;
        }

        .ia-student-count-number {
            color: #0083CB;
            font-size: 28px;
            line-height: 1;
            font-weight: 900;
        }

        .ia-student-count-label {
            margin-top: 5px;
            color: #64748B;
            font-size: 12px;
            font-weight: 700;
        }

        .ia-students-section {
            margin-top: 20px;
        }

        .ia-students-heading {
            margin: 0 0 10px;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
        }

        .ia-table-wrap {
            overflow-x: auto;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
        }

        .ia-table {
            width: 100%;
            border-collapse: collapse;
            background: #FFFFFF;
        }

        .ia-table thead {
            background: #F8FAFC;
        }

        .ia-table th {
            padding: 12px 16px;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            text-align: left;
            white-space: nowrap;
            border-bottom: 1px solid #E2E8F0;
        }

        .ia-table td {
            padding: 13px 16px;
            color: #334155;
            font-size: 13px;
            border-bottom: 1px solid #E2E8F0;
            vertical-align: middle;
        }

        .ia-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .ia-table tbody tr:hover {
            background: #F8FAFC;
        }

        .ia-student-name {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 180px;
        }

        .ia-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #F1F5F9;
            color: #475569;
            font-size: 13px;
            font-weight: 800;
        }

        .ia-student-full-name {
            color: #111827;
            font-weight: 700;
        }

        .ia-table-link {
            color: #0083CB;
            font-weight: 600;
            text-decoration: none;
        }

        .ia-table-link:hover {
            text-decoration: underline;
        }

        .ia-muted {
            color: #94A3B8;
        }

        .ia-no-students {
            margin-top: 20px;
            padding: 26px 18px;
            border: 1px dashed #CBD5E1;
            border-radius: 12px;
            background: #F8FAFC;
            text-align: center;
        }

        .ia-no-students-icon {
            width: 42px;
            height: 42px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #F1F5F9;
            color: #64748B;
        }

        .ia-no-students-icon svg {
            width: 21px;
            height: 21px;
        }

        .ia-no-students-text {
            margin: 10px 0 0;
            color: #64748B;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }

        .ia-empty-card {
            padding: 36px 24px;
            text-align: center;
        }

        .ia-empty-icon {
            width: 54px;
            height: 54px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #EFF8FF;
            color: #0083CB;
        }

        .ia-empty-icon svg {
            width: 28px;
            height: 28px;
        }

        .ia-empty-title {
            margin: 16px 0 0;
            color: #111827;
            font-size: 18px;
            font-weight: 800;
        }

        .ia-empty-text {
            max-width: 540px;
            margin: 8px auto 0;
            color: #64748B;
            font-size: 14px;
            line-height: 1.6;
        }

        @media (max-width: 900px) {
            .ia-summary-grid {
                grid-template-columns: 1fr;
            }

            .ia-assignment-header {
                grid-template-columns: 1fr;
            }

            .ia-student-count {
                width: 100%;
                min-width: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                text-align: left;
            }

            .ia-student-count-label {
                margin-top: 0;
                order: -1;
            }
        }

        @media (max-width: 640px) {
            .ia-summary-card,
            .ia-assignment-card {
                padding: 18px;
            }

            .ia-summary-content {
                gap: 12px;
            }

            .ia-summary-title,
            .ia-instructor-name {
                font-size: 16px;
            }

            .ia-contact-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .ia-table th,
            .ia-table td {
                padding: 11px 13px;
            }
        }
    </style>

    <div class="ia-page">

        {{-- SUMMARY --}}
        <div class="ia-summary-grid">

            <div class="ia-summary-card">
                <div class="ia-summary-content">

                    <div class="ia-icon-box">
                        <x-heroicon-o-user-group />
                    </div>

                    <div class="ia-summary-text">

                        <h2 class="ia-summary-title">
                            Instructor Assignments
                        </h2>

                        <p class="ia-summary-description">
                            View each lesson instructor and the students
                            currently assigned to them.
                        </p>

                    </div>

                </div>
            </div>

            <div class="ia-summary-card">

                <div class="ia-stat">

                    <div>

                        <p class="ia-stat-label">
                            Total Assignments
                        </p>

                        <p class="ia-stat-value">
                            {{ $rows->count() }}
                        </p>

                    </div>

                    <div class="ia-icon-box">
                        <x-heroicon-o-academic-cap />
                    </div>

                </div>

            </div>

        </div>

        {{-- ASSIGNMENTS --}}
        <div class="ia-assignments">

            @forelse($rows as $row)

                <div class="ia-assignment-card">

                    <div class="ia-assignment-header">

                        {{-- INSTRUCTOR INFORMATION --}}
                        <div class="ia-instructor">

                            <div class="ia-name-row">

                                <h2 class="ia-instructor-name">
                                    {{ $row['instructor_name'] }}
                                </h2>

                                <span class="ia-role">
                                    {{ $row['assignment_role'] }}
                                </span>

                            </div>

                            <p class="ia-lesson-title">
                                {{ $row['lesson_title'] }}
                            </p>

                            @if(
                                $row['instructor_email']
                                || $row['instructor_phone']
                            )

                                <div class="ia-contact-row">

                                    @if($row['instructor_email'])

                                        <a
                                            href="mailto:{{ $row['instructor_email'] }}"
                                            class="ia-contact-link"
                                        >
                                            <x-heroicon-o-envelope />

                                            <span>
                                                {{ $row['instructor_email'] }}
                                            </span>
                                        </a>

                                    @endif

                                    @if($row['instructor_phone'])

                                        <a
                                            href="tel:{{ $row['instructor_phone'] }}"
                                            class="ia-contact-link"
                                        >
                                            <x-heroicon-o-phone />

                                            <span>
                                                {{ $row['instructor_phone'] }}
                                            </span>
                                        </a>

                                    @endif

                                </div>

                            @endif

                        </div>

                        {{-- STUDENT COUNT --}}
                        <div class="ia-student-count">

                            <div class="ia-student-count-number">
                                {{ $row['student_count'] }}
                            </div>

                            <div class="ia-student-count-label">
                                {{ $row['student_count'] === 1
                                    ? 'Student'
                                    : 'Students'
                                }}
                            </div>

                        </div>

                    </div>

                    {{-- STUDENTS --}}
                    @if($row['students']->isNotEmpty())

                        <div class="ia-students-section">

                            <h3 class="ia-students-heading">
                                Assigned Students
                            </h3>

                            <div class="ia-table-wrap">

                                <table class="ia-table">

                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($row['students'] as $student)

                                            <tr>

                                                <td>

                                                    <div class="ia-student-name">

                                                        <div class="ia-avatar">
                                                            {{ strtoupper(
                                                                substr(
                                                                    $student['name'],
                                                                    0,
                                                                    1
                                                                )
                                                            ) }}
                                                        </div>

                                                        <span
                                                            class="ia-student-full-name"
                                                        >
                                                            {{ $student['name'] }}
                                                        </span>

                                                    </div>

                                                </td>

                                                <td>

                                                    @if($student['email'])

                                                        <a
                                                            href="mailto:{{ $student['email'] }}"
                                                            class="ia-table-link"
                                                        >
                                                            {{ $student['email'] }}
                                                        </a>

                                                    @else

                                                        <span class="ia-muted">
                                                            —
                                                        </span>

                                                    @endif

                                                </td>

                                                <td>

                                                    @if($student['phone'])

                                                        <a
                                                            href="tel:{{ $student['phone'] }}"
                                                            class="ia-table-link"
                                                        >
                                                            {{ $student['phone'] }}
                                                        </a>

                                                    @else

                                                        <span class="ia-muted">
                                                            —
                                                        </span>

                                                    @endif

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    @else

                        <div class="ia-no-students">

                            <div class="ia-no-students-icon">
                                <x-heroicon-o-users />
                            </div>

                            <p class="ia-no-students-text">
                                No students are currently assigned to this
                                instructor for this lesson.
                            </p>

                        </div>

                    @endif

                </div>

            @empty

                <div class="ia-empty-card">

                    <div class="ia-empty-icon">
                        <x-heroicon-o-user-group />
                    </div>

                    <h2 class="ia-empty-title">
                        No instructor assignments yet
                    </h2>

                    <p class="ia-empty-text">
                        Configure lead and follow-up instructors on a lesson
                        to see their assignments here.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</x-filament-panels::page>