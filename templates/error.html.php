<div class="panel-body">
    <p class="alert alert-danger">
        <?php echo $trans('failure_message') ?>
    </p>
    <h4>
        <?php echo $trans('install_log') ?>
    </h4>
    <?php if ($var('log')): ?>
        <pre><?php echo $var('log') ?></pre>
    <?php else: ?>
        <em>
            <?php echo $trans('no_log_available', array('log_file' => $var('log_filename'))) ?>
        </em>
    <?php endif ?>
</div>
