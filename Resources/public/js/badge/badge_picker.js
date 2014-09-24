/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){
    var badgePicker     = window.Claroline.BadgePicker = {};
    var modal           = window.Claroline.Modal;

    badgePicker.defaultRequestData = {
        multiple: true
    };
    badgePicker.url = "";
    badgePicker.data = {};
    badgePicker.successHandler = function() {};

    badgePicker.configureBadgePicker = function (url,requestData, successHandler) {
        this.url            = url;
        this.successHandler = successHandler;
        this.data           = $.extend({}, this.defaultRequestData, requestData);
    };

    badgePicker.openBadgePicker = function () {
        badgePicker.configureBadgePicker(this.url, this.data, this.successHandler);
        badgePicker.displayModal();
    };

    badgePicker.displayModal = function () {
        var settings = {
            url:  this.url,
            type: 'POST',
            data: $.extend({}, this.data)
        };

        $.ajax(settings)
        .success(function (data) {
            var modalElement = modal.create(data);

            modalElement.on('click', 'button.submit', function (event) {
                event.preventDefault();
                var nodes = $(".badge_picker_item input[type=checkbox]:checked", modalElement);
                var nodeValue = [];
                nodes.each(function (index, element) {
                    element = $(element);

                    nodeValue[index] = {
                        id:   element.val(),
                        icon: element.attr('data-icon'),
                        text: element.attr('data-text')
                    };
                });
                console.log(nodeValue);

                badgePicker.successHandler(nodeValue);
                modalElement.modal('hide');
            });
        })
        .error(function () {
            modal.error();
        });
    };
});