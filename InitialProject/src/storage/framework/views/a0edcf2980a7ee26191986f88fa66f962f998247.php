

<style>
    .container {
        padding: 20px;
    }

    /* Header section */
    .blue-stripe {
        background-color: #003e80;
        padding: 30px 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .blue-stripe h1 {
        color: white;
        font-size: 2.2rem;
        font-weight: 600;
        margin: 0;
    }

    /* Content boxes */
    .research-rationale-box {
        background-color: white;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }

    /* Headings */
    .research-rationale-box h2 {
        color: #003e80;
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaeaea;
    }

    .research-rationale-box h3 {
        color: #333;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Member cards */
    .member-card {
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
        background-color: white;
        border: 1px solid #eaeaea;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .head-lab-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #003e80;
        color: white;
        padding: 4px 10px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Image styling */
    .center-image {
        width: 100%;
        max-width: 200px;
        height: auto;
        margin-bottom: 15px;
        border: 1px solid #eaeaea;
        object-fit: contain;
        aspect-ratio: 3/4;
    }

    /* Profile link styles */
    .profile-link {
        display: inline-block;
        text-decoration: none;
    }

    /* Person info */
    .person-info {
        margin-top: 12px;
    }

    .person-info p {
        color: #333;
        font-size: 1.1rem;
        font-weight: 500;
        margin: 5px 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .blue-stripe h1 {
            font-size: 1.8rem;
        }

        .research-rationale-box {
            padding: 20px;
        }

        .research-rationale-box h2 {
            font-size: 1.4rem;
        }

        .center-image {
            max-width: 160px;
        }
    }
</style>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <?php $__currentLoopData = $resgd; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- Blue Stripe with Group Name -->
    <div class="blue-stripe">
        <h1 class="text-center"><?php echo e($rg->{'group_name_'.app()->getLocale()}); ?></h1>
    </div>

    <!-- Research Rationale -->
    <div class="research-rationale-box">
        <h2>Research Rationale</h2>
        <h3><?php echo e($rg->{'group_desc_'.app()->getLocale()}); ?></h3>
    </div>

    <!-- Researcher Details -->
    <div class="research-rationale-box">
        <h2>Researcher Details</h2>
        <h3><?php echo e($rg->{'group_detail_'.app()->getLocale()}); ?></h3>
    </div>

    <!-- Research Group Members (Teachers) -->
    <div class="research-rationale-box">
        <h2 class="text-center">Member Of Research Group</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            <?php $__currentLoopData = $rg->user; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(
            $r->hasRole('teacher')
            && isset($r->pivot)
            && in_array($r->pivot->role, [1, 2])
            ): ?>
            <div class="col">
                <div class="member-card">
                    <?php if($r->pivot->role == 1): ?>
                    <div class="head-lab-badge">Head LAB</div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('detail', Crypt::encrypt($r->id))); ?>" class="profile-link">
                        <img src="<?php echo e($r->picture ?? asset('img/default-profile.png')); ?>"
                            alt="<?php echo e($r->{'fname_'.app()->getLocale()}); ?> <?php echo e($r->{'lname_'.app()->getLocale()}); ?>"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <!-- Logic แสดงตำแหน่ง/ชื่อ ตาม locale -->
                        <?php if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.'): ?>
                        <p><?php echo e($r->{'fname_en'}); ?> <?php echo e($r->{'lname_en'}); ?>, Ph.D.</p>
                        <?php elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer'): ?>
                        <p><?php echo e($r->{'fname_en'}); ?> <?php echo e($r->{'lname_en'}); ?></p>
                        <?php elseif(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.'): ?>
                        <p><?php echo e(str_replace('Dr.', ' ', $r->position_en)); ?> <?php echo e($r->fname_en); ?> <?php echo e($r->lname_en); ?>, Ph.D.</p>
                        <?php else: ?>
                        <p><?php echo e($r->{'position_'.app()->getLocale()}); ?> <?php echo e($r->{'fname_'.app()->getLocale()}); ?> <?php echo e($r->{'lname_'.app()->getLocale()}); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Postdoctoral Researchers -->
    <div class="research-rationale-box">
        <h2 class="text-center">Postdoctoral Researchers</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            <?php $__currentLoopData = $rg->user; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($r->pivot) && $r->pivot->role == 3): ?>
            <div class="col">
                <div class="member-card">
                    <a href="<?php echo e(route('detail', Crypt::encrypt($r->id))); ?>" class="profile-link">
                        <img src="<?php echo e($r->picture ?? asset('img/default-profile.png')); ?>"
                            alt="<?php echo e($r->{'fname_'.app()->getLocale()}); ?> <?php echo e($r->{'lname_'.app()->getLocale()}); ?>"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <?php if(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.'): ?>
                        <p><?php echo e($r->{'fname_en'}); ?> <?php echo e($r->{'lname_en'}); ?>, Ph.D.</p>
                        <?php else: ?>
                        <p><?php echo e($r->{'position_'.app()->getLocale()}); ?> <?php echo e($r->{'fname_'.app()->getLocale()}); ?> <?php echo e($r->{'lname_'.app()->getLocale()}); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Students -->
    <div class="research-rationale-box">
        <h2 class="text-center">Student</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            <?php
            $uniqueStudents = $rg->user->unique('id')->filter(function($user) {
            return $user->hasRole('student');
            });
            ?>
            <?php $__currentLoopData = $uniqueStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col">
                <div class="member-card">
                    <a href="<?php echo e(route('detail', Crypt::encrypt($user->id))); ?>" class="profile-link">
                        <img src="<?php echo e($user->picture ?? asset('img/default-profile.png')); ?>"
                            alt="<?php echo e($user->{'fname_'.app()->getLocale()}); ?> <?php echo e($user->{'lname_'.app()->getLocale()}); ?>"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <p><?php echo e($user->{'position_'.app()->getLocale()}); ?> <?php echo e($user->{'fname_'.app()->getLocale()}); ?> <?php echo e($user->{'lname_'.app()->getLocale()}); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\WINDOWS\Documents\GitHub\git-group-repository-group-3-sec-2-v-2\InitialProject\src\resources\views/researchgroupdetail.blade.php ENDPATH**/ ?>