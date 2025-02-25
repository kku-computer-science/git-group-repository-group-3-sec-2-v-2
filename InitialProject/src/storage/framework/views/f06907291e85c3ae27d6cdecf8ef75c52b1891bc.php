

<?php $__env->startSection('content'); ?>
<style>
    .img-cover {
        object-fit: cover;
        object-position: center;
    }

    .card-hover:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .search-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .custom-accordion .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: var(--bs-primary);
    }

    .custom-accordion .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, 0.125);
    }

    .expertise-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }

    .readmore-content.d-none {
        display: none;
    }

    .readmore-toggle {
        cursor: pointer;
        order: 1;
    }
</style>

<div class="container-fluid py-5 px-4">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold text-primary mb-4">Our Researchers</h1>
            <form method="GET" action="<?php echo e(route('researchers.index')); ?>" class="search-form">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control border-2 shadow-none" name="textsearch"
                        value="<?php echo e($search ?? ''); ?>" placeholder="Search researchers by name or interest..."
                        aria-label="Search researchers">
                    <button class="btn btn-primary px-4" type="submit">
                        <ion-icon name="search-outline" class="align-middle"></ion-icon>
                        <span class="ms-2">Search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Accordion Section -->
    <div class="accordion custom-accordion" id="programAccordion">
        <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($program->users->count() > 0): ?>
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
            <h2 class="accordion-header" id="heading<?php echo e($program->id); ?>">
                <button class="accordion-button fs-5 py-4 <?php echo e(in_array($program->id, $expandedProgramIds) ? '' : 'collapsed'); ?>"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($program->id); ?>"
                    aria-expanded="<?php echo e(in_array($program->id, $expandedProgramIds) ? 'true' : 'false'); ?>"
                    aria-controls="collapse<?php echo e($program->id); ?>">
                    <ion-icon name="school-outline" class="me-3 fs-4"></ion-icon>
                    <span class="fw-semibold"><?php echo e($program->program_name_en); ?></span>
                    <span class="badge bg-primary rounded-pill ms-3"><?php echo e($program->users->count()); ?></span>
                </button>
            </h2>

            <div id="collapse<?php echo e($program->id); ?>"
                class="accordion-collapse collapse <?php echo e(in_array($program->id, $expandedProgramIds) ? 'show' : ''); ?>"
                aria-labelledby="heading<?php echo e($program->id); ?>">
                <div class="accordion-body p-4">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                        <?php $__currentLoopData = $program->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col">
                            <a href="<?php echo e(route('detail', Crypt::encrypt($user->id))); ?>"
                                class="text-decoration-none card-hover">
                                <div class="card h-100 border-0 shadow-sm rounded-4 transition-all">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-4">
                                            <div class="h-100 position-relative">
                                                <img class="img-cover rounded-start h-100 w-100"
                                                    src="<?php echo e($user->picture ?? asset('img/default-profile.png')); ?>"
                                                    alt="<?php echo e($user->{'fname_'.app()->getLocale()}); ?>'s photo">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body p-4">
                                                <div class="d-flex flex-column h-100">
                                                    <h5 class="card-title text-primary mb-1">
                                                        <?php echo e($user->{'fname_'.app()->getLocale()}); ?>

                                                        <?php echo e($user->{'lname_'.app()->getLocale()}); ?>

                                                        <?php if($user->doctoral_degree): ?>
                                                        <span class="fs-6 text-muted">, <?php echo e($user->doctoral_degree); ?></span>
                                                        <?php endif; ?>
                                                    </h5>
                                                    <p class="text-muted mb-3"><?php echo e($user->position_en); ?></p>

                                                    <div class="email-section mb-3">
                                                        <a href="mailto:<?php echo e($user->email); ?>"
                                                            class="text-decoration-none text-primary">
                                                            <ion-icon name="mail-outline" class="align-middle me-1"></ion-icon>
                                                            <?php echo e($user->email); ?>

                                                        </a>
                                                    </div>

                                                    <div class="expertise-section mt-auto">
                                                        <h6 class="fw-bold mb-2">Research Interests</h6>
                                                        <div class="expertise-tags d-flex flex-wrap align-items-start gap-1">
                                                            <?php
                                                            $maxToShow = 3;
                                                            $expertiseCount = $user->expertise->count();
                                                            ?>

                                                            <?php $__currentLoopData = $user->expertise->sortBy('expert_name')->take($maxToShow); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expertise): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge bg-light text-primary">
                                                                <?php echo e($expertise->expert_name); ?>

                                                            </span>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                            <?php if($expertiseCount > $maxToShow): ?>
                                                            <div class="readmore-content d-none">
                                                                <?php $__currentLoopData = $user->expertise->sortBy('expert_name')->slice($maxToShow); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expertise): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <span class="badge bg-light text-primary">
                                                                    <?php echo e($expertise->expert_name); ?>

                                                                </span>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                            <span class="badge bg-light text-primary readmore-toggle"
                                                                onclick="toggleReadmore(this)">
                                                                +<?php echo e($expertiseCount - $maxToShow); ?> Readmore
                                                            </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<script>
    function toggleReadmore(element) {
        const readmoreContent = element.previousElementSibling;
        if (readmoreContent) {
            if (readmoreContent.classList.contains('d-none')) {
                readmoreContent.classList.remove('d-none');
                element.textContent = "Show less";
            } else {
                readmoreContent.classList.add('d-none');
                const count = readmoreContent.querySelectorAll('.badge').length;
                element.textContent = `+${count} Readmore`;
            }
            element.parentNode.appendChild(element);
        }
    }
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\WINDOWS\Documents\GitHub\git-group-repository-group-3-sec-2-v-2\InitialProject\src\resources\views/researchers/index.blade.php ENDPATH**/ ?>