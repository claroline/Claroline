/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* Global Translator */

(function () {
    'use strict';

    var env = $('#sf-environement').attr('data-env');
    var stackedRequests = 0;
    var modal = window.Claroline.Modal;
    var translator = window.Translator;
    var claroDate = window.Claroline.ClaroDate;

    var ajaxServerErrorHandler = function (statusCode, responseText) {
        if (env !== 'prod') {
            var w = window.open();
            $(w.document.body).html(responseText);
        } else {
            modal.confirmContainer(translator.trans('error', {}, 'platform'), responseText);
        }
    };

    var ajaxAuthenticationErrorHandler = function (form) {
        modal.create(form).on('submit', '#login-form', function (e) {
            var $this = $(e.currentTarget);
            var inputs = {};
            e.preventDefault();

            // Send all form's inputs
            $.each($this.find('input'), function (i, item) {
                var $item = $(item);
                inputs[$item.attr('name')] = $item.val();
            });

            $.ajax({
                type: 'POST',
                url: e.currentTarget.action,
                cache: false,
                data: inputs,
                success: function (data) {
                    if (data.has_error) {
                        $('.form-group', $this).addClass('has-error');
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
    };

    $(document).bind('ajaxSend', function () {
        stackedRequests++;
        $('.please-wait').show();
    }).bind('ajaxComplete', function () {
        stackedRequests--;

        if (stackedRequests === 0) {
            $('.please-wait').hide();
        }
    });

    $(document).ajaxError(function (event, jqXHR) {
        if (jqXHR.status === 403 && jqXHR.getResponseHeader('XXX-Claroline') !== 'resource-error') {
            ajaxAuthenticationErrorHandler(jqXHR.responseText);
        } else if (jqXHR.status === 500 || jqXHR.status === 422 || jqXHR.status === 403) {
            ajaxServerErrorHandler(jqXHR.status, jqXHR.responseText);
        }
    });

    // Change this to a compile-time function.
    Twig.setFunction('path', function (route, parameters) {
        return Routing.generate(route, parameters);
    });

    // Required for variables translations (the language can't be known at the compile time)
    Twig.setFilter('trans', function (name, parameters, domain) {
        return translator.trans(name, parameters, domain);
    });

    // Required for variables translations (the language can't be known at the compile time)
    Twig.setFilter('transchoice', function (name, amt, parameters, domain) {
        return translator.transChoice(name, amt, parameters, domain);
    });

    //Required for the resource manager, when we want to open a directory after a search
    Twig.setFunction('getCurrentUrl', function() {
        return location.pathname;
    });

    // Without the next lines, the fixed top bar overlays content
    // when jumping to a an internal anchor target
    var shiftWindow = function () {
        scrollBy(0, -50);
    };
    $(document).ready(function () {
        if (location.hash) {
            setTimeout(
                function () {
                    shiftWindow();
                },
                300
            );
        }
    });
    $(window).on('hashchange', shiftWindow);



})();
