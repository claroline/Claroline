(function($) {
    var banner                               = $("#blog_banner");
    var bannerHeight                         = $("#icap_blog_banner_form_banner_height");
    var bannerBackgroundImageColorPicker     = $('.banner_background_color');
    var bannerBackgroundColorField           = $('#icap_blog_banner_form_banner_background_color');
    var bannerBackgroundImageContainer       = $("#icap_blog_banner_form_banner_background_image_container");
    var bannerBackgroundImageFieldTemplate   = '<input type="file" id="icap_blog_banner_form_banner_background_image" name="icap_blog_banner_form[banner_background_image]" class="form-control">';
    var removeBannerBackgroundImageButton    = $("#remove_banner_background_image");
    var bannerBackgroundImageParametersBlock = $("#banner_background_image_parameters");
    var bannerBackgroundImagePositionField   = $("#icap_blog_banner_form_banner_background_image_position").hide();
    var bannerBackgroundImagePositionBlock   = $(".position_table", bannerBackgroundImageParametersBlock);
    var bannerBackgroundImageRepeatBlock     = $("#icap_blog_banner_form_banner_background_image_repeat_choices", bannerBackgroundImageParametersBlock);
    var bannerBackgroundImageRepeatField     = $("#icap_blog_banner_form_banner_background_image_repeat", bannerBackgroundImageParametersBlock).hide();
    var bannerBackgroundImageRepeatFieldX    = $("#icap_blog_banner_form_banner_background_image_repeat_x", bannerBackgroundImageRepeatBlock);
    var bannerBackgroundImageRepeatFieldY    = $("#icap_blog_banner_form_banner_background_image_repeat_y", bannerBackgroundImageRepeatBlock);

    bannerBackgroundImageColorPicker.colorpicker({format: 'hex'}).on('changeColor', function (event) {
        changeBannerBackgroundColor(event.color.toHex());
    });
    bannerBackgroundColorField.change(function (event) {
        changeBannerBackgroundColor($(this).val());
    });

    function changeBannerBackgroundColor(color)
    {
        bannerBackgroundColorField.val(color);
        $(".input-group-addon", bannerBackgroundImageColorPicker).css('background-color', color);
        banner.css('background-color', color);
    }

    $("#icap_blog_banner_form_banner_activate").change(function (event) {
        banner.toggleClass('hidden');
    });

    bannerHeight.spinner(bannerHeight.data());
    bannerHeight
        .on('spin', function (event, ui) {
            var newHeight = $(this).val();
            banner.css('height', newHeight);
        })
        .change(function (event) {
            var newHeight = $(this).val();

            if (100 > newHeight) {
                $(this).val(100);
            }
            banner.css('height', $(this).val());
        });

    bannerBackgroundImageContainer.on('change', "#icap_blog_banner_form_banner_background_image", function(){
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (event) {
                banner.css('background-image', 'url(' + event.target.result + ')');
                removeBannerBackgroundImageButton.removeClass("hidden");
                bannerBackgroundImageParametersBlock.removeClass("hidden");
            };

            reader.readAsDataURL(input.files[0]);
        }
    });

    removeBannerBackgroundImageButton.click(function (event) {
        banner.css('background-image', 'none');
        removeBannerBackgroundImageButton.addClass("hidden");
        bannerBackgroundImageParametersBlock.addClass("hidden");
        $("#icap_blog_banner_form_banner_background_image", bannerBackgroundImageContainer).remove();
        bannerBackgroundImageContainer.append(bannerBackgroundImageFieldTemplate);
    });

    $("input[type=checkbox]", bannerBackgroundImageRepeatBlock).click(function (event) {
        var checkbox       = $(this);
        var repeatValue    = 0;
        var repeatString   = "no-repeat";

        if (bannerBackgroundImageRepeatFieldX.is(":checked") && bannerBackgroundImageRepeatFieldY.is(":checked")) {
            repeatString = "repeat";
        }
        else {
            if (bannerBackgroundImageRepeatFieldX.is(":checked")) {
                repeatString = "repeat-x";
            }
            if (bannerBackgroundImageRepeatFieldY.is(":checked")) {
                repeatString = "repeat-y";
            }
        }

        bannerBackgroundImageRepeatField.val(repeatString);

        updateBannerBackgroundImage();
    });

    $(".orientation_btn", bannerBackgroundImagePositionBlock).click(function (event) {
        $(".orientation_btn.selected", bannerBackgroundImagePositionBlock).removeClass('selected');

        var newPosition = $(this);
        newPosition.addClass('selected');

        bannerBackgroundImagePositionField.val(newPosition.data('value'));

        updateBannerBackgroundImage();
    });

    function updateBannerBackgroundImage()
    {
        var repeatString     = bannerBackgroundImageRepeatField.val();
        var selectedPosition = bannerBackgroundImagePositionField.val().split(" ");

        banner.css('background-repeat', repeatString);
        banner.css('background-position', bannerBackgroundImagePositionField.val());

        $(".orientation_btn.selected", bannerBackgroundImagePositionBlock).removeClass('selected');
        switch(repeatString) {
            case 'no-repeat':
                $(".orientation_btn[data-value='" + bannerBackgroundImagePositionField.val() + "']", bannerBackgroundImagePositionBlock).addClass('selected');
                break;
            case 'repeat':
                $(".orientation_btn", bannerBackgroundImagePositionBlock).addClass('selected');
                break;
            case 'repeat-x':
                $(".orientation_btn.x" + selectedPosition[1], bannerBackgroundImagePositionBlock).addClass('selected');
                break;
            case 'repeat-y':
                $(".orientation_btn.y" + selectedPosition[0], bannerBackgroundImagePositionBlock).addClass('selected');
                break;
        }
    }
})(jQuery);