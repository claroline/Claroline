<h4><?php echo $trans('welcome') ?></h4>
<p><?php echo $trans('welcome_message') ?></p>

<form action="<?php echo $path('/') ?>" method="post" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2"><?php echo $trans('choose_language') ?></label>
        <div class="col-lg-2">
            <select name="language" class="form-control auto-submit">
                <option <?php if ($var('language') === 'en') echo 'selected' ?>>
                    English
                </option>
                <option <?php if ($var('language') === 'fr') echo 'selected' ?>>
                    Français
                </option>
            </select>
        </div>
    </div>
    <input type="submit" class="hidden"/>
</form>

<a href="<?php echo $path('/requirements') ?>" class="btn btn-default">
    <?php echo $trans('next_step') ?>
</a>