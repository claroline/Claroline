<p class="info-txt"><?php echo $trans('welcome_message') ?></p>

<form action="<?php echo $path('/') ?>" method="post" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-3"><?php echo $trans('install_language') ?></label>
        <div class="col-lg-2">
            <select name="install_language" class="form-control auto-submit">
                <option <?php if ($var('install_language') === 'en') echo 'selected' ?>>
                    English
                </option>
                <option <?php if ($var('install_language') === 'fr') echo 'selected' ?>>
                    Français
                </option>
            </select>
        </div>
    </div>
    <input type="submit" class="hidden"/>
</form>

<div class="step-controls">
    <a href="<?php echo $path('/requirements') ?>" class="btn btn-primary">
        <?php echo $trans('next_step') ?>
    </a>
</div>
