<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'Uzima Milele'); ?></title>

    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">

    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        lato: ['Lato', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0083CB',
                        primaryDark: '#076994',
                        navy: '#0E3D4F',
                        accent: '#F4B122',
                    }
                }
            }
        }
    </script>
</head>

<body class="font-lato bg-gray-50 text-gray-900 antialiased">

    <?php
        $unreadNotificationsCount = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;
    ?>

    <?php if ($__env->exists('partials.navbar', [
        'unreadNotificationsCount' => $unreadNotificationsCount
    ])) echo $__env->make('partials.navbar', [
        'unreadNotificationsCount' => $unreadNotificationsCount
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <main class="min-h-screen pt-24 md:pt-28">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php if ($__env->exists('partials.footer', [
        'unreadNotificationsCount' => $unreadNotificationsCount
    ])) echo $__env->make('partials.footer', [
        'unreadNotificationsCount' => $unreadNotificationsCount
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/layouts/app.blade.php ENDPATH**/ ?>