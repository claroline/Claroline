$(document).ready(function () {
    'use strict';

    var modalNewForm = null;
    var previousPath = null;

    $('a.launch-modal').on('click', function (event) {
        event.preventDefault();
        var currentPath = $(this).attr('href');
        if (previousPath != null && previousPath == currentPath) {
            modalNewForm.modal('show');
        } else {
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

                    previousPath = currentPath;
                })
            ;
        }
    });
});