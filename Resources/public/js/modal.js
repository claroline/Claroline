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
    var modal = window.Claroline.Modal = {};
    var modalStack = [];

    /**
     * Handles nested modals.
     */
    $('body').on({
        'show.bs.modal': function () {
            var stackLength = modalStack.length;

            if (stackLength > 0) {
                var previousModal = modalStack[stackLength - 1];

                if (previousModal.get(0) === $(this).get(0)) {
                    return;
                }

                previousModal.addClass('parent-hide');
            }

            modalStack.push($(this));
        },
        'hide.bs.modal': function () {
            modalStack.pop();
            var stackLength = modalStack.length;

            if (stackLength > 0) {
                modalStack[stackLength - 1].removeClass('parent-hide');
            }
        }
    }, '.modal');

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
     * This function show a complete modal with given title and content.
     *
     * @param title The title of the modal.
     * @param content The content of the modal.
     */
    modal.simpleContainer = function (title, content)
    {
        return modal.create(
            common.createElement('div', 'modal-dialog').html(
                common.createElement('div', 'modal-content').append(
                    common.createElement('div', 'modal-header')
                    .append(common.createElement('button', 'close').html('&times;').attr('data-dismiss', 'modal'))
                    .append(common.createElement('h4', 'modal-title').html(title))
                )
                .append(common.createElement('div', 'modal-body').html(content))
                .append(common.createElement('div', 'modal-footer').html(
                    common.createElement('button', 'btn btn-primary')
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
     * @param formId the form id
     */
    modal.displayForm = function (url, successHandler, formRenderHandler, formId) {
        $.ajax({
            url: url,
            success: function(data, textStatus, jqXHR) {
                modal.hide();
                modal.create(data).on('click', 'button.btn', function(event) {
                    event.preventDefault();
                    submitForm(data, successHandler, formId);
                });
                formRenderHandler(data);
            }
        });
    }

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
     * @param body the modal body
     * @param header the modal header
     */
    modal.confirmRequest = function (url, successHandler, successParameter, body, header) {
        var html = Twig.render(
            ModalWindow,
            {'confirmFooter': true, 'modalId': 'confirm-modal', 'body': body, 'header': header}
        );

        $('body').append(html);
        //display validation modal
        $('#confirm-modal').modal('show');
        //destroy the modal when hidden
        $('#confirm-modal').on('hidden.bs.modal', function () {
            $(this).remove();
        });

        $('#confirm-ok').on('click', function(event) {
            $.ajax({
                url: url,
                success: function(data) {
                    successHandler(event, successParameter, data);
                    $('#confirm-modal').modal('hide');
                }
            });
        });
    }

    function submitForm (html, successHandler, formId) {
        var form = $(html).find('form');
        var url = form.attr('action');
//        var formData = new FormData(form[0]);
        var formData = new FormData(document.getElementById(formId));

        $.ajax({
            url: url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    $('.modal').modal('hide');
                    successHandler(data, textStatus, jqXHR);
                } else {
                    //how do I find the root element of html ? It would be better to not have to use this class.
                    $('.modal-dialog').replaceWith(data);
                }
            }
        });
    }
}());
