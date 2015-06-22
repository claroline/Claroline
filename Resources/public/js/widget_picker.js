$(function(){
    var widgetPicker = window.Claroline.WidgetPicker = {};
    var modal = window.Claroline.Modal;

    widgetPicker.defaultRequestData = {
        multiple: true
    };
    widgetPicker.url = "";
    widgetPicker.data = {};
    widgetPicker.successHandler = function() {};
    widgetPicker.closeHandler   = function() {};

    widgetPicker.configureWidgetPicker = function (url,requestData, successHandler, closeHandler) {
        this.url = url;
        this.successHandler = successHandler;
        this.closeHandler = closeHandler;
        this.data = $.extend({}, this.defaultRequestData, requestData);
    };

    widgetPicker.openWidgetPicker = function () {
        widgetPicker.configureWidgetPicker(this.url, this.data, this.successHandler, this.closeHandler);
        widgetPicker.displayModal();
    };

    widgetPicker.displayModal = function () {
        var settings = {
            url: this.url,
            type: 'GET',
            data: $.extend({}, this.data)
        };

        $.ajax(settings)
        .success(function (data) {
            var modalElement = modal.create(data);

            modalElement.on('click', 'button.submit', function (event) {
                event.preventDefault();
                var nodes = $(".widget_picker_item input[type=checkbox]:checked", modalElement);
                var nodeValue = [];
                nodes.each(function (index, element) {
                    element = $(element);

                    nodeValue[index] = {
                        id: parseInt(element.val()),
                        icon: element.attr('data-icon'),
                        text: element.attr('data-text')
                    };
                });

                widgetPicker.successHandler(nodeValue);
                modalElement.modal('hide');
                widgetPicker.closeHandler(nodeValue);
            });
        })
        .error(function () {
            modal.error();
        });
    };
});