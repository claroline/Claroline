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
    var currentStep = 0;
    var currentItem = 1;

    $('#item-' + 1).css('font-weight', 'bold');
    var totalSteps = document.getElementById('twig-attributes').getAttribute('data-total-steps');
    var totalItems = document.getElementById('twig-attributes').getAttribute('data-total-items');

    $('#progress-bar').html(currentStep + '/' + totalSteps);

    $('body').on('click', '.icon-arrow-right', function () {
        currentItem++;
        if (currentItem >= totalItems) {currentItem = totalItems; }
        loadRightFrame(currentItem);
    }).on('click', '.icon-arrow-left', function () {
        currentItem--;
        if (currentItem <= 1) {currentItem = 1; }
        loadRightFrame(currentItem);
    });

    var loadRightFrame = function (item) {
        currentItem = item;

        if ($('#item-' + item).attr('class') === 'activity-step' &&
            $('#item-' + item).attr('data-is-passed') === 'false') {
            currentStep++;
            $('#progress-bar').html(currentStep + '/' + totalSteps);
            $('#item-' + item).attr('data-is-passed', 'true');
        }

        $('#item-' + item).css('font-weight', 'bold');
        var route =  $('#item-' + item).attr('href');
        window.parent.document.getElementById('right-frame').src = route;
    };

    $('.activity-step').on('click', function (e) {
        e.preventDefault();
        var itemId = e.target.id;
        loadRightFrame(itemId.replace('item-', ''));
    });

    $('.activity-instruction').on('click', function (e) {
        e.preventDefault();
        var itemId = e.target.id;
        loadRightFrame(itemId.replace('item-', ''));
    });
})();



