(function($) {
    "use strict";

    $(function() {
        var visibilityFormName = "icap_portfolio_visibility_form";
        var visibilityForm     = $("#visibility_form");
        var chooseUserBlock    = $("#choose_user");

        ZenstruckFormHelper.initSelect2Helper();
        ZenstruckFormHelper.initFormCollectionHelper();

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
