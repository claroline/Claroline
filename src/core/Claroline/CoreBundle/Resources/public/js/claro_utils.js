(function ($, window, undefined) {

    var utils = this.ClaroUtils = {};

    utils.ajaxAuthenticationErrorHandler = function (callBack) {
        $.ajax({
            type: 'GET',
            url: Routing.generate('claro_security_login'),
            cache: false,
            success: function (data) {
                $('#modal-body').hide();
                $('#modal-login').append(data);
                $('#bootstrap-modal').modal('show');
                $('#login-form').submit(function (e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: Routing.generate('claro_security_login_check'),
                        cache: false,
                        data: $('#login-form').serialize(),
                        success: function (data) {
                            $('#bootstrap-modal').modal('hide');
                            callBack();
                        }
                    });
                });
            }
        });
    }


    utils.sendRequest = function (route, successHandler, completeHandler) {
        var url = '';
        'string' == typeof route ? url = route : url = Routing.generate(route.name, route.parameters);
        $.ajax({
            type: 'GET',
            url: url,
            cache: false,
            success: function (data, textStatus, jqXHR) {
                if ('function' == typeof successHandler) {
                    successHandler(data, textStatus, jqXHR);
                }
            },
            complete: function(data){
                if ('function' == typeof completeHandler){
                    completeHandler(data)}
            },
            error: function(xhr, e, errorThrown){
                if (xhr.status == 403){
                    utils.ajaxAuthenticationErrorHandler(function () {
                        'function' == typeof successHandler ?
                            utils.sendRequest(route, successHandler) :
                            window.location.reload();
                    })
                } else {
                    var title = utils.getTitle(xhr.responseText)
                    if(title !== '') {
                        alert(title);
                    }
                    else {
                        alert(xhr.responseText);
                    }
                }
            }
        });
    }

    utils.sendForm = function(route, form, successHandler){
        var url = '';
        'string' == typeof route ? url = route : url = Routing.generate(route.name, route.parameters);
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X_Requested_With', 'XMLHttpRequest');
        xhr.onload = function (e) {
            successHandler(xhr);
        };
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4){
                if (xhr.status == 403){
                    window.location.reload();
                }
            }
        }
        xhr.send(formData);
    }

    /**
     * Returns the check value of a combobox form.
     */
    utils.getCheckedValue = function (radioObj) {
        if (!radioObj) {
            return '';
        }

        var radioLength = radioObj.length;

        if (radioLength == undefined) {
            if (radioObj.checked) {
                return radioObj.value;
            } else {
                return '';
            }
        }

        for (var i = 0; i < radioLength; i++) {
            if (radioObj[i].checked) {
                return radioObj[i].value;
            }
        }
        return '';
    }

    utils.findLoadedJsPath = function (filename) {
        return $('script[src*="'+filename+'"]').attr('src');
    }

    utils.splitCookieValue = function(cookie) {
        var values = new Object();
        var cookieArray = cookie.split(';');

        for (var i=0; i<cookieArray.length; i++){
            var key = cookieArray[i].split('=')[0];
            var value = cookieArray[i].split('=')[1];
            value.replace(/^\s*|\s*$/g,'');
            values[key] = value;
        }

        return values;
    }

    utils.renderPager = function (nbPage, activePage, type, appendTo){

        var paginator = '';
        paginator += '<div id="'+type+'-paginator" class="pagination"><ul><li><a class="'+type+'-paginator-prev-item" href="#">Prev</a></li>'
        for (var i = 0; i < nbPage;) {
            i++;
            paginator += '<li data-page="'+i+'"><a class="'+type+'-paginator-item" href="#">'+i+'</a></li>';
        }
        paginator += '<li><a href="#" class="'+type+'-paginator-next-item">Next</a></li></ul></div>';

        appendTo.after(paginator);

        var resizePager = function(pagerItems, prev, next, activePage) {

            //how many items can we put each pages ?
            var maxSize = 0;

            if(prev.offsetTop != next.offsetTop) {
                $(pagerItems).each(function(index, value){
                    if($(this)[0].offsetTop == prev.offsetTop){
                        maxSize++;
                    }
                })
            }

            var resizeFromLeft = function (){
                var iremove = (pagerItems.length)-maxSize;
                while (iremove >= 0) {
                    $(pagerItems[iremove].remove);
                    iremove --;
                }

                var reduceLeft = function(pagerItems){
                    if (prev.offsetTop != next.offsetTop) {
                        pagerItems.first().remove();
                        reduceLeft($('.'+type+'-paginator-item'));
                    }
                }

                reduceLeft($('.'+type+'-paginator-item'));
            }

            var resizeFromRight = function(){
                var iremove = maxSize;
                while (iremove < pagerItems.length) {
                    $(pagerItems[iremove]).remove();
                    iremove++;
                }

                var reduceRight = function(pagerItems){
                    if (prev.offsetTop != next.offsetTop) {
                        pagerItems.last().remove();
                        reduceRight($('.'+type+'-paginator-item'));
                    }
                }
                reduceRight($('.'+type+'-paginator-item'));
            }

            var resizeFromCenter = function(){
                var offset = Math.floor(maxSize/2)+parseInt(activePage); //lol
                while (offset < pagerItems.length) {
                    $(pagerItems[offset]).remove();
                    offset++;
                }

                var start = parseInt(activePage)-Math.floor(maxSize/2);
                start-=2;
                while (start >= 0) {

                    $(pagerItems[start]).remove();
                    start--;
                }

                var reduceBothSide = function(pagerItems){
                    if (prev.offsetTop != next.offsetTop) {
                        pagerItems.first().remove();
                        pagerItems.last().remove();
                        reduceBothSide($('.'+type+'-paginator-item'));
                    }
                }

                reduceBothSide($('.'+type+'-paginator-item'));
            }

            if(maxSize != 0){
                if(activePage <= Math.floor(maxSize/2)) {
                    resizeFromRight();
                } else  {
                    if(activePage >= ((pagerItems.length)-Math.floor(maxSize/2))){
                        resizeFromLeft();
                    } else {
                        resizeFromCenter();
                    }
                }
            }

        }

        resizePager($('.'+type+'-paginator-item'), $('.'+type+'-paginator-prev-item')[0],  $('.'+type+'-paginator-next-item')[0], activePage)

        $('.instance-paginator-item').each(function(index, element){
            element.parentElement.className = '';
        })

        var searched = $('li[data-page="'+activePage+'"]');
        searched.first().addClass('active');

        return $('#'+type+'-paginator');
    }


    utils.getUriParameters = function(name){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

        for(var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }

        return vars;
    }

    /* Gets the <title> of a document
    http://www.devnetwork.net/viewtopic.php?f=13&t=117065
    */
    utils.getTitle = function(html){
    html = html.replace(/<script[^>]*>((\r|\n|.)*?)<\/script[^>]*>/mg, '');  //Removing <script> tags, because we don't want to execute them

    //Extract <head>
    var html_head = html.match(/<head[^>]*>((\r|\n|.)*)<\/head/m);
    html_head = html_head ? html_head[1] : '';

    var head = jQuery("<head></head>").append(html_head);
    var body = jQuery("<div></div>").append(html);
    var title = '';

    if (!head.children().length) head = body;    //For Firefox

    //IE - for some reason doesn't have <title> element
    //using regular expression to extract it:
    title = html_head.match(/<title[^>]*>((\r|\n|.)*)<\/title/m);
    title = title ? title[1] : '';

    console.log(title);  // => jQuery: The Write Less, Do More, JavaScript Library
    return title;
    }
})(jQuery, window);
