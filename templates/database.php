<h4><?php echo $trans('database_parameters') ?></h4>

<form action="<?php echo $path('/database') ?>" method="post">



    <a href="<?php echo $path('/requirements') ?>" class="btn btn-default">
        <?php echo $trans('previous_step') ?>
    </a>
    <button type="submit" class="btn btn-default">
        <?php echo $trans('next_step') ?>
    </button>
</form>
