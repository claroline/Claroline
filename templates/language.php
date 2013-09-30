<h2><?php echo $trans('welcome_message') ?></h2>

<form action="<?php echo $path('/') ?>" method="post">
    <label><?php echo $trans('choose_language') ?> :</label>
    <select name="language" onchange="this.form.submit()">
        <option <?php if ($var('language') === 'en') echo 'selected' ?>>en</option>
        <option <?php if ($var('language') === 'fr') echo 'selected' ?>>fr</option>
    </select>
    <input type="submit" style="visibility: hidden" value=""/>
</form>

<a href="<?php echo $path('/requirements') ?>" class="button">
    <?php echo $trans('next_step') ?>
</a>