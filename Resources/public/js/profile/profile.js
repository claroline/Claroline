/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    $('body').on('click', '.submit-facet-edit-form', function(event) {
        event.preventDefault();
        var formId = $($(event.currentTarget)[0].parentElement.parentElement).attr('id');
        var formData = new FormData(document.getElementById(formId));
        var action = $($(event.currentTarget)[0].parentElement.parentElement).attr('action');

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data) {

                var flashbag =
                    '<div id="custom-flashbag-div" class="alert alert-success">' +
                    '<a id="flashbag-close-button" class="close" href="#" aria-hidden="true">&times;</a>' +
                    Translator.get('platform:edit_profile_success') +
                    '</div>';

                $('.panel-body').first().prepend(flashbag);

                for (var fieldId in data) {
                    var input = $('#field-' + fieldId);
                    input.attr('value', data[fieldId]);
                }


            },
            error: function(data) {
                alert('something went wrong');
            }
        });
    })
    .on('click', 'input.datepicker', function(event) {
        $(event.currentTarget).datepicker('show');
    });

    $('.datepicker').datepicker()
        .on('changeDate', function(event) {
            $(event.currentTarget).datepicker('hide');
        })
        .on('keydown', function(event) {
            event.preventDefault();
            this.$(event.currentTarget).datepicker('hide');
        });
})();