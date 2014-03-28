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
        var form                             = $('#public_profile_preferences');
        var formName                         = 'user_public_profile_preferences_form';
        var basicInformationPublicProfile    = $('#user_public_profile_preferences_form_display_base_informations', form);
        var userPublicProfileNotVisibleBlock = $('#user_public_profile_not_visible');
        var userPublicProfileVisibleBlocks   = $(".profil_visible");

        var displayPhoneNumberField = $('#' + formName + '_display_phone_number');
        var displayEmailField       = $('#' + formName + '_display_email');

        var currentUserPublicProfilePreferences = {
            'display_phone_number': displayPhoneNumberField.attr('checked'),
            'display_email':        displayEmailField.attr('checked')
        };
        var currentSharedPolicy = parseFormValue($(form).serializeArray()).share_policy;

        form.change(function() {
            manageVisibility(parseFormValue($(this).serializeArray()));
        });

        function manageVisibility(data)
        {
            if (data.share_policy != undefined && data.share_policy != currentSharedPolicy) {
                sharedPolicyUpdated(data.share_policy);
                currentSharedPolicy = data.share_policy;

            }
            updateFieldVisibility('base_informations', data.display_base_informations != undefined);
            updateFieldVisibility('phone_number', data.display_phone_number != undefined);
            updateFieldVisibility('email', data.display_email != undefined);
        }

        function sharedPolicyUpdated(sharedPolicy) {
            if (0 == sharedPolicy) {
                userPublicProfileNotVisibleBlock.removeClass('hidden');
                userPublicProfileVisibleBlocks.addClass('hidden');

                basicInformationPublicProfile
                    .attr('checked', false)
                    .attr('disabled', false);
                displayPhoneNumberField.attr('checked', false);
                displayEmailField.attr('checked', false);
            }
            else {
                userPublicProfileVisibleBlocks.removeClass('hidden');
                userPublicProfileNotVisibleBlock.addClass('hidden');

                basicInformationPublicProfile
                    .attr('checked', 'checked')
                    .attr('disabled', 'disabled');
            }
        }

        function updateFieldVisibility(field, visibility) {
            var block = $('#' + field);
            if (visibility) {
                block.removeClass('hidden');
                currentUserPublicProfilePreferences['display_' + field] = 'checked';
            }
            else {
                block.addClass('hidden');
                currentUserPublicProfilePreferences['display_' + field] = false;
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