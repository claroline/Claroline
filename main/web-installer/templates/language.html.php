<div class="panel-body">
    <?php echo $trans('welcome_message'); ?>
    <hr>
    <form action="<?php echo $path('/'); ?>" method="post" class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-sm-2"><?php echo $trans('install_language'); ?></label>
            <div class="col-sm-10">
                <select name="install_language" class="form-control auto-submit">
                    <?php foreach ($getLangs() as $lang) {
    ?>
                    <option value="<?php echo $lang;
    ?>" <?php if ($var('install_language') === $lang) {
    echo 'selected';
}
    ?>>
                        <?php echo strtoupper($lang);
    ?>
                    </option>
                    <?php 
} ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2"><?php echo $trans('country'); ?></label>
            <div class="col-sm-10">
                <select name="country" class="form-control auto-submit">
                    <option></option>
                    <?php $current = $getCountry(); ?>
                    <?php foreach ($getCountries() as $country) {
    ?>
                    <option <?php if ($country === $current) {
    echo 'selected';
}
    ?>>
                        <?php echo $country;
    ?>
                    </option>
                    <?php 
} ?>
                </select>
            </div>
        </div>
        <input type="submit" class="hidden"/>
    </form>
</div>
<div class="panel-footer">
    <div class="step-controls">
        <a href="<?php echo $path('/requirements') ?>" class="btn btn-primary">
            <?php echo $trans('next_step') ?>
        </a>
    </div>
</div>
