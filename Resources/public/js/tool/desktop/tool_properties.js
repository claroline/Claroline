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

    $('.fa-arrow-circle-up').on('click', function (e) {
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        moveRowUp(rowIndex);
    });

    $('.fa-arrow-circle-down').on('click', function (e) {
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        moveRowDown(rowIndex);
    });

    $('#edit-tools-btn').on('click', function (e) {
        e.preventDefault();
        var formData = new FormData(document.getElementById('desktop-tool-form'));
        var url = $('#desktop-tool-form').attr('action');

        $('#tool-table tr').each(function (index) {
            if ($(this).attr('data-tool-id')) {
                formData.append('tool-' + $(this).attr('data-tool-id'), index);
            }
        });

        var redirect = $(this).attr('href');

        $.ajax({
            url: url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                window.location = redirect;
            }
        });
    });

    function moveRowUp(index) {
        var rows = $('#tool-table tr');
        var size = rows.length;

        if (index !== 1) {
            rows.eq(index).insertAfter(rows.eq(index - 2));
            setOrderingIconsState();
        }
    }

    function moveRowDown(index) {
        var rows = $('#tool-table tr');
        var size = rows.length;

        if (index !== size) {
            rows.eq(index).insertAfter(rows.eq(index + 1));
            setOrderingIconsState();
        }
    }

    function setOrderingIconsState() {
        var upIcons = $('#tool-table span.ordering-icon.up');
        var downIcons = $('#tool-table span.ordering-icon.down');
        var downLength = downIcons.length;

        upIcons.each(function (index, icon) {
            $(icon)[(index === 0 ? 'addClass' : 'removeClass')]('disabled');
        });
        downIcons.each(function (index, icon) {
            $(icon)[index === downLength - 1 ? 'addClass' : 'removeClass']('disabled');
        });
    }
})();

