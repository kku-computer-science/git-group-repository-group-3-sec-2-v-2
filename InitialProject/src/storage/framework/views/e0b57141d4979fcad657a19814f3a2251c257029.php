
<?php $__env->startSection('content'); ?>
<div class="container">
    <!-- Blue Stripe Section -->
    <div class="blue-stripe">
        <h1 class="text-center">Research Group</h1>
        <div class="row mb-3">
            <div class="col-md-6 offset-md-3">
                <input type="text" id="searchInput" class="form-control" placeholder="ค้นหากลุ่มวิจัย...">
            </div>
        </div>
    </div>

    <!-- Research Group Cards -->
    <div id="researchGroupList" class="row row-cols-1 row-cols-md-3 g-4">
        <?php $__currentLoopData = $resg; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col research-group-item">
                <div class="card h-100">
                    <a href="<?php echo e(route('researchgroupdetail', ['id' => $rg->id])); ?>" class="text-decoration-none">
                        <div class="group-image-container">
                            <img src="<?php echo e(asset('img/'.$rg->group_image)); ?>" alt="Group Image" class="group-image">
                            <div class="overlay">
                                <h5 class="group-name"><?php echo e($rg->{'group_name_'.app()->getLocale()}); ?></h5>
                                <div class="group-description">
                                    <?php echo e(Str::limit($rg->{'group_desc_'.app()->getLocale()}, 150)); ?>

                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<style>
/* Container styles */
.container {
    padding: 20px;
}

/* Blue stripe styles */
.blue-stripe {
    background-color: #003e80;
    padding: 30px 20px;
    border-radius: 8px;
    margin-bottom: 40px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.blue-stripe h1 {
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 25px;
}

.blue-stripe .form-control {
    background-color: white;
    border: none;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 1.1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.blue-stripe .form-control:focus {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    outline: none;
}

/* Card styles */
.card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
}

/* Image container and hover effects */
.group-image-container {
    position: relative;
    width: 100%;
    padding-top: 75%; /* 4:3 Aspect Ratio */
    overflow: hidden;
}

.group-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.3),
        rgba(0, 0, 0, 0.7)
    );
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
    text-align: center;
    transition: opacity 0.3s ease;
}

.group-name {
    color: white;
    font-size: 1.4rem;
    font-weight: bold;
    margin-bottom: 15px;
    transition: transform 0.3s ease;
}

.group-description {
    color: white;
    font-size: 1rem;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Hover effects */
.group-image-container:hover .group-image {
    transform: scale(1.1);
}

.group-image-container:hover .overlay {
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.5),
        rgba(0, 0, 0, 0.8)
    );
}

.group-image-container:hover .group-name {
    transform: translateY(-10px);
}

.group-image-container:hover .group-description {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .blue-stripe h1 {
        font-size: 2rem;
    }
    
    .group-name {
        font-size: 1.2rem;
    }
    
    .group-description {
        font-size: 0.9rem;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        var searchValue = $(this).val().toLowerCase();
        $('.research-group-item').each(function() {
            var groupName = $(this).find('.group-name').text().toLowerCase();
            $(this).toggle(groupName.includes(searchValue));
        });
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\WINDOWS\Documents\GitHub\git-group-repository-group-3-sec-2-v-2\InitialProject\src\resources\views/research_g.blade.php ENDPATH**/ ?>