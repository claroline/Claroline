/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){
    var badgeFormFile = $("#badge_form_file");
    badgeFormFile.hide();

    $(".nav-tabs a.has-error:first").tab("show");

    var uploadImagePlaceholder = $(".upload_image_placeholder");
    uploadImagePlaceholder.click(function(event) {
        badgeFormFile.click();
        event.preventDefault();
    });

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