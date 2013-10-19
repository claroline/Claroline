<p class="info-txt"><?php echo $trans('pre_install_msg') ?></p>

<form action="<?php echo $path('/install') ?>" method="post" class="form-horizontal">
    <div class="btn-group step-controls">
        <a id="pre-install" href="<?php echo $path('/mailing') ?>" class="btn btn-default">
            <?php echo $trans('previous_step') ?>
        </a>
        <button id="do-install" type="submit" class="btn btn-primary">
            <?php echo $trans('do_install') ?>
        </button>
    </div>
</form>
