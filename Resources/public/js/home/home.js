(function () {
    'use strict';

    window.Claroline.Home = {};
    var home = window.Claroline.Home;

    home.path = $('#homePath').html(); //global

    if (!home.path) {
        home.path = './';
    }

    home.locale = $('#homeLocale').html(); //global

    if (!home.locale) {
        home.locale = 'en';
    }

    home.asset = $('#homeAsset').html(); //global

    if (!home.asset) {
        home.asset = './';
    }

    home.modal = function (url, id, element)
    {
        $('.modal').modal('hide');

        id = typeof(id) !== 'undefined' ? id : null;
        element = typeof(element) !== 'undefined' ? element : null;

        $.ajax(home.path + url)
            .done(
                function (data)
                {
                    var modal = document.createElement('div');
                    modal.className = 'modal fade';

                    if (id) {
                        modal.setAttribute('id', id);
                    }

                    if (element) {
                        $(modal).data('element', element);
                    }

                    modal.innerHTML = data;

                    $(modal).appendTo('body');

                    $(modal).modal('show');

                    $(modal).on('hidden.bs.modal', function () {
                        $(this).remove();
                    });

                }
        )
            .error(
                    function ()
                    {
                        alert('An error occurred!\n\nPlease try again later or check your internet connection');
                    }
                  )
            ;

    };

    /**
     * This function resize the height of a textarea relative of their content.
     *
     * @param [Textarea Obj] Obj The textarea to resize.
     */
    home.resize = function (obj)
    {
        var lineheight = $(obj).css('line-height').substr(0, $(obj).css('line-height').indexOf('px'));
        var lines = $(obj).val().split('\n').length;

        lineheight = parseInt(lineheight, 10) + 4;

        $(obj).css('height', ((lines + 1) * lineheight) + 'px');
    };


    home.findUrls = function (text)
    {
        var source = (text || '').toString();
        var urlArray = [];
        var matchArray;

        // Regular expression to find FTP, HTTP(S) and email URLs.
        var regexToken =
        /(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})/g;

        // Iterate through any URLs in the text.
        while ((matchArray = regexToken.exec(source)) !== null) {
            var token = matchArray[0];
            urlArray.push(token);
        }

        return urlArray;
    };

    /**
     * Create and update an element by POST method with ajax.
     *
     * @param [DOM obj] element The .creator element
     * @param [String] id The id of the content, this parameter is optional.
     *
     * @TODO Prevent multiple clicks
     */
    home.creator = function (element, id)
    {
        id = typeof(id) !== 'undefined' ? id : null;

        var creatorElement = $(element).parents('.creator').get(0);
        var title = $('.content-title', creatorElement).get(0);
        var text = $('.content-text', creatorElement).get(0);
        var type = $(creatorElement).data('type');
        var father = $(creatorElement).data('father');
        var generatedContent = '';
        var path = '';
        var contentPath = '';

        if (id) {
            path = 'content/update/' + id;
        } else {
            path = 'content/create';
        }

        if ($(creatorElement).find('.generated-content').html()) {
            generatedContent = $(creatorElement).find('.generated-content').html();
        }

        if (text.value !== '' || title.value !== '') {
            $.post(home.path + path,
                {
                    'title': title.value,
                    'text': text.value,
                    'generated': generatedContent,
                    'type': type,
                    'father': father
                }
            )
                .done(
                    function (data)
                    {
                        if (!isNaN(data) && data !== '') {
                            contentPath = 'content/' + data + '/' + type;

                            var insertElement = function (content) {
                                $(creatorElement).next().prepend(content).hide().fadeIn('slow');
                            };

                            if (father) {
                                contentPath = 'content/' + data + '/' + type + '/' + father;

                                insertElement = function (content)
                                {
                                    $('.creator' + father).after(content);
                                    $('.creator' + father).find('.collapse' + father).collapse('hide');
                                };
                            }

                            $.ajax(home.path + contentPath)
                                .done(
                                        function (data)
                                        {
                                            insertElement(data);
                                            $('.contents').trigger('ContentModified');
                                        }
                                )
                            ;

                            title.value = '';
                            text.value = '';
                            home.resize(text);
                            $(creatorElement).find('.generated').html('');

                        } else if (data === 'true') {

                            contentPath = 'content/' + id + '/' + type;

                            if (father) {
                                creatorElement = $(creatorElement).parents('.creator' + father).get(0);
                                contentPath = 'content/' + id + '/' + type + '/' + father;
                            }

                            $.ajax(home.path + contentPath)
                                 .done(
                                    function (data)
                                    {
                                        $(creatorElement).replaceWith(data);
                                        $('.contents').trigger('ContentModified');
                                    }
                                )
                            ;

                        } else {
                            home.modal('content/error');
                        }
                    }
                )
                .error(
                    function ()
                    {
                        home.modal('content/error');
                    }
                  )
            ;

        }
    };

    /**
     * Get content from a external url and put it in a creator of contents.
     *
     * @param url The url of a webpage.
     */
    home.generatedContent = function (creator, url)
    {
        $.post(home.path + 'content/graph', { 'generated_content_url': url })
            .done(
                function (data)
                {
                    if (data !== 'false') {
                        $(creator).find('.generated').html(data);
                    }
                }
             )
            .error(
                function ()
                {
                    home.modal('content/error');
                }
            )
        ;
    };

}());
