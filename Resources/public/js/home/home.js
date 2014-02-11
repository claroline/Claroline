/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    window.Claroline.Home = {};
    var home = window.Claroline.Home;

    home.path = $('#homePath').html(); //global
    home.locale = $('#homeLocale').html(); //global
    home.asset = $('#homeAsset').html(); //global


    if (!home.path) {
        home.path = './';
    }

    if (!home.locale) {
        home.locale = 'en';
    }

    if (!home.asset) {
        home.asset = './';
    }

    home.modal = function (url, id, element)
    {
        $('.modal').modal('hide');

        id = typeof(id) !== 'undefined' ? id : null;
        element = typeof(element) !== 'undefined' ? element : null;

        $.ajax(home.path + url)
        .done(function (data) {
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

        })
        .error(function () {
            alert('An error occurred!\n\nPlease try again later or check your internet connection');
        });

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
     * Insert the HTML of a new or edited content.
     */
    home.insertContent = function (creatorElement, data, type, father, update)
    {
        update = typeof(update) !== 'undefined' ? update : null;

        var contentPath = 'content/' + data + '/' + type;

        if (father) {
            contentPath += '/' + father;
        }

        $.ajax(home.path + contentPath).done(function (data) {
            if (father && !update) {
               $('.creator' + father).after(data);
               $('.creator' + father).find('.collapse' + father).collapse('hide');
            } else if (father && update) {
                $(creatorElement).parents('.creator' + father).first().replaceWith(data);
            } else if (update) {
                $(creatorElement).replaceWith(data);
            } else {
                $(creatorElement).next().prepend(data).hide().fadeIn('slow');
            }

            $('.contents').trigger('ContentModified');
        });
    };

    /**
     * Empty titles and contents in a creator for each languages.
     */
    home.emptyContent = function (creatorElement)
    {
        $('input', creatorElement).val('');
        $('textarea', creatorElement).val('');
    };

    /**
     * Create and update an element by POST method with ajax.
     *
     * @param [DOM obj] element The .creator element
     * @param [String] id The id of the content, this parameter is optional.
     *
     * @TODO Prevent multiple clicks
     */
    home.creator = function (element, id, update)
    {
        id = typeof(id) !== 'undefined' ? id : null;
        update = typeof(update) !== 'undefined' ? update : null;

        var creatorElement = $(element).parents('.creator').get(0);
        var form = $('form', creatorElement).first().serializeArray();
        var type = $(creatorElement).data('type');
        var father = $(creatorElement).data('father');
        var route ='content/create/' + type;

        if (father) {
            route += '/' + father;
        }

        if (update) {
            route ='content/update/' + id;
        }

        $.post(home.path + route, form)
        .done(function (data) {
            if (!isNaN(data) && data !== '') {
                home.insertContent(creatorElement, data, type, father);
                home.emptyContent(creatorElement);
            } else if (data === 'true') {
                home.insertContent(creatorElement, id, type, father, update);
            } else {
                home.modal('content/error');
            }
        })
        .error(function () {
            home.modal('content/error');
        });
    };

    /**
     * Get content from a external url and put it in a creator of contents.
     *
     * @param url The url of a webpage.
     */
    home.generatedContent = function (url, action, error)
    {
        error = typeof(error) !== 'undefined' ? error : true;

        $.post(home.path + 'content/graph', { 'generated_content_url': url })
        .done(function (data) {
            if (data !== 'false') {
                action(data);
            }
        })
        .error(function () {
            if (error) {
                home.modal('content/error');
            }
        });
    };

    home.isValidURL = function (url, action)
    {
        $.post(home.path + 'isvalidurl', {
            'url': url
        })
        .done(function (data) {
            if (data.trim() === 'true') {
                action(data);
            }
        });
    };

    /**
     * jQuery Upload HTML5
     *
     * example:
     *
     * $('input').upload(
     *     home.path + 'resource/create/file/' + workspace,
     *     function (res) {
     *         console.log('done', res);
     *     },
     *     function (progress) {
     *         $('.progress-bar').css('width', Math.round((progress.loaded * 100) / progress.totalSize) + '%')
     *     }
     * );
     *
     */
    $.fn.upload = function (remote, successFn, progressFn) {
        return this.each(function () {

            var formData = new FormData($(this).parents('form').get(0));

            $.ajax({
                url: remote,
                type: 'POST',
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload && progressFn) {
                        myXhr.upload.addEventListener('progress', progressFn, false);
                    }
                    return myXhr;
                },
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                complete : function (res) {
                    if (successFn) {
                        successFn(res);
                    }
                }
            });
        });
    };

    $('body').on('click', '.content-translatable .content-menu .dropdown-menu a', function () {
        var translatable = $(this).parents('.content-translatable').first();
        var lang = $(this).text();
        $('.content-menu button span', translatable).text(lang);
        $('.lang', translatable).each(function () {
            if ($(this).data('lang') === lang) {
                $(this).removeClass('hide');
            } else {
                $(this).addClass('hide');
            }
        });
    });

}());
