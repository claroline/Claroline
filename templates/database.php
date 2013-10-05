<h4><?php echo $trans('database_parameters') ?></h4>

<?php $errors = $var('errors') ?>

<form action="<?php echo $path('/database') ?>" method="post" class="form-horizontal" autocomplete="off">
    <div class="form-group <?php if (isset($errors['database_driver'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('driver') ?>
        </label>
        <div class="col-lg-3">
            <select name="database_driver" class="form-control">
                <option <?php if ($var('database_driver', '') === 'pdo_mysql') echo 'selected' ?>>
                    MySQL
                </option>
                <option <?php if ($var('database_driver', '') === 'pdo_pgsql') echo 'selected' ?>>
                    PostgreSQL
                </option>
            </select>
        </div>
        <?php if (isset($errors['database_driver'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['database_driver']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['database_host'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('host') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="database_host"
                   class="form-control"
                   value="<?php echo $var('database_host', 'localhost') ?>"
            >
        </div>
        <?php if (isset($errors['database_host'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['database_host']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['database_name'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('database') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="database_name"
                   class="form-control"
                   value="<?php echo $var('database_name', 'claroline') ?>"
            >
        </div>
        <?php if (isset($errors['database_name'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['database_name']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['database_user'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('user') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="database_user"
                   class="form-control"
                   value="<?php echo $var('database_user', 'root') ?>"
            >
        </div>
        <?php if (isset($errors['database_user'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['database_user']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['database_password'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('password') ?>
        </label>
        <div class="col-lg-3">
            <input type="password"
                   name="database_password"
                   class="form-control"
                   value="<?php echo $var('database_password', '') ?>"
            >
        </div>
        <?php if (isset($errors['database_password'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['database_password']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['database_port'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('port') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="database_port"
                   class="form-control"
                   value="<?php echo $var('database_port', '') ?>"
            >
        </div>
        <?php if (isset($errors['database_port'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['database_port']) ?>
            </span>
        <?php endif ?>
    </div>

    <a href="<?php echo $path('/requirements') ?>" class="btn btn-default">
        <?php echo $trans('previous_step') ?>
    </a>
    <button type="submit" class="btn btn-default">
        <?php echo $trans('next_step') ?>
    </button>
</form>
