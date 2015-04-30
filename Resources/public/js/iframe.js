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

    var iframe = window.Claroline.Iframe = {};

    iframe.resize = function(frameId) {
        var height = window.innerWidth;//Firefox
        if (document.body.clientHeight)	height=document.body.clientHeight;//IE

        var frame = document.getElementById(frameId);
        frame.style.height = parseInt(height - frame.offsetTop - 8) + "px";
    }
}());
