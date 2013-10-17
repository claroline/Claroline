<p><?php echo $trans('admin_msg') ?></p>

<?php $errors = $var('errors'); ?>

<form action="<?php echo $path('/admin') ?>" method="post" class="form-horizontal" autocomplete="off">
    <div class="form-group <?php if (isset($errors['firstName'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('first_name') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="firstName"
                   class="form-control"
                   value="<?php echo $var('first_admin_settings')->getFirstName() ?>"
                >
        </div>
        <?php if (isset($errors['firstName'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['firstName']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['lastName'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('last_name') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="lastName"
                   class="form-control"
                   value="<?php echo $var('first_admin_settings')->getLastName() ?>"
                >
        </div>
        <?php if (isset($errors['lastName'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['lastName']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['username'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('username') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="username"
                   class="form-control"
                   value="<?php echo $var('first_admin_settings')->getUsername() ?>"
                >
        </div>
        <?php if (isset($errors['username'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['username']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['password'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('password') ?>
        </label>
        <div class="col-lg-3">
            <input type="password"
                   name="password"
                   class="form-control"
                   value="<?php echo $var('first_admin_settings')->getPassword() ?>"
                >
        </div>
        <?php if (isset($errors['password'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['password']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['email'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('email') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="email"
                   class="form-control"
                   value="<?php echo $var('first_admin_settings')->getEmail() ?>"
                >
        </div>
        <?php if (isset($errors['email'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['email']) ?>
            </span>
        <?php endif ?>
    </div>

    <div class="btn-group step-controls">
        <a href="<?php echo $path('/platform') ?>" class="btn btn-default">
            <?php echo $trans('previous_step') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?php echo $trans('next_step') ?>
        </button>
    </div>
</form>
