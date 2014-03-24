/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function($) {
    "use strict";

    $(function() {
        var form     = $('#public_profile_preferences');
        var formName = 'user_public_profile_preferences_form';

        form.change(function() {
            manageVisibility(parseFormValue($(this).serializeArray()));
        });

        function manageVisibility(data)
        {
            console.log('Form updated, parsing data...');
            console.log(data);
            if (data.share_policy != undefined) {
                console.log('share policy updated.');
            }
        }

        function parseFormValue(formValue)
        {
            var parsedFormValue = {};
            $.each(formValue, function(index, element) {
                var parsedName = element.name;
                var parsedName = parsedName.substring(formName.length + 1, parsedName.length - 1);
                if ('_token' != parsedName) {
                    parsedFormValue[parsedName] = element.value;
                }
            });

            return parsedFormValue;
        };
    });
})(jQuery);