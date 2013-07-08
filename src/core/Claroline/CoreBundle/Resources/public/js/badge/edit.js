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

    var awardFormContainer  = $("#award_form_container");
    var awardUsersContainer = $("#award_users_container");

    $('#awardUser').click(function(event) {
        event.preventDefault();
        awardUsersContainer.toggleClass('hidden');
        awardFormContainer.toggleClass('hidden');
    });
    $('#viewAwardedUser').click(function(event) {
        event.preventDefault();
        awardFormContainer.toggleClass('hidden');
        awardUsersContainer.toggleClass('hidden');
    });
});