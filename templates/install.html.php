<p class="info-txt"><?php echo $trans('pre_install_msg') ?></p>

<form action="<?php echo $path('/install') ?>" method="post" class="form-horizontal">
    <a href="<?php echo $path('/mailing') ?>" class="btn btn-default">
        <?php echo $trans('previous_step') ?>
    </a>
    <button type="submit" class="btn btn-primary">
        <?php echo $trans('do_install') ?>
    </button>
</form>


