(function () {
    var stackedRequests = 0;
    var env = $('#sf-environement').attr('data-env');

    $('body').bind('ajaxSend', function(){
        stackedRequests++;
        $('.please-wait').show();
    }).bind('ajaxComplete', function(){
        stackedRequests--;

        if (stackedRequests === 0) {
            $('.please-wait').hide();
        }
    });

    var ajaxAuthenticationErrorHandler = function (form) {
        $('#ajax-login-validation-box-body').append(form);
        $('#ajax-login-modal').modal('show');
        $('#login-form').submit(function (e) {
            var $this = $(e.currentTarget)
            var inputs = {};

            // Send all form's inputs
            $.each($this.find('input'), function (i, item) {
                var $item = $(item);
                inputs[$item.attr('name')] = $item.val();
            });
            console.debug(inputs)
            e.preventDefault();
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

    if  (env == 'dev') {
        var ajaxServerErrorHandler = function(html) {
            var w = window.open();
            $(w.document.body).html(html);
        }
    } else {
        var ajaxServerErrorHandler = function(html) {
            alert('An error occured. Please contact the administrator');
        }
    }

    $(document).ajaxError(function(event, jqXHR, ajaxSettings, throwError){

        if (jqXHR.status == 403) {
            ajaxAuthenticationErrorHandler(jqXHR.responseText);
        }

        if (jqXHR.status == 500) {
            ajaxServerErrorHandler(jqXHR.responseText);
        }
    });

    //Change this to a compile-time function.
    Twig.setFunction('path', function(route, parameters){
        return Routing.generate(route, parameters);
    })
})();