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

    $('.role-delete-btn').on('click', function (event) {
        console.debug(event);
        event.preventDefault();

        if (!$(event.target).hasClass('disabled')) {
            //show alert modal
            var html = Twig.render(
                ModalWindow,
                {
                    'confirmFooter': true,
                    'modalId': 'confirm-modal',
                    'body': Translator.trans('remove_workspace_role_warning', {}, 'platform'),
                    'header': Translator.trans('remove_role', {}, 'platform')
                }
            );

            $('body').append(html);
            //display validation modal
            $('#confirm-modal').modal('show');
            //destroy the modal when hidden
            $('#confirm-modal').on('hidden.bs.modal', function () {
                $(this).remove();
            });

            var url = $(event.target).attr('href');
            var saveEvent = event;

            $('#confirm-ok').on('click', function(event) {
                $.ajax({
                    url: url,
                    success: function(data) {
                        $(saveEvent.target).parent().parent().remove();
                        $('#confirm-modal').modal('hide');
                    }
                });
            })
        }
    });
})();
