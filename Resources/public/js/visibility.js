(function($) {
    "use strict";

    $(function() {
        var visibilityFormName = "icap_portfolio_visibility_form";
        var visibilityForm     = $("#visibility_form");
        var chooseUserBlock    = $("#choose_user");
        var chooseGroupBlock   = $("#choose_group");
        var userSelect2Field   = $("#icap_portfolio_visibility_form_search_user");
        var groupSelect2Field  = $("#icap_portfolio_visibility_form_search_group");
        var addingUserButton   = $('.form-collection-add-user');
        var addingGroupButton  = $('.form-collection-add-group');

        ZenstruckFormHelper.initSelect2Helper()

        $('.form-collection').on('click', '.form-collection-element a.remove', function(e) {
            e.preventDefault();
            $(this).parents('.form-collection-element').remove();
        });

        // form collection prototype creation
        addingUserButton.on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $container = $this.siblings('div[data-prototype]').first();
            var count = $('.form-collection-element', $container).length;
            var prototype = $container.data('prototype');

            // set count, used as id in DOM
            prototype = prototype.replace(/__name__/g, count);
            // set label
            prototype = prototype.replace(/__value__/g, userSelect2Field.select2('data').text);

            // create dom element
            var $newWidget = $(prototype.trim());
            // set user id
            $('input', $newWidget).val(userSelect2Field.select2('data').id);

            $container.children('.form-collection').removeClass('hide').append($newWidget);

            userSelect2Field.select2('data', null);
            $this.attr('disabled', 'disabled');
        });

        // form collection prototype creation
        addingGroupButton.on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $container = $this.siblings('div[data-prototype]').first();
            var count = $('.form-collection-element', $container).length;
            var prototype = $container.data('prototype');

            // set count, used as id in DOM
            prototype = prototype.replace(/__name__/g, count);
            // set label
            prototype = prototype.replace(/__value__/g, groupSelect2Field.select2('data').text);

            // create dom element
            var $newWidget = $(prototype.trim());
            // set user id
            $('input', $newWidget).val(groupSelect2Field.select2('data').id);

            $container.children('.form-collection').removeClass('hide').append($newWidget);

            groupSelect2Field.select2('data', null);
            $this.attr('disabled', 'disabled');
        });

        userSelect2Field
            .on("change", function(e) {
                log("change "+JSON.stringify({val:e.val, added:e.added, removed:e.removed}));
                addingUserButton.removeAttr('disabled');
            });
        groupSelect2Field
            .on("change", function(e) {
                log("change "+JSON.stringify({val:e.val, added:e.added, removed:e.removed}));
                addingGroupButton.removeAttr('disabled');
            });

        var log = function (message) {
            console.log(message);
        };

        visibilityForm.change(function() {
            var formValues = parseFormValue($(this).serializeArray());
            if (1 == formValues.visibility) {
                chooseUserBlock.removeClass('hidden');
                chooseGroupBlock.removeClass('hidden');
            }
            else {
                chooseUserBlock.addClass('hidden');
                chooseGroupBlock.addClass('hidden');
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
