<h4><?php echo $trans('requirements_check') ?></h4>

<table class="table table-bordered">

<?php foreach ($var('setting_categories') as $category): ?>
    <?php if (!$category->hasIncorrectSetting()): ?>
        <tr>
            <td>
                <strong>
                    <?php echo $trans($category->getName()) ?>
                </strong>
            </td>
            <td class="success">
                <?php echo $trans('correct_config') ?>
            </td>
        </tr>
    <?php else: ?>
        <?php for ($i = 0, $settings = $category->getIncorrectSettings(); $i < count($settings); ++$i): ?>
            <tr>
                <?php if ($i === 0): ?>
                    <td rowspan="<?php echo count($settings) ?>">
                        <strong>
                            <?php echo $trans($category->getName()) ?>
                        </strong>
                    </td>
                <?php endif ?>
                <?php if (!$settings[$i]->isCorrect()): ?>
                    <td class="<?php echo $settings[$i]->isRequired() ? 'danger' : 'warning' ?>">
                        <?php echo $trans($settings[$i]->getDescription(), $settings[$i]->getDescriptionParameters()) ?>
                    </td>
                <?php endif ?>
            </tr>
        <?php endfor ?>
    <?php endif ?>
<?php endforeach ?>

</table>

<a href="<?php echo $path('/') ?>" class="btn btn-default">
    <?php echo $trans('previous_step') ?>
</a>

<a href="<?php echo $path('/database') ?>"
   class="btn btn-default <?php if ($var('has_failed_requirement')) echo 'disabled' ?>">
    <?php echo $trans('next_step') ?>
</a>
