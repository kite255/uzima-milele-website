<x-filament-panels::page>

    <style>
        .ac-page {
            --ac-primary: #0083CB;
            --ac-primary-dark: #076994;
            --ac-navy: #0E3D4F;
            --ac-accent: #F4B122;

            display: flex;
            flex-direction: column;
            gap: 28px;
            width: 100%;
        }

        .ac-hero {
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

        .ac-hero::before,
        .ac-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.11);
        }

        .ac-hero::before {
            width: 190px;
            height: 190px;
            right: -45px;
            bottom: -80px;
        }

        .ac-hero::after {
            width: 95px;
            height: 95px;
            right: 115px;
            top: 25px;
        }

        .ac-hero-content {
            position: relative;
            z-index: 2;
        }

        .ac-eyebrow {
            margin: 0 0 8px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 800;
        }

        .ac-title {
            margin: 0;
            color: #ffffff;
            font-size: 34px;
            line-height: 1.15;
            font-weight: 900;
        }

        .ac-description {
            margin: 12px 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            line-height: 1.6;
        }

        .ac-stats {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 16px;
        }

        .ac-stat {
            min-width: 0;
            padding: 20px 22px;
            border: 1px solid #E2E8F0;
            border-radius: 18px;
            background: #ffffff;
        }

        .ac-stat.primary {
            border-top: 4px solid var(--ac-primary);
        }

        .ac-stat.navy {
            border-top: 4px solid var(--ac-navy);
        }

        .ac-stat.green {
            border-top: 4px solid #22C55E;
        }

        .ac-stat.accent {
            border-top: 4px solid var(--ac-accent);
        }

        .ac-stat.red {
            border-top: 4px solid #EF4444;
        }

        .ac-stat-label {
            color: #64748B;
            font-size: 13px;
            font-weight: 600;
        }

        .ac-stat-value {
            margin-top: 10px;
            color: var(--ac-navy);
            font-size: 30px;
            line-height: 1;
            font-weight: 900;
        }

        .ac-section {
            overflow: hidden;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            background: #ffffff;
        }

        .ac-section-header {
            padding: 22px 24px;
            border-bottom: 1px solid #E2E8F0;
        }

        .ac-section-title {
            margin: 0;
            color: var(--ac-navy);
            font-size: 22px;
            font-weight: 900;
        }

        .ac-actions {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            padding: 24px;
        }

        .ac-action {
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

        .ac-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        }

        .ac-action.blue {
            background: #E4F3FB;
            color: #0083CB;
        }

        .ac-action.navy {
            background: #EDF3F5;
            color: #0E3D4F;
        }

        .ac-action.yellow {
            background: #FFF3D6;
            color: #8A6100;
        }

        .ac-action.green {
            background: #ECFDF5;
            color: #047857;
        }

        .ac-action.red {
            background: #FFF0F0;
            color: #B91C1C;
        }

        .ac-action.gray {
            background: #F3F4F6;
            color: #0E3D4F;
        }

        @media (max-width: 1200px) {
            .ac-stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .ac-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .ac-hero {
                padding: 26px 22px;
            }

            .ac-title {
                font-size: 28px;
            }

            .ac-stats,
            .ac-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="ac-page">

        {{-- HERO --}}
        <section class="ac-hero">

            <div class="ac-hero-content">

                <p class="ac-eyebrow">
                    Admin Center
                </p>

                <h2 class="ac-title">
                    Karibu, {{ auth()->user()->name }}
                </h2>

                <p class="ac-description">
                    Manage users, instructors, students,
                    lessons, enrollments, questions and
                    learning operations across Uzima Milele.
                </p>

            </div>

        </section>

        {{-- STATS --}}
        <section class="ac-stats">

            <div class="ac-stat primary">
                <div class="ac-stat-label">
                    Users
                </div>

                <div class="ac-stat-value">
                    {{ $totalUsers }}
                </div>
            </div>

            <div class="ac-stat navy">
                <div class="ac-stat-label">
                    Students
                </div>

                <div class="ac-stat-value">
                    {{ $totalStudents }}
                </div>
            </div>

            <div class="ac-stat primary">
                <div class="ac-stat-label">
                    Instructors
                </div>

                <div class="ac-stat-value">
                    {{ $totalInstructors }}
                </div>
            </div>

            <div class="ac-stat green">
                <div class="ac-stat-label">
                    Lessons
                </div>

                <div class="ac-stat-value">
                    {{ $totalLessons }}
                </div>
            </div>

            <div class="ac-stat navy">
                <div class="ac-stat-label">
                    Enrollments
                </div>

                <div class="ac-stat-value">
                    {{ $totalEnrollments }}
                </div>
            </div>

            <div class="ac-stat accent">
                <div class="ac-stat-label">
                    Pending Questions
                </div>

                <div class="ac-stat-value">
                    {{ $pendingQuestions }}
                </div>
            </div>

        </section>

        {{-- QUICK ACTIONS --}}
        <section class="ac-section">

            <div class="ac-section-header">

                <h3 class="ac-section-title">
                    Quick Actions
                </h3>

            </div>

            <div class="ac-actions">

                <a
                    href="{{ url('/admin/users') }}"
                    class="ac-action blue"
                >
                    Manage Users
                </a>

                <a
                    href="{{ url('/admin/lessons') }}"
                    class="ac-action navy"
                >
                    Manage Lessons
                </a>

                <a
                    href="{{ url('/admin/instructor-assignments') }}"
                    class="ac-action yellow"
                >
                    Instructor Assignments
                </a>

                <a
                    href="{{ url('/admin/lesson-enrollments') }}"
                    class="ac-action green"
                >
                    Student Enrollments
                </a>

                <a
                    href="{{ url('/admin/quizzes') }}"
                    class="ac-action gray"
                >
                    Quizzes
                </a>

                <a
                    href="{{ url('/admin/lesson-questions') }}"
                    class="ac-action red"
                >
                    Lesson Q&A
                </a>

                <a
                    href="{{ url('/admin/lesson-modules') }}"
                    class="ac-action blue"
                >
                    Lesson Modules
                </a>

                <a
                    href="{{ url('/admin/lesson-topics') }}"
                    class="ac-action navy"
                >
                    Lesson Topics
                </a>

            </div>

        </section>

    </div>

</x-filament-panels::page>