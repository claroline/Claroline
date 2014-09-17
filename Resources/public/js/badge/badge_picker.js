/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){

    var badgePicker = window.Claroline.BadgePicker = {};
    var modal       = window.Claroline.Modal;

    badgePicker.openBadgePicker = function (url, callback) {
        modal.displayForm(url, callback, function () {}, false);
    };
});