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
    var translator = window.Translator;
    var routing = window.Routing;
    var modal = window.Claroline.Modal = {};

    /**
     * Hide all open modals.
     */
    modal.hide = function ()
    {
        $('.modal').modal('hide');
    };

    /**
     * Hide parent modal when nested modals.
     */
    modal.parentHide = function () {
        if (typeof(modal.parentModals) === 'undefined') {
            modal.parentModals = [];
        }

        $('.modal.in:not(.parent-hide, .fullscreen), .modal-backdrop:not(.parent-hide, .fullscreen)')
        .each(function () {
            $(this).addClass('parent-hide');
            modal.parentModals.push(this);
        });
    };

    /**
     * Show parent modal when close nested modals.
     */
    modal.parentShow = function ()
    {
        if (typeof(modal.parentModals) !== 'undefined') {
            for (var object in modal.parentModals) {
                if (modal.parentModals.hasOwnProperty(object)) {
                    $(modal.parentModals[object]).removeClass('parent-hide');
                }
            }
            modal.parentModals = [];
        }
    };

    /**
     * Create a new modal that destroys itself when close.
     *
     * @param content The content to put inside this modal (this modal does not contain modal-digalog element)
     */
    modal.create = function (content)
    {
        modal.parentHide();

        return modal.createElement('div', 'modal fade')
            .html(content)
            .appendTo('body')
            .modal('show')
            .on('hidden.bs.modal', function () {
                modal.parentShow();
                $(this).remove();
            });
    };

    /**
     * This function show a new modal with an error message.
     */
    modal.error = function ()
    {
        modal.hide();
        modal.simpleContainer(
            translator.get('home:An error occurred'),
            translator.get('home:Please try again later or check your internet connection')
        );
    };

    /**
     * This function creates a new element in the document with a given class name.
     *
     * @param tag The tag name of the new element.
     * @param className The class name of the new element.
     */
    modal.createElement = function (tag, className)
    {
        return $(document.createElement(tag)).addClass(className);
    };

    /**
     * This function show a complete modal with given title and content.
     *
     * @param title The title of the modal.
     * @param content The content of the modal.
     */
    modal.simpleContainer = function (title, content)
    {
        modal.create(
            modal.createElement('div', 'modal-dialog').html(
                modal.createElement('div', 'modal-content').append(
                    modal.createElement('div', 'modal-header')
                    .append(modal.createElement('button', 'close').html('&times;').attr('data-dismiss', 'modal'))
                    .append(modal.createElement('h4', 'modal-title').html(title))
                )
                .append(modal.createElement('div', 'modal-body').html(content))
                .append(modal.createElement('div', 'modal-footer').html(
                    modal.createElement('button', 'btn btn-primary')
                    .html(translator.get('home:Ok'))
                    .attr('data-dismiss', 'modal')
                    )
                )
            )
        );
    };

    /**
     * Show a new modal from a given url, this url must return the entire HTML of the modal-dialog, if you want to
     * show a modal without definding all the HTML you can use simpleContainer function.
     *
     * @param url The url of a modal content.
     * @param action An optional function to execute when modal is showed, this is useful in order to make binds.
     */
    modal.fromUrl = function (url, action)
    {
        $.ajax(url)
        .done(function (data) {
            var element = modal.create(data);
            if (typeof(action) === 'function') {
                action(element);
            }
        })
        .error(function () {
            modal.error();
        });
    };

    /**
     * Show a new modal from a given route, this route must render the entire HTML of the modal-dialog, if you want to
     * show a modal without definding all the HTML you can use simpleContainer function.
     *
     * @param route The route of a modal content.
     * @param variables The route.
     * @param action A function to execute when modal is showed, this is useful in order to make binds.
     *
     * Example: modal.fromRoute('my_route', {'myVariable': 'myValue'});
     */
    modal.fromRoute = function (route, variables, action)
    {
        modal.fromUrl(routing.generate(route, variables), action);
    };

}());
