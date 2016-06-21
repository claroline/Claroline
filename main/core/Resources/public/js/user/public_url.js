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
        var urlForm          = $('#public_profile_url');
        var urlField         = $('#user_public_profile_url_form_public_url', urlForm);
        var currentPublicUrl = $('#current_public_profile_url').val();
        var loadingBlock     = $('.feedback .loading', urlForm);
        var okBlock          = $('.feedback .fa-check', urlForm);
        var koBlock          = $('.feedback .fa-times', urlForm);
        var tuneButton       = $('#tune_button');
        var timer;

        urlField.keyup(function() {
            clearTimeout(timer);
            timer = setTimeout(search, 300);
        });

        var search = function () {
            var publicUrl = urlField.val()
            loadingState();

            if ('' == publicUrl || /[\s\/]/g.test(publicUrl)) {
                koState();
            }
            else if (currentPublicUrl != publicUrl) {
                $.post(
                    urlForm.attr('data-ajax-action'),
                    {'publicUrl': publicUrl},
                    function(data) {
                        if (data.check) {
                            okState();
                        }
                        else {
                            koState();
                        }
                    })
                    .fail(function() {
                        koState();
                    });
            }
            else {
                okState();
                tuneButton.attr('disabled', 'disabled');
            }
        };

        if (currentPublicUrl != urlField.val()) {
            search();
        }

        function loadingState()
        {
            urlField
                .removeClass('check-ok')
                .removeClass('check-ko');
            loadingBlock.removeClass('hidden');
            okBlock.addClass('hidden');
            koBlock.addClass('hidden');
            tuneButton.attr('disabled', 'disabled');
        }

        function okState()
        {
            urlField
                .removeClass('check-ko')
                .addClass('check-ok');
            loadingBlock.addClass('hidden');
            okBlock.removeClass('hidden');
            koBlock.addClass('hidden');
            tuneButton.attr('disabled', false);
        }

        function koState()
        {
            urlField
                .removeClass('check-ok')
                .addClass('check-ko');
            loadingBlock.addClass('hidden');
            okBlock.addClass('hidden');
            koBlock.removeClass('hidden');
            tuneButton.attr('disabled', 'disabled');
        }
    });
})(jQuery);
