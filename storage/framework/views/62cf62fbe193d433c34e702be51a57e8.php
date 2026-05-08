

<?php $__env->startSection('content'); ?>

<section class="min-h-screen flex items-center justify-center bg-gray-50 px-4">

    <div class="text-center max-w-xl">

        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            <?php echo e($title); ?>

        </h1>

        <p class="text-gray-600 mb-8">
            <?php echo e($message); ?>

        </p>

        <a href="<?php echo e(route('home')); ?>"
           class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition">
            Rudi Nyumbani
        </a>

    </div>

</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\coming-soon.blade.php ENDPATH**/ ?>