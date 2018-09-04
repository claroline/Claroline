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

    window.Claroline = window.Claroline || {};

    var common = window.Claroline.Common = {};
    var routing = window.Routing;

    /**
     * This function creates a new element in the document with a given class name.
     *
     * @param tag The tag name of the new element.
     * @param className The class name of the new element.
     */
    common.createElement = function (tag, className)
    {
        return $(document.createElement(tag)).addClass(className);
    };

    /**
     * If has namespace
     */
    common.hasNamespace = function (element, namespace)
    {
        if (element.hasOwnProperty('namespace') && element.namespace === namespace) {
            return true;
        }
    };

    /**
     * Toogle the css class name of a HTML element
     *
     * @param element The HTML element
     * @param condition A boolean
     * @param className The class name, if this value is undefined 'hide' will be used
     */
    common.toggle = function (element, condition, className)
    {
        className = className !== undefined ? className : 'hide';

        if (condition) {
            element.removeClass(className);
        } else {
            element.addClass(className);
        }
    };

    /**
     * jQuery Upload HTML5
     *
     * example:
     *
     * $('input').upload(
     *     home.path + 'resource/create/file/' + workspace,
     *     function (res) {
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

})();
