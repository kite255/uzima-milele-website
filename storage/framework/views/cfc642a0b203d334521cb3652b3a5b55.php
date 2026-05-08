<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen bg-gradient-to-br from-[#EAF7FC] via-white to-[#FFF7E3] flex items-center justify-center px-4 py-10 font-lato">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                
                
                <div class="bg-[#0E3D4F] px-8 py-8 text-center">
                    <div class="flex justify-center mb-4">
                        <img src="<?php echo e(asset('logo.png')); ?>" 
                             alt="Uzima Milele" 
                             class="h-16 w-auto object-contain">
                    </div>

                    <h1 class="text-2xl font-black text-white">
                        Thibitisha Nenosiri
                    </h1>

                    <p class="text-sm text-white/80 mt-2">
                        Eneo hili ni salama. Tafadhali thibitisha nenosiri lako ili kuendelea.
                    </p>
                </div>

                
                <div class="px-8 py-8">
                    <form method="POST" action="<?php echo e(route('password.confirm')); ?>" class="space-y-5">
                        <?php echo csrf_field(); ?>

                        
                        <div>
                            <label for="password" class="block text-sm font-bold text-[#0E3D4F] mb-2">
                                Nenosiri
                            </label>

                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   autofocus
                                   placeholder="Weka nenosiri lako"
                                   class="w-full rounded-xl border-gray-300 focus:border-[#0083CB] focus:ring-[#0083CB] text-sm">

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-2 text-sm text-red-600">
                                    <?php echo e($message); ?>

                                </p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <button type="submit"
                                class="w-full rounded-xl bg-[#0083CB] hover:bg-[#076994] text-white font-bold py-3 transition">
                            Thibitisha
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="<?php echo e(route('dashboard')); ?>"
                           class="text-sm font-semibold text-[#0083CB] hover:text-[#076994]">
                            Rudi nyuma
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-gray-500 mt-6">
                © <?php echo e(date('Y')); ?> Uzima Milele. Haki zote zimehifadhiwa.
            </p>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\auth\confirm-password.blade.php ENDPATH**/ ?>