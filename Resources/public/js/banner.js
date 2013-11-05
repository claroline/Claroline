(function($) {
    var banner                               = $("#blog_banner");
    var bannerHeight                         = $("#icap_blog_options_form_banner_height");
    var bannerBackgroundImageColorPicker     = $('.banner_background_color');
    var bannerBackgroundColorField           = $('#icap_blog_options_form_banner_background_color');
    var bannerBackgroundImageContainer       = $("#icap_blog_options_form_banner_background_image_container");
    var bannerBackgroundImageFieldTemplate   = '<input type="file" id="icap_blog_options_form_banner_background_image" name="icap_blog_options_form[banner_background_image]" class="form-control">';
    var removeBannerBackgroundImageButton    = $("#remove_banner_background_image");
    var bannerBackgroundImageParametersBlock = $("#banner_background_image_parameters");
    var bannerBackgroundImagePositionField   = $("#icap_blog_options_form_banner_background_image_position").hide();
    var bannerBackgroundImagePositionBlock   = $(".position_table", bannerBackgroundImageParametersBlock);
    var bannerBackgroundImageRepeatBlock     = $("#icap_blog_options_form_banner_background_image_repeat_choices", bannerBackgroundImageParametersBlock);
    var bannerBackgroundImageRepeatField     = $("#icap_blog_options_form_banner_background_image_repeat", bannerBackgroundImageParametersBlock).hide();
    var bannerBackgroundImageRepeatFieldX    = $("#icap_blog_options_form_banner_background_image_repeat_x", bannerBackgroundImageRepeatBlock);
    var bannerBackgroundImageRepeatFieldY    = $("#icap_blog_options_form_banner_background_image_repeat_y", bannerBackgroundImageRepeatBlock);

    var tabPosition = new Array();
    tabPosition[0]  = new Array();
    tabPosition[0]["right"]  = "100%";
    tabPosition[0]["center"] = "50%";
    tabPosition[0]["left"]   = "0%";
    tabPosition[1]  = new Array();
    tabPosition[1]["bottom"] = "100%";
    tabPosition[1]["center"] = "50%";
    tabPosition[1]["top"]    = "0%";

    bannerBackgroundImageColorPicker.colorpicker({format: 'hex'}).on('changeColor', function (event) {
        changeBannerBackgroundColor(event.color.toHex());
    });
    bannerBackgroundColorField.change(function (event) {
        changeBannerBackgroundColor($(this).val());
    });

    function changeBannerBackgroundColor(color)
    {
        bannerBackgroundColorField.val(color);
        console.log($(".input-group-addon", this));
        $(".input-group-addon", bannerBackgroundImageColorPicker).css('background-color', color);
        banner.css('background-color', color);
    }

    $("#icap_blog_options_form_banner_activate").change(function (event) {
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

    bannerBackgroundImageContainer.on('change', "#icap_blog_options_form_banner_background_image", function(){
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
        $("#icap_blog_options_form_banner_background_image", bannerBackgroundImageContainer).remove();
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

        var positions = newPosition.data('value').split(" ");
        var positionX = tabPosition[0][positions[0]];
        var positionY = tabPosition[1][positions[1]];
        var bannerBackgroundImagePosition = positionX + " " + positionY;

        bannerBackgroundImagePositionField.val(bannerBackgroundImagePosition);

        updateBannerBackgroundImage();
    });

    function updateBannerBackgroundImage()
    {
        var repeatString     = bannerBackgroundImageRepeatField.val();
        var selectedPosition = $(".orientation_btn.selected", bannerBackgroundImagePositionBlock).data('value').split(" ");

        banner.css('background-repeat', repeatString);
        banner.css('background-position', bannerBackgroundImagePositionField.val());
    }
})(jQuery);