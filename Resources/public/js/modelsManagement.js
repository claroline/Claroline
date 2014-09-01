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
    
    var modelId;
    var surveyId;
        
    $('.delete-model-btn').on('click', function () {
        modelId = $(this).data('model-id');
        surveyId = $(this).data('survey-id');
        $('#delete-model-validation-box').modal('show');
    });
    
    $('#delete-model-confirm-ok').on('click', function () {
        $('#delete-model-validation-box').modal('hide');
        
        window.location = Routing.generate(
            'claro_survey_model_delete',
            {
                'model': modelId,
                'survey': surveyId
            }
        );
    });
})();