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

    $('body').on('click', '.delete-role-btn', function(event) {
        var roleId = $(event.currentTarget).attr('data-role-id');
        var url = Routing.generate('platform_roles_remove', {'role': roleId})
        var roleName = $(event.currentTarget).attr('data-role-name');

        var html = Twig.render(
            ModalWindow,
            {
                'confirmFooter': true,
                'modalId': 'confirm-modal',
                'body': Translator.get('platform:remove_role_confirm', {'name': roleName}),
                'header': Translator.get('platform:remove_role', {})
            }
        );

        $('body').append(html);
        //display validation modal
        $('#confirm-modal').modal('show');
        //destroy the modal when hidden
        $('#confirm-modal').on('hidden.bs.modal', function () {
            $(this).remove();
        });

        $('#confirm-ok').on('click', function(event) {
            $.ajax({
                url: url,
                success: function(data) {
                    $('#tr-role-' + roleId).remove();
                    $('#confirm-modal').modal('hide');
                }
            });
        })
    });
})();