

<?php
$classes = Flux::classes()
    ->add('[:where(&)]:min-w-48 [:where(&)]:p-4')
    ->add('[:where(&)]:min-w-48 [:where(&)]:p-4')
    ->add('rounded-lg [:where(&)]:shadow-xs')
    ->add('border border-zinc-200 dark:border-zinc-600')
    ->add('bg-white dark:bg-zinc-700')
    // This is a fix for elements with the popover attribute not being friendly with display
    // CSS properties like: "flex" because popovers need display: none; when closed...
    ->add('not-data-open:hidden')
    ;
?>

<div
    <?php echo e($attributes->class($classes)); ?>

    popover="manual"
    data-flux-popover
    tabindex="-1"
>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/local_packages/flux-pro-main/stubs/resources/views/flux/popover/index.blade.php ENDPATH**/ ?>