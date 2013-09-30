<h2><?php echo $trans('welcome_message') ?></h2>

<form action="<?php echo $path('/') ?>" method="post">
    <label><?php echo $trans('choose_language') ?> :</label>
    <select name="language">
        <option <?php if ($var('language') === 'en') echo 'selected' ?>>en</option>
        <option <?php if ($var('language') === 'fr') echo 'selected' ?>>fr</option>
    </select>
    <input type="submit" value="<?php echo $trans('next_step') ?>"/>
</form>
