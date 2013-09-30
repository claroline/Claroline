<h2><?php echo $trans('requirements_check') ?></h2>

<a href="<?php echo $path('/') ?>" class="button">
    <?php echo $trans('previous_step') ?>
</a>

<a href="<?php echo $path('/database') ?>"
   class="button <?php if ($var('no_next')) echo 'disabled' ?>">
    <?php echo $trans('next_step') ?>
</a>
