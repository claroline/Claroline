(function($) {
    "use strict";

    $(function() {
        var visibilityFormName = "icap_portfolio_visibility_form";
        var visibilityForm     = $("#visibility_form");
        var chooseUserBlock    = $("#choose_user");
        var select2Field       = $("#icap_portfolio_visibility_form_search_user");
        var addingFieldButton  = $('.form-collection-add');

        ZenstruckFormHelper.initSelect2Helper()

        $('.form-collection').on('click', '.form-collection-element a.remove', function(e) {
            e.preventDefault();
            $(this).parents('.form-collection-element').remove();
        });

        // form collection prototype creation
        addingFieldButton.on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $container = $this.siblings('div[data-prototype]').first();
            var count = $('.form-collection-element', $container).length;
            var prototype = $container.data('prototype');

            // set count
            prototype = prototype.replace(/__name__/g, count);
            // set label
            prototype = prototype.replace(/__value__/g, select2Field.select2('data').text);

            // create dom element
            var $newWidget = $(prototype.trim());

            $container.children('.form-collection').removeClass('hide').append($newWidget);

            select2Field.select2('data', null);
            $this.attr('disabled', 'disabled');
        });

        select2Field
            .on("change", function(e) {
                log("change "+JSON.stringify({val:e.val, added:e.added, removed:e.removed}));
                addingFieldButton.removeAttr('disabled');
            });

        var log = function (message) {
            console.log(message);
        };

        visibilityForm.change(function() {
            var formValues = parseFormValue($(this).serializeArray());
            if (1 == formValues.visibility) {
                chooseUserBlock.removeClass('hidden');
            }
            else {
                chooseUserBlock.addClass('hidden');
            }
        });

        function parseFormValue(formValue)
        {
            var parsedFormValue = {};
            $.each(formValue, function(index, element) {
                var parsedName = element.name;
                var parsedName = parsedName.substring(visibilityFormName.length + 1, parsedName.length - 1);
                if ('_token' != parsedName) {
                    parsedFormValue[parsedName] = element.value;
                }
            });

            return parsedFormValue;
        }
    });
})(jQuery);
