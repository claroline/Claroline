$(document).ready(function() {
    initGradingScalesForm();
    initCriterionForm();
});





function initGradingScalesForm() {
    // ADD GRADING SCALE
    var $collectionHolder = $('div.grading-scales');
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    var nbInputs = $collectionHolder.find(':input').length;
    $collectionHolder.attr('data-index', nbInputs);

    // we must have at least 3 grading scales
    // @TODO check how we should build those minimal rows (management rules?)
    if (nbInputs === 0) {
        var values = [
            Translator.trans('default_scale_very_good', {}, 'innova_collecticiel'),
            Translator.trans('default_scale_satisfying', {}, 'innova_collecticiel'),
            Translator.trans('default_scale_insufficient', {}, 'innova_collecticiel')
        ];
        for (var i = 0; i < 3; i++) {
            addScaleFormRow($collectionHolder, values[i]);
        }
    }

    $('.add-grading-scale').on('click', function(e) {
        e.preventDefault();
        addScaleFormRow($collectionHolder);
    });
}

function initCriterionForm() {
    var $criterionListContainer = $('div.criterion');
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    var nbInputs = $criterionListContainer.find(':input').length;
    $criterionListContainer.attr('data-index', nbInputs);

    if (nbInputs === 0) {
        addCriteriaFormRow($criterionListContainer, Translator.trans('default_criteria_comment', {}, 'innova_collecticiel'));
    }

    $('.add-criteria').on('click', function(e) {
        e.preventDefault();
        addCriteriaFormRow($criterionListContainer);
    });
}

function addScaleFormRow($collectionHolder, value) {
    var prototype = $collectionHolder.data('prototype');
    var index = parseInt($collectionHolder.attr('data-index'));
    var newFormRow = prototype.replace(/__name__/g, index);
    $collectionHolder.append(newFormRow);
    $collectionHolder.attr('data-index', index + 1);
    if (value && value !== '') {
        $('#innova_collecticiel_appreciation_form_gradingScales_'+index+'_scaleName').attr('value', value);
    }
}

function addCriteriaFormRow($collectionHolder, value) {
    var prototype = $collectionHolder.data('prototype');
    var index = parseInt($collectionHolder.attr('data-index'));
    var newFormRow = prototype.replace(/__name__/g, index);
    $collectionHolder.append(newFormRow);
    $collectionHolder.attr('data-index', index + 1);
    if (value && value !== '') {
        $('#innova_collecticiel_appreciation_form_gradingCriterias_'+index+'_criteriaName').attr('value', value);
    }
}

// ATTACH EVENTS

$('body').on('click', 'button.delete-grading-scale', function(e) {
    e.preventDefault();
    $(this).closest('div.row').remove();
  
    return false;
});


$('body').on('click', 'button.delete-criteria', function(e) {
    e.preventDefault();
    $(this).closest('div.row').remove();
    return false;
});
