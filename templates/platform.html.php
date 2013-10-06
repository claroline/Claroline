<h4><?php echo $trans('platform_parameters') ?></h4>

<form action="<?php echo $path('/platform') ?>" method="post" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2"><?php echo $trans('platform_language') ?></label>
        <div class="col-lg-2">
            <select name="platform_language" class="form-control">
                <option <?php if ($var('platform_language') === 'en') echo 'selected' ?>>
                    English
                </option>
                <option <?php if ($var('platform_language') === 'fr') echo 'selected' ?>>
                    Français
                </option>
            </select>
        </div>
    </div>

    <a href="<?php echo $path('/database') ?>" class="btn btn-default">
        <?php echo $trans('previous_step') ?>
    </a>
    <button type="submit" class="btn btn-default">
        <?php echo $trans('next_step') ?>
    </button>
</form>
