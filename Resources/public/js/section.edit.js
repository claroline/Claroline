$(document).ready(function() {
    'use strict';
	//Activate delete section modal
    $('a.delete-section').each(function(){
        var newLink = $(this);
        newLink.attr("data-path", newLink.attr('href'));
        newLink.attr('href', '#deleteSectionModal-'+newLink.attr('data-section')).attr('data-toggle', 'modal');
        var modalDeleteForm = null;
        newLink.on('click', function (event){
            if(modalDeleteForm === null){
                event.preventDefault();
                $.get(newLink.attr("data-path"))
                    .always(function () {
                        if (modalDeleteForm !== null) {
                            modalDeleteForm.remove();
                        }
                    })
                    .done(function (data) {
                        $('body').append(data);
                        modalDeleteForm = $('#deleteSectionModal-'+newLink.attr('data-section'));
                        modalDeleteForm.modal('show');
                    })
                ;
            }
        });
    });
});