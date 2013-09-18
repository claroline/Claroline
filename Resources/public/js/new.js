$(document).ready(function() {
    'use strict';

    var modalNewForm = null;

    var newLink = $('a.new-section');
    var newPath = newLink.attr('href');
    newLink.attr('href', '#newSectionModal').attr('data-toggle', 'modal');
    newLink.on('click', function (event){

        if(modalNewForm === null){
            event.preventDefault();
            $.get(newPath)
                .always(function () {
                    if (modalNewForm !== null) {
                        modalNewForm.remove();
                    }
                })
                .done(function (data) {
                    $('body').append(data);
                    modalNewForm = $('#newSectionModal');
                    console.log(modalNewForm.html());
                    modalNewForm.modal('show');
                })
            ;
        }
    });
});