/* Global Translator */

(function () {
    'use strict';

    var env = $('#sf-environement').attr('data-env');
    var stackedRequests = 0;
    var ajaxServerErrorHandler = function (statusCode, responseText) {
        if (env !== 'prod') {
            var w = window.open();
            $(w.document.body).html(responseText);
        } else {
            var msg = statusCode === 403 ? 'not_allowed' : 'an_error_occured';
            alert(Translator.get('platform:' + msg + '_message'));
        }
    };
    var ajaxAuthenticationErrorHandler = function (form) {
        $('#ajax-login-validation-box-body').append(form);
        $('#ajax-login-modal').modal('show');
        $('#login-form').submit(function (e) {
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
                        alert(data.error);
                    } else {
                        window.location.reload();
                    }
                }
            });
        });
    };

    $('body').bind('ajaxSend', function () {
        stackedRequests++;
        $('.please-wait').show();
    }).bind('ajaxComplete', function () {
        stackedRequests--;

        if (stackedRequests === 0) {
            $('.please-wait').hide();
        }
    });

    $(document).ajaxError(function (event, jqXHR) {
        console.debug(jqXHR.getResponseHeader('XXX-Claroline'));
        console.debug(jqXHR);
        if (jqXHR.status === 403 && jqXHR.getResponseHeader('XXX-Claroline') !== 'insufficient-permissions') {
            ajaxAuthenticationErrorHandler(jqXHR.responseText);
        } else if (jqXHR.status === 500 || jqXHR.status === 422 || jqXHR.status === 403) {
            ajaxServerErrorHandler(jqXHR.status, jqXHR.responseText);
        }
    });

    //Change this to a compile-time function.
    Twig.setFunction('path', function (route, parameters) {
        return Routing.generate(route, parameters);
    });

    //required for variables translations (the language can't be known at the compile time)
    Twig.setFilter('trans', function(name, parameters, domain) {
        return Translator.get(domain + ':' + name);
    });
})();
