$(function(){
    var locationhash = window.location.hash;
    if (locationhash.substr(0,2) == "#!") {
        $("a[href='#" + locationhash.substr(2) + "']").tab("show");
    }

    $(".nav-tabs a.has-error:first").tab("show");

    function submitForm() {
        $("#editBadgeForm").submit();
    }

    $('.confirm').confirmModal();
    $('.edit-confirm').confirmModal({'confirmCallback': submitForm});

    $("[data-toggle=popover]").popover();

    ZenstruckFormHelper.initSelect2Helper();

    var expiringPeriodDurationBlock = $("#expiring_period_duration");

    $('#badge_form_is_expiring').click(function(){
        if (this.checked) {
            expiringPeriodDurationBlock.show();
        }
        else {
            expiringPeriodDurationBlock.hide();
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

    var badgeFormFile = $("#badge_form_file");
    badgeFormFile.hide();

    var changeBadgeImageButton = $("#change_image");
    changeBadgeImageButton.hide();
    changeBadgeImageButton
        .click(function(event) {
            badgeFormFile.click();
        })
        .hover(function(){
                changeBadgeImageButton.show();
            }, function() {
                changeBadgeImageButton.hide();
            }
        );

    var uploadImagePlaceholder = $(".upload_image_placeholder");
    uploadImagePlaceholder
        .click(function(event) {
            event.preventDefault();
        })
        .hover(function(){
                changeBadgeImageButton.show();
            }, function() {
                changeBadgeImageButton.hide();
            }
        );

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
});
