<form action="<?php echo $path('/database') ?>" method="post" class="form-horizontal" autocomplete="off">
    <div class="panel-body">
        <?php if ($var('global_error')): ?>
            <div class="alert alert-danger">
                <?php echo $trans($var('global_error')) ?>
                <?php if ($var('global_error') == 'not_empty_database'): ?>
                    <p><?php echo $trans('not_empty_database_submit') ?></p>
                    <br>
                    <button type="submit" name="force_install" class="btn btn-danger">
                        <?php echo $trans('proceed_anyway') ?>
                    </button>
                <?php endif ?>
            </div>
        <?php else: ?>
            <p class="info-txt">
                <?php echo $trans('database_msg') ?>
            </p>
        <?php endif ?>

        <?php $errors = $var('validation_errors') ?>

        <hr>
        <div class="form-group <?php if (isset($errors['driver'])) {
    echo 'has-error';
} ?>">
            <label class="control-label col-sm-2">
                <?php echo $trans('driver') ?>
            </label>
            <div class="col-sm-10">
                <select name="driver" class="form-control">
                    <option <?php if ($var('settings')->getDriver() === 'pdo_mysql') {
    echo 'selected';
} ?>>
                        MySQL
                    </option>
                </select>
            </div>
            <?php if (isset($errors['driver'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['driver']) ?>
                </span>
            <?php endif ?>
        </div>
        <div class="form-group <?php if (isset($errors['host'])) {
    echo 'has-error';
} ?>">
            <label class="control-label col-sm-2">
                <span class="required">*</span>
                <?php echo $trans('host') ?>
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="host"
                       class="form-control"
                       value="<?php echo $var('settings')->getHost() ?>"
                >
            </div>
            <?php if (isset($errors['host'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['host']) ?>
                </span>
            <?php endif ?>
        </div>
        <div class="form-group <?php if (isset($errors['name'])) {
    echo 'has-error';
} ?>">
            <label class="control-label col-sm-2">
                <span class="required">*</span>
                <?php echo $trans('database') ?>
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="name"
                       class="form-control"
                       value="<?php echo $var('settings')->getName() ?>"
                >
            </div>
            <?php if (isset($errors['name'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['name']) ?>
                </span>
            <?php endif ?>
        </div>
        <div class="form-group <?php if (isset($errors['user'])) {
    echo 'has-error';
} ?>">
            <label class="control-label col-sm-2">
                <span class="required">*</span>
                <?php echo $trans('user') ?>
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="user"
                       class="form-control"
                       value="<?php echo $var('settings')->getUser() ?>"
                >
            </div>
            <?php if (isset($errors['user'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['user']) ?>
                </span>
            <?php endif ?>
        </div>
        <div class="form-group <?php if (isset($errors['password'])) {
    echo 'has-error';
} ?>">
            <label class="control-label col-sm-2">
                <?php echo $trans('password') ?>
            </label>
            <div class="col-sm-10">
                <input type="password"
                       name="password"
                       class="form-control"
                       value="<?php echo $var('settings')->getPassword() ?>"
                >
            </div>
            <?php if (isset($errors['password'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['password']) ?>
                </span>
            <?php endif ?>
        </div>
        <div class="form-group <?php if (isset($errors['port'])) {
    echo 'has-error';
} ?>">
            <label class="control-label col-sm-2">
                <?php echo $trans('port') ?>
            </label>
            <div class="col-sm-10">
                <input type="text"
                       name="port"
                       class="form-control"
                       value="<?php echo $var('settings')->getPort() ?>"
                >
            </div>
            <?php if (isset($errors['port'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['port']) ?>
                </span>
            <?php endif ?>
        </div>
    </div>
    <div class="panel-footer">
        <div class="btn-group">
            <a href="<?php echo $path('/requirements') ?>" class="btn btn-default">
                <?php echo $trans('previous_step') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?php echo $trans('next_step') ?>
            </button>
        </div>
    </div>
</form>
