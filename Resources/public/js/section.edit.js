$(document).ready(function() {
    'use strict';
	//Activate delete section modal
    $('a.delete-section').each(function(){
        var deleteLink = $(this);
        deleteLink.attr("data-path", deleteLink.attr('href'));
        deleteLink.attr('href', '#deleteSectionModal-'+deleteLink.attr('data-section')).attr('data-toggle', 'modal');
        var modalDeleteForm = null;
        deleteLink.on('click', function (event){
            if(modalDeleteForm === null){
                event.preventDefault();
                $.get(deleteLink.attr("data-path"))
                    .always(function () {
                        if (modalDeleteForm !== null) {
                            modalDeleteForm.remove();
                        }
                    })
                    .done(function (data) {
                        $('body').append(data);
                        modalDeleteForm = $('#deleteSectionModal-'+deleteLink.attr('data-section'));
                        modalDeleteForm.modal('show');
                    })
                ;
            }
        });
    });
});