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
    
    $('.view-survey-btn').on('click', function () {
        var surveyId = $(this).data('survey-id');
        
        $.ajax({
            url: Routing.generate(
                'claro_survey_display',
                {'survey': surveyId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-survey-body').html(datas);
                $('#view-survey-box').modal('show');
            }
        });
    });
})();