(function($) {
    var banner                               = $("#blog_banner");
    var bannerActivate                       = $("#icap_blog_banner_form_banner_activate");
    var bannerHeight                         = $("#icap_blog_banner_form_banner_height");
    var bannerBackgroundImageColorPicker     = $('.banner_background_color');
    var bannerBackgroundColorField           = $('#icap_blog_banner_form_banner_background_color');
    var bannerBackgroundImageContainer       = $("#icap_blog_banner_form_banner_background_image_container");
    var bannerBackgroundImageFieldTemplate   = '<input type="file" id="icap_blog_banner_form_file" name="icap_blog_banner_form[file]" class="form-control">';
    var removeBannerBackgroundImageButton    = $("#remove_banner_background_image");
    var bannerBackgroundImageField           = $("#icap_blog_banner_form_banner_background_image");
    var bannerBackgroundImageParametersBlock = $("#banner_background_image_parameters");
    var bannerBackgroundImagePositionField   = $("#icap_blog_banner_form_banner_background_image_position").hide();
    var bannerBackgroundImagePositionBlock   = $(".position_table", bannerBackgroundImageParametersBlock);
    var bannerBackgroundImageRepeatBlock     = $("#icap_blog_banner_form_banner_background_image_repeat_choices", bannerBackgroundImageParametersBlock);
    var bannerBackgroundImageRepeatField     = $("#icap_blog_banner_form_banner_background_image_repeat", bannerBackgroundImageParametersBlock).hide();
    var bannerBackgroundImageRepeatFieldX    = $("#icap_blog_banner_form_banner_background_image_repeat_x", bannerBackgroundImageRepeatBlock);
    var bannerBackgroundImageRepeatFieldY    = $("#icap_blog_banner_form_banner_background_image_repeat_y", bannerBackgroundImageRepeatBlock);
    var bannerResetButton                    = $("#btn_reset");

    var initialIsBannerActivate                  = bannerActivate.is(":checked");
    var initialBannerHeight                      = bannerHeight.val();
    var initialBannerBackgroundColor             = bannerBackgroundColorField.val();
    var initialBannerBackgroundImage             = bannerBackgroundImageField.val();
    var initialBannerBackgroundImagePosition     = bannerBackgroundImagePositionField.val();
    var initialBannerBackgroundImageRepeat       = bannerBackgroundImageRepeatField.val();

    bannerBackgroundImageColorPicker.colorpicker({format: 'hex'}).on('changeColor', function (event) {
        bannerBackgroundColorField.val(event.color.toHex());
        changeBannerBackgroundColor();
    });
    bannerBackgroundColorField.change(function (event) {
        bannerBackgroundColorField.val($(this).val());
        changeBannerBackgroundColor();
    });

    function changeBannerBackgroundColor()
    {
        var color = bannerBackgroundColorField.val();
        $(".input-group-addon", bannerBackgroundImageColorPicker).css('background-color', color);
        banner.css('background-color', color);
    }

    bannerActivate.change(function (event) {
        banner.toggleClass('hidden');
    });

    bannerHeight.spinner(bannerHeight.data());
    bannerHeight
        .on('spin', function (event, ui) {
            changeBannerHeight();
        })
        .change(function (event) {
            var newHeight = $(this).val();

            if (100 > newHeight) {
                $(this).val(100);
            }

            changeBannerHeight();
        });

    function changeBannerHeight()
    {
        banner.css('height', bannerHeight.val());
    }

    bannerBackgroundImageContainer.on('change', "#icap_blog_banner_form_file", function(){
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (event) {
                banner.css('background-image', 'url(' + event.target.result + ')');
                bannerBackgroundImageField.val(input.files[0].name);
                removeBannerBackgroundImageButton.removeClass("hidden");
                bannerBackgroundImageParametersBlock.removeClass("hidden");
            };

            reader.readAsDataURL(input.files[0]);
        }
    });

    removeBannerBackgroundImageButton.click(function (event) {
        banner.css('background-image', 'none');
        bannerBackgroundImageField.val(null);
        removeBannerBackgroundImageButton.addClass("hidden");
        bannerBackgroundImageParametersBlock.addClass("hidden");
        $("#icap_blog_banner_form_file", bannerBackgroundImageContainer).remove();
        bannerBackgroundImageContainer.append(bannerBackgroundImageFieldTemplate);
        bannerBackgroundImageRepeatField.val("no-repeat");
        bannerBackgroundImagePositionField.val("left top")
        initBannerForm();
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

    function initBannerForm()
    {
        if ("no-repeat" != bannerBackgroundImageRepeatField.val()) {
            switch(bannerBackgroundImageRepeatField.val()) {
                case "repeat":
                    bannerBackgroundImageRepeatFieldX.attr('checked', true);
                    bannerBackgroundImageRepeatFieldY.attr('checked', true);
                    break;
                case "repeat-x":
                    bannerBackgroundImageRepeatFieldX.attr('checked', true);
                    break;
                case "repeat-y":
                    bannerBackgroundImageRepeatFieldY.attr('checked', true);
                    break;
            }
        }

        updateBannerBackgroundImage();
    }

    initBannerForm();

    function resetBannerForm()
    {
        bannerActivate.attr("checked", initialIsBannerActivate);
        bannerHeight.val(initialBannerHeight);
        bannerBackgroundColorField.val(initialBannerBackgroundColor);
        bannerBackgroundImageField.val(initialBannerBackgroundImage);
        bannerBackgroundImagePositionField.val(initialBannerBackgroundImagePosition);
        bannerBackgroundImageRepeatField.val(initialBannerBackgroundImageRepeat);

        initBannerForm();
        changeBannerBackgroundColor();
        changeBannerHeight();
        resetImageField();
    }

    function resetImageField()
    {
        if ('' == initialBannerBackgroundImage) {
            banner.css('background-image', 'none');
            removeBannerBackgroundImageButton.addClass("hidden");
            bannerBackgroundImageParametersBlock.addClass("hidden");
            $("#icap_blog_banner_form_file", bannerBackgroundImageContainer).remove();
            bannerBackgroundImageContainer.append(bannerBackgroundImageFieldTemplate);
        }
        else {
            banner.css('background-image', 'url(' + bannerBackgroundImageContainer.data("image-path") + "/" + initialBannerBackgroundImage + ')');
            removeBannerBackgroundImageButton.removeClass("hidden");
            bannerBackgroundImageParametersBlock.removeClass("hidden");
            $("#icap_blog_banner_form_file", bannerBackgroundImageContainer).remove();
            bannerBackgroundImageContainer.append(bannerBackgroundImageFieldTemplate);
        }

        if (bannerActivate.is(":checked")) {
            banner.removeClass('hidden');
        }
        else {
            banner.addClass('hidden');
        }
    }

    bannerResetButton.click(function(event) {
        resetBannerForm();
    });
})(jQuery);