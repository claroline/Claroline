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
    var modal = window.Claroline.Modal;

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

    /**
     * Find urls ina text
     *
     * @param text A string
     *
     * @return An array with urls
     */
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
     * Create or update an element by POST method with ajax.
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
        var route = 'content/create/' + type;

        if (father) {
            route += '/' + father;
        }

        if (update) {
            route = 'content/update/' + id;
        }

        if (!home.creatorIsEmpty(form)) {
            $.post(home.path + route, form)
            .done(function (data) {
                if (!isNaN(data) && data !== '') {
                    home.insertContent(creatorElement, data, type, father);
                    home.emptyContent(creatorElement);
                } else if (data === 'true') {
                    home.insertContent(creatorElement, id, type, father, update);
                } else {
                    modal.error();
                }
            })
            .error(function () {
                modal.error();
            });
        }
    };

    /**
     * Delete a content or a content type.
     *
     * @param element The HTML elementof a content.
     * @param type, in order to delete a type, make this parameter true
     */
    home.deleteContent = function (element, type)
    {
        var path = typeof(type) === 'undefined' || type === false ? 'delete' : 'deletetype';
        var id = element.data('id');

        if (id) {
            $.ajax(home.path + 'content/' + path + '/' + id)
            .done(function (data) {
                if (data === 'true') {
                    if (type) {
                        element = element.parent();
                    }

                    element.hide('slow', function () {
                        $(this).remove();
                        $('.contents').trigger('ContentModified');
                    });
                } else {
                    modal.error();
                }
            })
            .error(function () {
                modal.error();
            });
        }
    };

    /**
     * check if a translated content form is empty
     *
     * @param form A serializeArray of a form element
     */
    home.creatorIsEmpty = function (form)
    {
        if (form instanceof Array) {
            for (var lang in form) {
                if (form.hasOwnProperty(lang) && form[lang].value !== undefined && form[lang].value !== '') {
                    return false;
                }
            }
        }

        return true;
    };

    /**
     * Change the size of a home page content.
     *
     * @param size The new size of the content, example: content-12
     * @param id The id of the content
     * @param type The type of the content
     * @param element The html elment to change after modify the content.
     */
    home.changeSize = function (size, id, type, element) {
        if (id && type && element) {
            $.post(home.path + 'content/update/' + id + '/' + size + '/' + type)
            .done(function (data) {
                if (data === 'true') {
                    $(element).removeClass(function (index, css) {
                        return (css.match(/\bcontent-\d+/g) || []).join(' ');
                    });
                    modal.hide();
                    $(element).addClass(size);
                    $(element).trigger('DOMSubtreeModified'); //height resize event
                    $('#sizes').modal('hide');
                    $('.contents').trigger('ContentModified');

                } else {
                    modal.error();
                }
            })
            .error(function () {
                modal.error();
            });
        }
    };

    /**
     * Put a content in a region (top, left, right, content and footer)
     *
     * @param name The name of the region
     * @param id The id of the content to put in a region
     */
    home.changeRegion = function (name, id) {
        if (name && id) {
            $.ajax(home.path + 'region/' + name + '/' + id)
            .done(function () {
                location.reload();
            })
            .error(function () {
                modal.error();
            });
        }
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
                modal.error();
            }
        });
    };

    home.canGenerateContent = function (url, action)
    {
        $.post(home.path + 'cangeneratecontent', {
            'url': url
        })
        .success(function (data) {
            if (data.trim() !== 'false') {
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
