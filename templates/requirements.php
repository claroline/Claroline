<h2><?php echo $trans('requirements_check') ?></h2>

<form action="<?php echo $path('/') ?>" method="get">
    <input type="submit" value="<?php echo $trans('previous_step') ?>"/>
</form>

<form action="<?php echo $path('/database') ?>" method="get">
    <input type="submit"
           value="<?php echo $trans('next_step') ?>"
           <?php if ($var('no_next')) echo 'disabled' ?>
    />
</form>