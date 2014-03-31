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
        var form                                = $('#public_profile_preferences');
        var formName                            = 'user_public_profile_preferences_form';
        var basicInformationPublicProfile       = $('#user_public_profile_preferences_form_display_base_informations', form);
        var userPublicProfileNotVisibleBlock    = $('#user_public_profile_not_visible');
        var userPublicProfileVisibleBlocks      = $(".profil_visible");
        var preferencesField                    = $('.preferences input[type=checkbox]');
        var currentUserPublicProfilePreferences = {};

        preferencesField.each(function() {
            var preferenceName = parseFieldName($(this).attr('name'));

             if('display_base_informations' !== preferenceName) {
                 currentUserPublicProfilePreferences[preferenceName] = $('#' + formName + '_' + preferenceName).attr('checked');
             }
        });

        var currentSharedPolicy = parseFormValue($(form).serializeArray()).share_policy;

        form.change(function() {
            manageVisibility(parseFormValue($(this).serializeArray()));
        });

        function manageVisibility(data)
        {
            if (data['share_policy'] != undefined && data['share_policy'] != currentSharedPolicy) {
                sharedPolicyUpdated(data['share_policy']);
                currentSharedPolicy = data['share_policy'];

                preferencesField.each(function() {
                    var preferenceName = parseFieldName($(this).attr('name'));
                    updateFieldVisibility(preferenceName, currentUserPublicProfilePreferences[preferenceName]);
                });
            }
            else {
                // Case where share policy is nobody but we want to display a field, changing shared policy to platform users
                if (0 == data['share_policy']) {
                    for (var currentUserPublicProfilePreferenceIndex in currentUserPublicProfilePreferences) {
                        if (data[currentUserPublicProfilePreferenceIndex] == undefined) {
                            currentUserPublicProfilePreferences[currentUserPublicProfilePreferenceIndex] = false;
                        }
                        else {
                            currentUserPublicProfilePreferences[currentUserPublicProfilePreferenceIndex] = 'checked';
                        }
                    }
                    $("input[name='" + formName + "[share_policy]'][value=1]", form).click();
                }
                else {
                    preferencesField.each(function() {
                        var preferenceName = parseFieldName($(this).attr('name'));
                        updateFieldVisibility(preferenceName, data[preferenceName] != undefined);
                    });
                }
            }
        }

        function sharedPolicyUpdated(sharedPolicy) {
            if (0 == sharedPolicy) {
                userPublicProfileNotVisibleBlock.removeClass('hidden');
                userPublicProfileVisibleBlocks.addClass('hidden');

                basicInformationPublicProfile
                    .attr('checked', false)
                    .attr('disabled', false);

                preferencesField.each(function() {
                    $(this).attr('checked', false);
                });
            }
            else {
                userPublicProfileVisibleBlocks.removeClass('hidden');
                userPublicProfileNotVisibleBlock.addClass('hidden');

                basicInformationPublicProfile
                    .attr('checked', 'checked')
                    .attr('disabled', 'disabled');

                preferencesField.each(function() {
                    $(this).attr('checked', currentUserPublicProfilePreferences[parseFieldName($(this).attr('name'))]);
                });
            }
        }

        function updateFieldVisibility(field, visibility) {
            var block = $('#' + field);
            if('display_base_informations' !== field) {
                if (visibility) {
                    block.removeClass('hidden');
                    currentUserPublicProfilePreferences[field] = 'checked';
                }
                else {
                    block.addClass('hidden');
                    currentUserPublicProfilePreferences[field] = false;
                }
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
        }

        function parseFieldName(name) {
            return name.substring(formName.length + 1, name.length - 1)
        }
    });
})(jQuery);