(function($) {
    "use strict";

    $(function() {
        var visibilityFormName = "icap_portfolio_visibility_form";
        var visibilityForm     = $("#visibility_form");
        var chooseBlock        = $("#choose_visibility_elements");
        var userSelect2Field   = $("#icap_portfolio_visibility_form_search_user");
        var groupSelect2Field  = $("#icap_portfolio_visibility_form_search_group");
        var teamSelect2Field   = $("#icap_portfolio_visibility_form_search_team");
        var addingUserButton   = $('.form-collection-add-user');
        var addingGroupButton  = $('.form-collection-add-group');
        var addingTeamButton   = $('.form-collection-add-team');

        ZenstruckFormHelper.initSelect2Helper()

        $('.form-collection').on('click', '.form-collection-element a.remove', function(e) {
            e.preventDefault();
            $(this).parents('.form-collection-element').remove();
        });

        // form collection prototype creation
        addingUserButton.on('click', function(event) {
            addingButtonClick(event, $(this), userSelect2Field);
        });

        // form collection prototype creation
        addingGroupButton.on('click', function(event) {
            addingButtonClick(event, $(this), groupSelect2Field);
        });

        // form collection prototype creation
        addingTeamButton.on('click', function(event) {
            addingButtonClick(event, $(this), teamSelect2Field);
        });

        var addingButtonClick = function (event, element, select2Field) {
            event.preventDefault();

            var $container = element.siblings('div[data-prototype]').first();
            var count = $('.form-collection-element', $container).length;
            var prototype = $container.data('prototype');

            // set count, used as id in DOM
            prototype = prototype.replace(/__name__/g, count);
            // set label
            prototype = prototype.replace(/__value__/g, select2Field.select2('data').text);

            // create dom element
            var $newWidget = $(prototype.trim());
            // set user id
            $('input', $newWidget).val(select2Field.select2('data').id);

            $container.children('.form-collection').removeClass('hide').append($newWidget);

            select2Field.select2('data', null);
            element.attr('disabled', 'disabled');
        };

        userSelect2Field.on("change", function(event) {
            select2FieldChange(event, addingUserButton);
        });
        groupSelect2Field.on("change", function(event) {
            select2FieldChange(event, addingGroupButton);
        });
        teamSelect2Field.on("change", function(event) {
            select2FieldChange(event, addingTeamButton);
        });

        var select2FieldChange = function (event, button) {
            var existingFieldValue = $('.form-collection input[value=' + event.val + ']', button.parent());
            if (0 >= existingFieldValue.length) {
                //console.log("change " + JSON.stringify({val: event.val, added: event.added, removed: event.removed}));
                button.prop('disabled', false);
            }
            else {
                button.prop('disabled', true);
                existingFieldValue.parent().effect("highlight", {color: '#d9534f'}, 1500);
            }
        };

        visibilityForm.change(function() {
            var formValues = parseFormValue($(this).serializeArray());
            if (1 == formValues.visibility) {
                chooseBlock.removeClass('hidden');
            }
            else {
                chooseBlock.addClass('hidden');
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
