<h4><?php echo $trans('parameters') ?></h4>

<?php if ($var('global_error')): ?>
    <div class="alert alert-danger">
        <?php echo $trans($var('global_error')) ?>
    </div>
<?php endif ?>

<?php $errors = $var('form_errors') ?>

<form action="<?php echo $path('/database') ?>" method="post" class="form-horizontal" autocomplete="off">
    <div class="form-group <?php if (isset($errors['driver'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('driver') ?>
        </label>
        <div class="col-lg-3">
            <select name="driver" class="form-control">
                <option <?php if ($var('driver', '') === 'pdo_mysql') echo 'selected' ?>>
                    MySQL
                </option>
                <option <?php if ($var('driver', '') === 'pdo_pgsql') echo 'selected' ?>>
                    PostgreSQL
                </option>
            </select>
        </div>
        <?php if (isset($errors['driver'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['driver']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['host'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('host') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="host"
                   class="form-control"
                   value="<?php echo $var('host', 'localhost') ?>"
            >
        </div>
        <?php if (isset($errors['host'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['host']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['dbname'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('database') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="dbname"
                   class="form-control"
                   value="<?php echo $var('dbname', 'claroline') ?>"
            >
        </div>
        <?php if (isset($errors['dbname'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['dbname']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['user'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('user') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="user"
                   class="form-control"
                   value="<?php echo $var('user', 'root') ?>"
            >
        </div>
        <?php if (isset($errors['user'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['user']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['password'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('password') ?>
        </label>
        <div class="col-lg-3">
            <input type="password"
                   name="password"
                   class="form-control"
                   value="<?php echo $var('password', '') ?>"
            >
        </div>
        <?php if (isset($errors['password'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['password']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['port'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('port') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="port"
                   class="form-control"
                   value="<?php echo $var('port', '') ?>"
            >
        </div>
        <?php if (isset($errors['port'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['port']) ?>
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
