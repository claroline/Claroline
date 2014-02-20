/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){
    var locationhash = window.location.hash;
    if (locationhash.substr(0,2) == "#!") {
        $("a[href='#" + locationhash.substr(2) + "']").tab("show");
    }

    $(".nav-tabs a.has-error:first").tab("show");

    $('.delete').confirmModal();

    $("[data-toggle=popover]").popover();

    ZenstruckFormHelper.initSelect2Helper();

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
