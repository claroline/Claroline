<p class="info-txt">
    <?php echo $trans('platform_msg') ?>
</p>

<?php $errors = $var('errors'); ?>

<form action="<?php echo $path('/platform') ?>" method="post" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2">
            <?php echo $trans('language') ?>
        </label>
        <div class="col-lg-3">
            <select name="language" class="form-control">
                <option <?php if ($var('platform_settings')->getLanguage() === 'en') echo 'selected' ?>>
                    English
                </option>
                <option <?php if ($var('platform_settings')->getLanguage() === 'fr') echo 'selected' ?>>
                    Français
                </option>
            </select>
        </div>
    </div>
    <div class="form-group <?php if (isset($errors['name'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('name') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="name"
                   class="form-control"
                   value="<?php echo $var('platform_settings')->getName() ?>"
                >
        </div>
        <?php if (isset($errors['name'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['name']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['supportEmail'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <span class="required">*</span>
            <?php echo $trans('support_email') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="supportEmail"
                   class="form-control"
                   value="<?php echo $var('platform_settings')->getSupportEmail() ?>"
                >
        </div>
        <?php if (isset($errors['supportEmail'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['supportEmail']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['organization'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('organization') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="organization"
                   class="form-control"
                   value="<?php echo $var('platform_settings')->getOrganization() ?>"
                >
        </div>
        <?php if (isset($errors['organization'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['organization']) ?>
            </span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['organizationUrl'])) echo 'has-error' ?>">
        <label class="control-label col-lg-2">
            <?php echo $trans('organization_url') ?>
        </label>
        <div class="col-lg-3">
            <input type="text"
                   name="organizationUrl"
                   class="form-control"
                   value="<?php echo $var('platform_settings')->getOrganizationUrl() ?>"
                >
        </div>
        <?php if (isset($errors['organizationUrl'])): ?>
            <span class="help-block">
                <?php echo $trans($errors['organizationUrl']) ?>
            </span>
        <?php endif ?>
    </div>

    <div class="btn-group step-controls">
        <a href="<?php echo $path('/database') ?>" class="btn btn-default">
            <?php echo $trans('previous_step') ?>
        </a>
        <button type="submit" class="btn btn-primary">
            <?php echo $trans('next_step') ?>
        </button>
    </div>
</form>
