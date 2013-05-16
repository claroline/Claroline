(function () {
    var env = $('#sf-environement').attr('data-env');
    var stackedRequests = 0;
    var ajaxServerErrorHandler = function (html) {
        if (env === 'dev') {
            var w = window.open();
            $(w.document.body).html(html);
        } else {
            alert('An error occured. Please contact the administrator');
        }
    };
    var ajaxAuthenticationErrorHandler = function (form) {
        $('#ajax-login-validation-box-body').append(form);
        $('#ajax-login-modal').modal('show');
        $('#login-form').submit(function (e) {
            var $this = $(e.currentTarget)
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
    }

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
        if (jqXHR.status == 403) {
            ajaxAuthenticationErrorHandler(jqXHR.responseText);
        } else if (jqXHR.status == 500) {
            ajaxServerErrorHandler(jqXHR.responseText);
        }
    });

    //Change this to a compile-time function.
    Twig.setFunction('path', function(route, parameters){
        return Routing.generate(route, parameters);
    })
})();