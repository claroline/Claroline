$(document).ready(function() {
    initNotationForm();
});

function initNotationForm() {
    var $notationListContainer = $('div.notation');
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    var nbInputs = $notationListContainer.find(':input').length;
    $notationListContainer.attr('data-index', nbInputs);

    if (nbInputs === 0) {
        addNotationFormRow($notationListContainer, Translator.trans('default_notation_comment', {}, 'innova_collecticiel'));
    }

    $('.add-notation').on('click', function(e) {
        e.preventDefault();
        addNotationFormRow($notationListContainer);
    });
}

function addNotationFormRow($collectionHolder, value) {
    var prototype = $collectionHolder.data('prototype');
    var index = parseInt($collectionHolder.attr('data-index'));
    var newFormRow = prototype.replace(/__name__/g, index);
    $collectionHolder.append(newFormRow);
    $collectionHolder.attr('data-index', index + 1);
    if (value && value !== '') {
        $('#innova_collecticiel_notation_form_gradingNotations_'+index+'_notationName').attr('value', value);
    }
}

// ATTACH EVENTS
$('body').on('click', 'button.delete-notation', function(e) {
    e.preventDefault();
    $(this).closest('div.row').remove();
  
    return false;
});
