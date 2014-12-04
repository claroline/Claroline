/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

'use_strict';

$('.create-modal-form').on('click', function (event) {
    event.preventDefault();
    window.Claroline.Modal.displayForm(
        $(event.target).attr('href'),
        addModel,
        function () {},
        'model-form'
    );
});

$('body').on('click', '.delete-model-link', function (event) {
    event.preventDefault();
    window.Claroline.Modal.confirmRequest(
        $(event.currentTarget).attr('href'),
        removeModel,
        undefined,
        Translator.trans('remove_model_comfirm', {}, 'platform'),
        Translator.trans('remove_model', {}, 'platform')
    );
});

$('body').on('click', '.rename-model-link', function (event) {
    event.preventDefault();
    window.Claroline.Modal.displayForm(
        $(event.target).attr('href'),
        editModel,
        function () {},
        'model-form'
    );
});

var addModel = function (model) {
    var html = Twig.render(ModelRow, {'model': model});
    $('#table-model-body').append(html);
    $('#no-model-div').hide();
    $('.model-list').show();
}

var removeModel = function (event, args, model) {
    console.debug(model.id);
    $('#model-' + model.id).remove();
}

var editModel = function (model) {
    $('#model-' + model.id +  ' td:first-child').html(model.name);
}
