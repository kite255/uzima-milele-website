

<?php $__env->startSection('title', $quiz->title); ?>

<?php $__env->startSection('content'); ?>

<?php
    $result = session('result');
    $review = session('review', []);

    $lesson = $quiz->lesson
        ?? $quiz->module?->lesson
        ?? $quiz->topic?->module?->lesson
        ?? null;

    $quizTypeLabel = $quiz->quiz_type === 'kupimwa'
        ? 'Jaribio la Kupimwa'
        : 'Jaribio la Kujipima';

    $requiredLabel = $quiz->is_required
        ? 'Lazima kufaulu'
        : 'Hiari';

    $requiredClass = $quiz->is_required
        ? 'bg-red-50 text-red-700 border-red-200'
        : 'bg-blue-50 text-blue-700 border-blue-200';

    $typeClass = $quiz->quiz_type === 'kupimwa'
        ? 'bg-amber-50 text-amber-700 border-amber-200'
        : 'bg-green-50 text-green-700 border-green-200';
?>

<section class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow p-6 md:p-8">

            <div class="mb-8">
                <a href="<?php echo e($lesson ? route('lessons.show', $lesson->slug) : route('student.dashboard')); ?>"
                   class="text-sm text-primary font-bold hover:underline">
                    ← Rudi kwenye somo
                </a>

                <h1 class="mt-4 text-3xl md:text-4xl font-black text-navy">
                    <?php echo e($quiz->title); ?>

                </h1>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quiz->description): ?>
                    <p class="mt-3 text-gray-600 leading-7">
                        <?php echo e($quiz->description); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold <?php echo e($typeClass); ?>">
                        <?php echo e($quizTypeLabel); ?>

                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold <?php echo e($requiredClass); ?>">
                        <?php echo e($requiredLabel); ?>

                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold bg-gray-50 text-gray-700 border-gray-200">
                        Alama ya kufaulu: <?php echo e($quiz->pass_mark); ?>%
                    </span>

                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-bold bg-gray-50 text-gray-700 border-gray-200">
                        Maswali: <?php echo e($quiz->questions->count()); ?>

                    </span>
                </div>

                <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 leading-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quiz->quiz_type === 'kujipima'): ?>
                        Jaribio hili ni la kujipima. Linakusaidia kupima uelewa wako, lakini halizuii kuendelea na somo.
                    <?php else: ?>
                        Jaribio hili ni la kupimwa. Unahitaji kupata angalau <?php echo e($quiz->pass_mark); ?>% ili kufaulu.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quiz->is_required): ?>
                        <br>
                        <strong>Angalizo:</strong> Mwalimu ameweka jaribio hili kuwa la lazima kufaulu.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 font-semibold">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result): ?>
                <div class="mb-8 p-5 rounded-xl border
                    <?php echo e($result['passed'] ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'); ?>">

                    <p class="font-black text-xl">
                        Alama: <?php echo e($result['score']); ?>%
                    </p>

                    <p class="mt-1">
                        Majibu sahihi: <?php echo e($result['correct']); ?> / <?php echo e($result['total']); ?>

                    </p>

                    <p class="mt-2 font-semibold">
                        <?php echo e($result['passed'] ? 'Hongera! Umefaulu jaribio hili.' : 'Hujafaulu bado. Unaweza kurudia tena.'); ?>

                    </p>

                    <p class="mt-2 text-sm">
                        Alama ya kufaulu: <?php echo e($result['pass_mark'] ?? $quiz->pass_mark); ?>%
                    </p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$result): ?>
                <form method="POST" action="<?php echo e(route('quiz.submit', $quiz->id)); ?>">
                    <?php echo csrf_field(); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-8 rounded-2xl border border-gray-200 p-5">

                            <p class="font-bold text-navy mb-4 leading-7">
                                <?php echo e($index + 1); ?>. <?php echo e($question->question); ?>

                            </p>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['question_' . $question->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mb-3 text-sm text-red-600 font-semibold">
                                    <?php echo e($message); ?>

                                </p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->type === 'true_false'): ?>
                                <label class="flex items-center gap-3 border rounded-xl p-4 mb-3 cursor-pointer hover:border-primary hover:bg-gray-50 transition">
                                    <input
                                        type="radio"
                                        name="question_<?php echo e($question->id); ?>"
                                        value="true"
                                        required
                                        class="text-primary focus:ring-primary"
                                    >
                                    <span class="text-sm text-gray-700">Kweli</span>
                                </label>

                                <label class="flex items-center gap-3 border rounded-xl p-4 mb-3 cursor-pointer hover:border-primary hover:bg-gray-50 transition">
                                    <input
                                        type="radio"
                                        name="question_<?php echo e($question->id); ?>"
                                        value="false"
                                        required
                                        class="text-primary focus:ring-primary"
                                    >
                                    <span class="text-sm text-gray-700">Siyo kweli</span>
                                </label>
                            <?php else: ?>
                                <?php
                                    $options = $question->options;

                                    if (is_string($options)) {
                                        $decodedOptions = json_decode($options, true);
                                        $options = json_last_error() === JSON_ERROR_NONE ? $decodedOptions : [];
                                    }

                                    $options = is_array($options) ? $options : [];
                                ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionIndex => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $optionText = is_array($option)
                                            ? ($option['text'] ?? $option['label'] ?? json_encode($option))
                                            : $option;
                                    ?>

                                    <label class="flex items-center gap-3 border rounded-xl p-4 mb-3 cursor-pointer hover:border-primary hover:bg-gray-50 transition">
                                        <input
                                            type="radio"
                                            name="question_<?php echo e($question->id); ?>"
                                            value="<?php echo e($optionIndex); ?>"
                                            required
                                            class="text-primary focus:ring-primary"
                                        >

                                        <span class="text-sm text-gray-700">
                                            <?php echo e($optionText); ?>

                                        </span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button type="submit"
                            class="w-full bg-primary hover:bg-primaryDark text-white font-bold py-4 rounded-xl transition">
                        Wasilisha Majibu
                    </button>
                </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result && count($review)): ?>
                <div class="mt-8">
                    <h2 class="text-2xl font-black text-navy mb-4">
                        Mapitio ya Majibu
                    </h2>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $item = $review[$question->id] ?? null;

                            $options = $question->options;

                            if (is_string($options)) {
                                $decodedOptions = json_decode($options, true);
                                $options = json_last_error() === JSON_ERROR_NONE ? $decodedOptions : [];
                            }

                            $options = is_array($options) ? $options : [];

                            $userAnswer = $item['user_answer'] ?? null;
                            $correctAnswer = $item['correct_answer'] ?? null;

                            if ($question->type === 'multiple_choice') {
                                $userAnswerText = isset($options[(int) $userAnswer])
                                    ? ($options[(int) $userAnswer]['text'] ?? $options[(int) $userAnswer]['label'] ?? 'Haijulikani')
                                    : 'Haijulikani';

                                $correctAnswerText = isset($options[(int) $correctAnswer])
                                    ? ($options[(int) $correctAnswer]['text'] ?? $options[(int) $correctAnswer]['label'] ?? 'Haijulikani')
                                    : 'Haijulikani';
                            } else {
                                $userAnswerText = $userAnswer === 'true' ? 'Kweli' : 'Siyo kweli';
                                $correctAnswerText = $correctAnswer === 'true' ? 'Kweli' : 'Siyo kweli';
                            }
                        ?>

                        <div class="mb-4 rounded-xl border p-4 <?php echo e(($item['is_correct'] ?? false) ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'); ?>">
                            <p class="font-bold text-navy">
                                <?php echo e($index + 1); ?>. <?php echo e($question->question); ?>

                            </p>

                            <p class="mt-2 text-sm text-gray-700">
                                Jibu lako:
                                <strong><?php echo e($userAnswerText); ?></strong>
                            </p>

                            <p class="mt-1 text-sm text-gray-700">
                                Jibu sahihi:
                                <strong><?php echo e($correctAnswerText); ?></strong>
                            </p>

                            <p class="mt-2 text-sm font-bold <?php echo e(($item['is_correct'] ?? false) ? 'text-green-700' : 'text-red-700'); ?>">
                                <?php echo e(($item['is_correct'] ?? false) ? 'Sahihi' : 'Si sahihi'); ?>

                            </p>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question->explanation): ?>
                                <p class="mt-3 text-sm text-gray-700 leading-6">
                                    <strong>Maelezo:</strong> <?php echo e($question->explanation); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result): ?>
                <div class="grid md:grid-cols-3 gap-4 mt-8">

                    <a href="<?php echo e(route('quiz.show', $quiz->id)); ?>"
                       class="text-center bg-gray-100 hover:bg-gray-200 text-navy font-bold py-3 rounded-xl transition">
                        Jaribu Tena
                    </a>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lesson): ?>
                        <a href="<?php echo e(route('lessons.show', $lesson->slug)); ?>"
                           class="text-center bg-primary hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                            Endelea Kujifunza
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <a href="<?php echo e(route('student.dashboard')); ?>"
                       class="text-center bg-navy hover:bg-primaryDark text-white font-bold py-3 rounded-xl transition">
                        Tazama Dashibodi
                    </a>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Downloads\UzimaMilelefiles\UzimaSite\uzima-website\resources\views\quiz\show.blade.php ENDPATH**/ ?>