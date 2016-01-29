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

    var home = window.Claroline.Home;
    var modal = window.Claroline.Modal;

    function save(id)
    {
        var ready = $.Deferred();
        var name = $('#theme-name').val();
        var themeLess = $('#theme-less textarea').val();
        var variables = '';

        $('.theme-value input').each(function () {
            variables +=  $('code', this.parentNode.parentNode).html() + ': ' + this.value + ';\n';
        });

        $.post(home.path + 'admin/theme/build', {
            'theme-id': id,
            'theme-less': themeLess,
            'name': name,
            'variables': variables
        })
        .done(function (data) {
            if (!isNaN(data) && data !== '') {

                if (name === '' || name === undefined) {
                    $('#theme-name').val('Theme' + data);
                }

                ready.resolve(data);

            } else {
                modal.fromRoute('claro_theme_error');
            }
        })
        .error(function () {
            modal.fromRoute('claro_theme_error');
        });

        return ready;
    }

    function deleteTheme(id)
    {
        var url = Routing.generate('claro_admin_theme_delete', { id: id });

        $.ajax(url, { type: 'DELETE' })
            .done(function () {
                window.location = Routing.generate('claro_admin_theme_list');
            });
    }

    $('body').on('click', '.theme-list .btn.dele', function () {
        var id = $(this).data('id');
        modal.fromRoute('claro_theme_confirm', {}, function (element) {
            element.on('click', '.btn.delete', function () {
                deleteTheme(id);
            });
        });
    })
    .on('click', '.theme-list .alert .close', function () {
        var id = $(this).data('id');
        modal.fromRoute('claro_theme_confirm', {}, function (element) {
            element.on('click', '.btn.delete', function () {
                deleteTheme(id);
            });
        });
    })
    .on('click', '.theme-value .btn', function () {
        var color = $('input', this.parentNode.parentNode).val();

        if (/(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(color)) {
            $(this).colorpicker('setValue', color);
        }
    });

    $('.theme-value .btn').each(function () {
        $(this).colorpicker().on('changeColor', function (event) {
            $('input', this.parentNode.parentNode).val(event.color.toHex());
        });
    });
}());
