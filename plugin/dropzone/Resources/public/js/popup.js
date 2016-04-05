$(document).ready(function () {
    'use strict';

    var modalNewForm = null;

    $('a.launch-modal').on('click', function (event) {
        event.preventDefault();
        var currentPath = $(this).attr('href');
        $.get(currentPath)
            .always(function () {
                if (modalNewForm !== null) {
                    modalNewForm.remove();
                }
            })
            .done(function (data) {
                $('body').append(data);
                modalNewForm = $('#modal-content');
                modalNewForm.modal('show');

                modalNewForm.on('hidden.bs.modal', function () {
                    modalNewForm.remove();
                });
            })
        ;
    });
});
