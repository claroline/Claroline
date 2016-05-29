<form action="<?php echo $path('/mailing') ?>" method="post" class="form-horizontal" autocomplete="off">
    <div class="panel-body">
        <?php if ($var('global_error')): ?>
            <div class="alert alert-danger">
                <?php echo $trans($var('global_error')) ?>
            </div>
        <?php else: ?>
            <p class="info-txt">
                <?php echo $trans('mailing_msg') ?>
            </p>
        <?php endif ?>

        <?php $errors = $var('validation_errors'); ?>
        <?php $transport = $var('mailing_settings')->getTransport() ?>

        <hr>
        <div class="form-group">
            <label class="control-label col-sm-2">
                <?php echo $trans('transport') ?>
            </label>
            <div class="col-sm-10">
                <select name="transport" class="form-control auto-submit">
                    <option <?php if ($transport === 'smtp') {
    echo 'selected';
} ?>>
                        SMTP
                    </option>
                    <option <?php if ($transport === 'sendmail') {
    echo 'selected';
} ?>>
                        Sendmail / Postfix
                    </option>
                    <option <?php if ($transport === 'gmail') {
    echo 'selected';
} ?>>
                        Gmail
                    </option>
                </select>
            </div>
        </div>

        <?php if ($transport === 'smtp'): ?>
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
                           value="<?php echo $var('mailing_settings')->getTransportOption('host') ?>"
                        >
                </div>
                <?php if (isset($errors['host'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['host']) ?>
                </span>
                <?php endif ?>
            </div>
            <div class="form-group <?php if (isset($errors['username'])) {
    echo 'has-error';
} ?>">
                <label class="control-label col-sm-2">
                    <?php echo $trans('username') ?>
                </label>
                <div class="col-sm-10">
                    <input type="text"
                           name="username"
                           class="form-control"
                           value="<?php echo $var('mailing_settings')->getTransportOption('username') ?>"
                    >
                </div>
                <?php if (isset($errors['username'])): ?>
                <span class="help-block">
                    <?php echo $trans($errors['username']) ?>
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
                           value="<?php echo $var('mailing_settings')->getTransportOption('password') ?>"
                    >
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['password']) ?>
                </span>
                <?php endif ?>
            </div>
            <div class="form-group <?php if (isset($errors['encryption'])) {
    echo 'has-error';
} ?>">
                <label class="control-label col-sm-2">
                    <?php echo $trans('encryption') ?>
                </label>
                <div class="col-sm-10">
                    <select name="encryption" class="form-control">
                        <option <?php if ($var('mailing_settings')->getTransportOption('encryption') === '') {
    echo 'selected';
} ?>>
                        </option>
                        <option <?php if ($var('mailing_settings')->getTransportOption('encryption') === 'tls') {
    echo 'selected';
} ?>>
                            tls
                        </option>
                        <option <?php if ($var('mailing_settings')->getTransportOption('encryption') === 'ssl') {
    echo 'selected';
} ?>>
                            ssl
                        </option>
                    </select>
                </div>
                <?php if (isset($errors['encryption'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['encryption']) ?>
                </span>
                <?php endif ?>
            </div>
            <div class="form-group <?php if (isset($errors['auth_mode'])) {
    echo 'has-error';
} ?>">
                <label class="control-label col-sm-2">
                    <?php echo $trans('auth_mode') ?>
                </label>
                <div class="col-sm-10">
                    <select name="auth_mode" class="form-control">
                        <option <?php if ($var('mailing_settings')->getTransportOption('auth_mode') === '') {
    echo 'selected';
} ?>>
                        </option>
                        <option <?php if ($var('mailing_settings')->getTransportOption('auth_mode') === 'plain') {
    echo 'selected';
} ?>>
                            plain
                        </option>
                        <option <?php if ($var('mailing_settings')->getTransportOption('auth_mode') === 'login') {
    echo 'selected';
} ?>>
                            login
                        </option>
                        <option <?php if ($var('mailing_settings')->getTransportOption('auth_mode') === 'cram-md5') {
    echo 'selected';
} ?>>
                            cram-md5
                        </option>
                    </select>
                </div>
                <?php if (isset($errors['auth_mode'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['auth_mode']) ?>
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
                           value="<?php echo $var('mailing_settings')->getTransportOption('port') ?>"
                        >
                </div>
                <?php if (isset($errors['port'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['port']) ?>
                </span>
                <?php endif ?>
            </div>
        <?php elseif ($transport === 'gmail'): ?>
            <div class="form-group <?php if (isset($errors['username'])) {
    echo 'has-error';
} ?>">
                <label class="control-label col-sm-2">
                    <span class="required">*</span>
                    <?php echo $trans('username') ?>
                </label>
                <div class="col-sm-10">
                    <input type="text"
                           name="username"
                           class="form-control"
                           value="<?php echo $var('mailing_settings')->getTransportOption('username') ?>"
                        >
                </div>
                <?php if (isset($errors['username'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['username']) ?>
                </span>
                <?php endif ?>
            </div>
            <div class="form-group <?php if (isset($errors['password'])) {
    echo 'has-error';
} ?>">
                <label class="control-label col-sm-2">
                    <span class="required">*</span>
                    <?php echo $trans('password') ?>
                </label>
                <div class="col-sm-10">
                    <input type="password"
                           name="password"
                           class="form-control"
                           value="<?php echo $var('mailing_settings')->getTransportOption('password') ?>"
                        >
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="help-block">
                    <?php echo $trans($errors['password']) ?>
                </span>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
    <div class="panel-footer">
        <div class="btn-group">
            <a href="<?php echo $path('/admin') ?>" class="btn btn-default">
                <?php echo $trans('previous_step') ?>
            </a>
            <a href="<?php echo $path('/skip-mailing') ?>" class="btn btn-info">
                <?php echo $trans('skip_step') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?php echo $trans('next_step') ?>
            </button>
        </div>
    </div>
</form>
