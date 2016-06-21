<div class="panel-body">
    <p class="info-txt">
        <?php if ($var('has_failed_requirement')): ?>
            <?php echo $trans('failed_requirement_msg') ?>
            <?php if ($var('has_failed_recommendation')): ?>
                <?php echo $trans('additional_failed_recommendation_msg') ?>
            <?php endif ?>
        <?php elseif ($var('has_failed_recommendation')): ?>
            <?php echo $trans('failed_recommendation_msg') ?>
        <?php else: ?>
            <?php echo $trans('correct_configuration_msg') ?>
        <?php endif ?>
    </p>

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
</div>
<div class="panel-footer">
    <div class="btn-group step-controls">
        <a href="<?php echo $path('/') ?>" class="btn btn-default">
            <?php echo $trans('previous_step') ?>
        </a>
        <?php if ($var('has_failed_requirement') || $var('has_failed_recommendation')): ?>
            <a href="" class="btn btn-warning">
                <?php echo $trans('test_again') ?>
            </a>
        <?php endif ?>
        <a href="<?php echo $path('/database') ?>"
           class="btn btn-primary <?php if ($var('has_failed_requirement')) {
    echo 'disabled';
} ?>">
            <?php echo $trans('next_step') ?>
        </a>
    </div>
</div>
