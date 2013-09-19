$(function(){
    var locationhash = window.location.hash;
    if (locationhash.substr(0,2) == "#!") {
        $("a[href='#" + locationhash.substr(2) + "']").tab("show");
    }

    $('[data-toggle=tooltip]').tooltip();

    $('.delete').confirmModal();

    var deleteImageCheckbox = $("#badge_form_change_image");

    var badgeFormFile = $("#badge_form_file");
    badgeFormFile.hide();

    var uploadImagePlaceholder = $(".upload_image_placeholder");
    uploadImagePlaceholder.click(function(event) {
        if (deleteImageCheckbox[0].checked) {
            badgeFormFile.click();
        }
        event.preventDefault();
    });

    var uploadImagePlaceholderCaption = $(".caption", uploadImagePlaceholder);
    uploadImagePlaceholderCaption.hide();

    badgeFormFile.change(function(){
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (event) {
                $("img", uploadImagePlaceholder).attr('src', event.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    });

    var currentImage  = $('#current_image');
    var badgeFormFile = $('#badge_form_file');
    deleteImageCheckbox.click(function() {
        var fileImage = $("img", uploadImagePlaceholder);
        if($(this)[0].checked)
        {
            fileImage.attr('src', fileImage.attr('data-src-placeholder'));
            uploadImagePlaceholderCaption.show();
        }
        else
        {
            fileImage.attr('src', fileImage.attr('data-src-current'));
            uploadImagePlaceholderCaption.hide();
        }
    });

    var awardFormContainer  = $("#award_form_container");
    var awardUsersContainer = $("#award_users_container");

    $('#awardUser').click(function(event) {
        event.preventDefault();
        awardUsersContainer.hide('fast');
        awardFormContainer.show('fast');
    });
    $('#viewAwardedUser').click(function(event) {
        event.preventDefault();
        awardFormContainer.hide('fast');
        awardUsersContainer.show('fast');
    });
});