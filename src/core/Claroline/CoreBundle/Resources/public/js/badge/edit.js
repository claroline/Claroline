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

    var awardModal = $('#award_user_modal');

    $('#awardUser').click(function(event) {
        event.preventDefault();
        $.ajax({
            url: event.currentTarget.getAttribute('href'),
            type: 'GET',
            success: function (data) {
                $('.modal-body', awardModal).html(data);
                awardModal.modal();
            }
        });
    });

    $('#validAwarding').click(function(event) {
        event.preventDefault();
        var awardForm = $('.modal-body form', awardModal);
        $.ajax({
            url: awardForm.attr('action'),
            type: 'POST',
            data: awardForm.serialize(),
            success: function (data) {
                if(data.error) {
                    $('.modal-body', awardModal).html(data);
                }
                else {
                    awardModal.modal('hide');
                    window.location.reload();
                }
            }
        });
    });
});