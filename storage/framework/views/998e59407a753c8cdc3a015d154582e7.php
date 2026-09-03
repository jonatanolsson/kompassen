<?php
$classes = Flux::classes()
    ->add('');
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-timeline-block>
   <?php echo e($slot); ?>

</div><?php /**PATH /var/www/html/local_packages/flux-pro-main/stubs/resources/views/flux/timeline/block.blade.php ENDPATH**/ ?>