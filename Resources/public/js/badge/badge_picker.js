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

    badgePicker.defaultSettings = {
        multiple: true
    };
    badgePicker.settings = {};

    badgePicker.configureBadgePicker = function (customSettings) {
        this.settings = $.extend({}, this.defaultSettings, customSettings);
    };

    badgePicker.openBadgePicker = function (url, callback) {
        badgePicker.configureBadgePicker(this.settings);
        var settings = {
            url:  url,
            type: 'POST',
            data: $.extend({}, badgePicker.settings)
        };
        modal.displayCustomForm(settings, callback, null, false);
    };
});