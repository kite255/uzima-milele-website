<nav class="bg-white shadow sticky top-0 z-50" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

        
        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2">
            <img src="<?php echo e(asset('logo.png')); ?>" alt="Uzima Milele" class="h-10">
            <span class="font-bold text-lg text-navy">Uzima Milele</span>
        </a>

        
        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700">

            <a href="<?php echo e(route('home')); ?>"
               class="hover:text-primary <?php echo e(request()->routeIs('home') ? 'text-primary font-semibold' : ''); ?>">
                Nyumbani
            </a>

            <a href="<?php echo e(route('about')); ?>"
               class="hover:text-primary <?php echo e(request()->routeIs('about') ? 'text-primary font-semibold' : ''); ?>">
                Kuhusu sisi
            </a>

            <a href="<?php echo e(route('devotions.index')); ?>"
               class="hover:text-primary <?php echo e(request()->routeIs('devotions.*') ? 'text-primary font-semibold' : ''); ?>">
                Tafakari
            </a>

            
           
           
           
          

            <a href="<?php echo e(route('children.index')); ?>"
               class="hover:text-primary <?php echo e(request()->routeIs('children.*') ? 'text-primary font-semibold' : ''); ?>">
                Watoto
            </a>

            <a href="<?php echo e(route('prayers.testimonies')); ?>"
               class="hover:text-primary <?php echo e(request()->routeIs('prayers.testimonies') ? 'text-primary font-semibold' : ''); ?>">
                Maombi & ushuhuda
            </a>

            <a href="<?php echo e(route('lessons.index')); ?>"
               class="hover:text-primary <?php echo e(request()->routeIs('lessons.*') ? 'text-primary font-semibold' : ''); ?>">
                Jifunze Biblia
            </a>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('notifications.index')): ?>
                    <a href="<?php echo e(route('notifications.index')); ?>"
                       title="Notifications"
                       class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border transition
                              <?php echo e(request()->is('notifications*')
                                    ? 'bg-primary text-white border-primary shadow-md'
                                    : 'bg-primary/10 text-primary border-primary/20 hover:bg-primary hover:text-white hover:border-primary hover:shadow-md'); ?>">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                        </svg>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($unreadNotificationsCount ?? 0) > 0): ?>
                            <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-accent px-1.5 text-[10px] font-black text-navy ring-2 ring-white">
                                <?php echo e($unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>

        
        <div class="hidden md:flex items-center gap-3">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'admin'): ?>
                    <a href="/admin"
                       class="px-4 py-2 bg-navy text-white rounded-full font-semibold hover:bg-primaryDark transition">
                        Admin
                    </a>
                <?php elseif(auth()->user()->role === 'instructor' && Route::has('instructor.dashboard')): ?>
                    <a href="<?php echo e(route('instructor.dashboard')); ?>"
                       class="px-4 py-2 bg-navy text-white rounded-full font-semibold hover:bg-primaryDark transition">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('student.dashboard')); ?>"
                       class="px-4 py-2 bg-navy text-white rounded-full font-semibold hover:bg-primaryDark transition">
                        Dashboard
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>

                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-full font-semibold hover:bg-red-700 transition">
                        Toka
                    </button>
                </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <a href="/changia"
               class="px-5 py-2 bg-primary text-white rounded-full font-semibold hover:bg-primaryDark transition">
                Changia
            </a>
        </div>

        
        <button @click="open = !open" class="md:hidden text-navy">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-7 w-7"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path x-show="!open"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"/>

                <path x-show="open"
                      x-cloak
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

    </div>

    
    <div x-show="open" x-transition x-cloak class="md:hidden bg-white border-t px-4 pb-4 space-y-3">

        <a href="<?php echo e(route('home')); ?>"
           class="block py-2 <?php echo e(request()->routeIs('home') ? 'text-primary font-semibold' : ''); ?>">
            Nyumbani
        </a>

        <a href="<?php echo e(route('about')); ?>"
           class="block py-2 <?php echo e(request()->routeIs('about') ? 'text-primary font-semibold' : ''); ?>">
            Kuhusu sisi
        </a>

        <a href="<?php echo e(route('devotions.index')); ?>"
           class="block py-2 <?php echo e(request()->routeIs('devotions.*') ? 'text-primary font-semibold' : ''); ?>">
            Tafakari
        </a>


        
      

        <a href="<?php echo e(route('children.index')); ?>"
           class="block py-2 <?php echo e(request()->routeIs('children.*') ? 'text-primary font-semibold' : ''); ?>">
            Watoto
        </a>

        <a href="<?php echo e(route('prayers.testimonies')); ?>"
           class="block py-2 <?php echo e(request()->routeIs('prayers.testimonies') ? 'text-primary font-semibold' : ''); ?>">
            Maombi & ushuhuda
        </a>

        <a href="<?php echo e(route('lessons.index')); ?>"
           class="block py-2 <?php echo e(request()->routeIs('lessons.*') ? 'text-primary font-semibold' : ''); ?>">
            Jifunze Biblia
        </a>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('notifications.index')): ?>
                <a href="<?php echo e(route('notifications.index')); ?>"
                   class="flex items-center justify-between rounded-xl px-4 py-3 font-bold text-navy hover:bg-primary/10 hover:text-primary transition">

                    <span class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                            </svg>
                        </span>

                        Notifications
                    </span>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($unreadNotificationsCount ?? 0) > 0): ?>
                        <span class="rounded-full bg-accent px-2.5 py-1 text-xs font-black text-navy">
                            <?php echo e($unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'admin'): ?>
                <a href="/admin" class="block py-2 font-semibold text-navy">
                    Admin
                </a>
            <?php elseif(auth()->user()->role === 'instructor' && Route::has('instructor.dashboard')): ?>
                <a href="<?php echo e(route('instructor.dashboard')); ?>" class="block py-2 font-semibold text-navy">
                    Dashboard
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('student.dashboard')); ?>" class="block py-2 font-semibold text-navy">
                    Dashboard
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>

                <button type="submit"
                        class="w-full text-left rounded-xl bg-red-600 px-4 py-3 font-bold text-white hover:bg-red-700 transition">
                    Toka
                </button>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <a href="/changia"
           class="block mt-3 text-center bg-primary text-white py-2 rounded-full font-bold hover:bg-primaryDark transition">
            Changia
        </a>

    </div>
</nav><?php /**PATH /home/uzimamil/public_html/new.uzimamilele.or.tz/new/resources/views/partials/navbar.blade.php ENDPATH**/ ?>