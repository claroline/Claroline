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
    var common = window.Claroline.Common;
    var modal = window.Claroline.Modal = {
        modalStack: []
    };

    /**
     * Push a modal element and his backdrop in a stack
     *
     * @param element The modal html element
     */
    modal.push = function (element)
    {
        var index = modal.modalStack.length - 1;

        if (index >= 0) {
            var previousModal = modal.modalStack[index];

            if (!previousModal.hasClass('fullscreen')) {
                previousModal.addClass('parent-hide');
            }
        }

        modal.modalStack.push($(element));

        $('.modal-backdrop:not(.parent-hide)').addClass('parent-hide');
    };

    /**
     * Pop a modal element and his backdrop in a stack
     */
    modal.pop = function ()
    {
        modal.modalStack.pop();

        var index = modal.modalStack.length - 1;

        if (index >= 0) {
            modal.modalStack[index].removeClass('parent-hide');

            $('.modal-backdrop.parent-hide').removeClass('parent-hide');
        }
    };

    /**
     * Hide all open modals.
     */
    modal.hide = function ()
    {
        $('.modal').modal('hide');
    };

    /**
     * Create a new modal that destroys itself when close.
     *
     * @param content The content to put inside this modal (this modal does not contain modal-digalog element)
     */
    modal.create = function (content)
    {
        return common.createElement('div', 'modal fade')
            .html(content)
            .appendTo('body')
            .modal('show')
            .on('hidden.bs.modal', function () {
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
     * Create a default modal footer.
     */
    modal.defaultFooter = function ()
    {
        return common.createElement('button', 'btn btn-primary')
            .html(translator.get('home:Ok'))
            .attr('data-dismiss', 'modal');
    };

    /**
     * This function show a complete modal with given title and content.
     *
     * @param title The title of the modal.
     * @param content The content of the modal.
     * @param footer The footer of the modal, if footer is not defined a default footer will be used.
     */
    modal.simpleContainer = function (title, content, footer)
    {
        footer = typeof(footer) !== 'undefined' ? footer : modal.defaultFooter();

        return modal.create(
            common.createElement('div', 'modal-dialog').html(
                common.createElement('div', 'modal-content').append(
                    common.createElement('div', 'modal-header')
                    .append(common.createElement('button', 'close').html('&times;').attr('data-dismiss', 'modal'))
                    .append(common.createElement('h4', 'modal-title').html(title))
                )
                .append(common.createElement('div', 'modal-body').html(content))
                .append(common.createElement('div', 'modal-footer').html(footer))
            )
        );
    };

     /**
     * This function show a confirm modal with given title and content.
     *
     * @param title The title of the modal.
     * @param content The content of the modal.
     */
    modal.confirmContainer = function (title, content)
    {
        var footer = common.createElement('div').append(
            common.createElement('button', 'btn btn-default')
            .html(translator.get('platform:cancel'))
            .attr('data-dismiss', 'modal')
        ).append(
            common.createElement('button', 'btn btn-primary').html(translator.get('home:Ok'))
            .attr('data-dismiss', 'modal')
        );

        return modal.simpleContainer(title, content, footer);
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


    /**
     * Displays a form in a modal. The form requires all the modals divs and layout because it's pretty much impossible
     * to render something pretty otherwise as the form will usually include the class modal-body for datas and
     * modal-footer for submissions/cancelation.
     * The modal root element must contain the class "modal-dialog"
     *
     * It assumes the route for the form submission returns:
     * - a json response when successfull
     * - the form rendered with its errors when an error occured
     *
     * @param url The route of the controller rendering the form
     * @param successHandler A successHandler
     * @param formRenderHandler an action wich is done after the form is rendered the first time
     */
    modal.displayForm = function (url, successHandler, formRenderHandler) {
        $.ajax(url)
        .success(function (data) {
            var modalElement = modal.create(data);

            modalElement.on('click', 'button.btn', function (event) {
                event.preventDefault();
                modal.submitForm(modalElement, successHandler);
            });
            formRenderHandler(data);
        })
        .error(function () {
            modal.error();
        });
    };

    /**
     * Displays a confirmation message in a modal.
     * The successHandler will take these parameters (
     *      event,
     *      successParameter,
     *      data (the data wich are returned by the ajax request)
     * )
     * @param url the url wich is going to be confirmed
     * @param successHandler a sucessHandler
     * @param successParameter a parameter required by the request handler
     * @param content the modal body
     * @param title the modal header
     */
    modal.confirmRequest = function (url, successHandler, successParameter, content, title) {
        modal.confirmContainer(title, content).on('click', '.btn-primary', function (event) {
            $.ajax(url)
            .success(function (data) {
                successHandler(event, successParameter, data);
            })
            .error(function () {
                modal.error();
            });
        });
    };

    /**
     * This method is triggered when submit a form inside a modal created with modal.displayForm()
     */
    modal.submitForm = function (modalElement, callBack)
    {
        var form = $('form', modalElement);
        var url = form.attr('action');
        var formData = form.serializeArray();

        $.post(url, formData)
        .success(function (data, textStatus, jqXHR) {
            if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                modalElement.modal('hide');
                callBack(data, textStatus, jqXHR);
            } else {
                $('.modal-dialog', modalElement).replaceWith(data);
            }
        })
        .error(function () {
            modal.error();
        });
    };

    /**
     * If the element is the same as the last element in the modal stack
     */
    modal.isLastModal = function (element)
    {
        var index = modal.modalStack.length - 1;

        if (index >= 0 && typeof(modal.modalStack[index]) !== undefined && modal.modalStack[index].get(0) === element) {
            return true;
        }
    };

    /** events **/

    $('body').on('show.bs.modal', '.modal', function (event) {
        if (common.hasNamespace(event, 'bs.modal') && !modal.isLastModal(this)) {
            modal.push(this);
            $(this).on('hide.bs.modal', function (event) {
                if (common.hasNamespace(event, 'bs.modal')) {
                    modal.pop();
                }
            });
        }
    });

}());
