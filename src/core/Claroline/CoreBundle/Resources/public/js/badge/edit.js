$(function(){
    $('[data-toggle=tooltip]').tooltip();

    $('.delete').confirmModal();

    $("[name='badge_form[expired_at]']").off('focus');

    var currentImage  = $('#current_image');
    var badgeFormFile = $('#badge_form_file');
    $('#delete_image').click(function() {
        if($(this)[0].checked)
        {
            currentImage.addClass('hidden');
            badgeFormFile.removeClass('hidden');
        }
        else
        {
            currentImage.removeClass('hidden');
            badgeFormFile.addClass('hidden');
        }
    });

    var attributeModal = $('#attribute_user_modal');

    $('#attributeUser').click(function(event) {
        event.preventDefault();
        $.ajax({
            url: event.currentTarget.getAttribute('href'),
            type: 'POST',
            success: function (data) {
                $('.modal-body', attributeModal).html(data);
                attributeModal.modal();
            }
        });

    });
});