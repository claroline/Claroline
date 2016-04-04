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
    
    $('.check-status-btn').on('click', function () {
        var scoTitle = $(this).data('sco-title');
        var status = $(this).data('best-lesson-status');
        var totalTime = $(this).data('total-time');
        var score = $(this).data('bestScore');
        
        
        if (score === undefined) {
            $('#score-tracking-title').addClass('hidden');
            $('#score-tracking-display').addClass('hidden');
        } else {
            $('#score-tracking-title').removeClass('hidden');
            $('#score-tracking-display').removeClass('hidden');
            $('#score-tracking-display').html(score);
        }
        $('#total-time-tracking-display').html(totalTime);
        $('#status-tracking-display').html(status);
        $('#scorm-tracking-modal-title').html(scoTitle);
        $('#scorm-tracking-modal-box').modal('show');
    });
})();